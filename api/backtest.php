<?php
// api/backtest.php
// POST JSON → runs portfolio backtest → returns JSON results
// All financial calculations are server-side PHP.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$raw = file_get_contents('php://input');
if (!$raw) { echo json_encode(['error' => 'Empty request']); exit; }

$input = json_decode($raw, true);
if (!$input) { echo json_encode(['error' => 'Invalid JSON']); exit; }

$params     = $input['params']     ?? [];
$portfolios = $input['portfolios'] ?? [];
$cashflows  = $input['cashflows']  ?? [];

if (empty($portfolios)) {
    echo json_encode(['error' => 'At least one portfolio is required']); exit;
}
if (count($portfolios) > 4) {
    echo json_encode(['error' => 'Maximum 4 portfolios allowed']); exit;
}

// ── Parameters ─────────────────────────────────────────────────
$initial_value   = max(100.0, floatval($params['initial_value'] ?? 10000));
$start_month     = !empty($params['start_date']) ? $params['start_date'] : null; // YYYY-MM
$end_month       = !empty($params['end_date'])   ? $params['end_date']   : null;
$inflate         = !empty($params['inflation_adjust']);
$rolling_window  = max(12, min(120, intval($params['rolling_window'] ?? 60)));
$benchmark_tick  = strtoupper(trim($params['benchmark'] ?? 'SPY'));
$risk_free       = 0.02; // 2% annual risk-free rate

// ── Collect all unique tickers ──────────────────────────────────
$need_tickers = [];
foreach ($portfolios as $p) {
    foreach ($p['assets'] ?? [] as $a) {
        $t = strtoupper(trim($a['ticker'] ?? ''));
        if ($t) $need_tickers[$t] = true;
    }
}
if ($benchmark_tick) $need_tickers[$benchmark_tick] = true;

// ── Fetch price series for each ticker ─────────────────────────
$price_data = []; // [ticker => [YYYY-MM => price]]
$names      = [];
foreach (array_keys($need_tickers) as $ticker) {
    $result = fetchPrices($ticker, $conn);
    if ($result) {
        $price_data[$ticker] = $result['prices'];
        $names[$ticker]      = $result['name'];
    }
}

// ── Check that all portfolio tickers have data ──────────────────
$missing = [];
foreach ($portfolios as $p) {
    foreach ($p['assets'] ?? [] as $a) {
        $t = strtoupper(trim($a['ticker'] ?? ''));
        if ($t && !isset($price_data[$t])) $missing[] = $t;
    }
}
if (!empty($missing)) {
    echo json_encode(['error' => 'Could not fetch data for: ' . implode(', ', array_unique($missing))]); exit;
}

// ── Determine global date range ─────────────────────────────────
$all_months = [];
foreach ($price_data as $ticker => $series) {
    foreach (array_keys($series) as $ym) $all_months[$ym] = true;
}
if (empty($all_months)) {
    echo json_encode(['error' => 'No price data available']); exit;
}

ksort($all_months);
$available_months = array_keys($all_months);
$global_start = $available_months[0];
$global_end   = end($available_months);

if ($start_month && $start_month > $global_start) $global_start = $start_month;
if ($end_month   && $end_month   < $global_end)   $global_end   = $end_month;

$month_list = buildMonthList($global_start, $global_end);

if (count($month_list) < 13) {
    echo json_encode(['error' => 'Date range too short — need at least 13 months of data']); exit;
}

// ── Simulate portfolios ─────────────────────────────────────────
$results = [];
foreach ($portfolios as $pconfig) {
    $r = simulatePortfolio($pconfig, $price_data, $month_list, $initial_value, $cashflows, $rolling_window, $risk_free);
    if (isset($r['error'])) {
        echo json_encode(['error' => $r['error']]); exit;
    }
    $results[] = $r;
}

// ── Simulate benchmark ──────────────────────────────────────────
$benchmark_result = null;
if ($benchmark_tick && isset($price_data[$benchmark_tick])) {
    $bench_cfg = [
        'name'    => $names[$benchmark_tick] ?? $benchmark_tick,
        'color'   => '#64748b',
        'assets'  => [['ticker' => $benchmark_tick, 'weight' => 100]],
        'rebalance' => 'none'
    ];
    $br = simulatePortfolio($bench_cfg, $price_data, $month_list, $initial_value, [], $rolling_window, $risk_free);
    if (!isset($br['error'])) $benchmark_result = $br;
}

// ── Inflation adjustment ────────────────────────────────────────
if ($inflate) {
    $monthly_cpi = pow(1.025, 1.0/12) - 1; // ~2.5% annual US CPI
    foreach ($results as &$r) {
        $n = count($r['growth_series']);
        foreach ($r['growth_series'] as $i => &$v) {
            $v = round($v / pow(1 + $monthly_cpi, $i), 2);
        }
        unset($v);
        $vs = $r['growth_series'][0] ?? 0;
        $ve = end($r['growth_series']) ?: 0;
        $yrs = ($n - 1) / 12.0;
        $r['metrics']['cagr'] = ($yrs > 0 && $vs > 0) ? round((pow($ve/$vs, 1/$yrs) - 1) * 100, 2) : 0;
        $r['metrics']['total_return'] = $vs > 0 ? round(($ve/$vs - 1) * 100, 2) : 0;
        $r['metrics']['final_value']  = round($ve, 2);
    }
    unset($r);
}

// ── Correlation matrix ──────────────────────────────────────────
$corr_matrix = null;
if (count($results) > 1) {
    $corr_matrix = buildCorrelationMatrix($results);
}

echo json_encode([
    'ok'          => true,
    'months'      => $month_list,
    'portfolios'  => $results,
    'benchmark'   => $benchmark_result,
    'corr_matrix' => $corr_matrix,
    'params_used' => [
        'initial_value'    => $initial_value,
        'start_month'      => $month_list[0]    ?? '',
        'end_month'        => end($month_list)  ?: '',
        'rolling_window'   => $rolling_window,
        'inflation_adjust' => $inflate,
        'benchmark'        => $benchmark_tick
    ]
]);

// ════════════════════════════════════════════════════════════════
// FUNCTIONS
// ════════════════════════════════════════════════════════════════

function fetchPrices(string $ticker, $conn): ?array {
    // Check Myanmar stocks first
    $stmt = $conn->prepare(
        "SELECT DATE_FORMAT(price_date,'%Y-%m') AS ym, close_price, company_name
         FROM myanmar_stocks WHERE ticker=? ORDER BY price_date ASC"
    );
    $stmt->bind_param('s', $ticker);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $prices = []; $name = $ticker;
        while ($row = $res->fetch_assoc()) {
            $prices[$row['ym']] = floatval($row['close_price']);
            if ($row['company_name']) $name = $row['company_name'];
        }
        return ['prices' => $prices, 'name' => $name];
    }

    // Check DB cache (24h TTL)
    $stmt = $conn->prepare(
        "SELECT price_data, ticker_name, cached_at FROM backtest_price_cache WHERE ticker=?"
    );
    $stmt->bind_param('s', $ticker);
    $stmt->execute();
    $cached = $stmt->get_result()->fetch_assoc();
    if ($cached && strtotime($cached['cached_at']) > time() - 86400) {
        $prices = json_decode($cached['price_data'], true);
        if ($prices) return ['prices' => $prices, 'name' => $cached['ticker_name'] ?? $ticker];
    }

    // Fetch from Yahoo Finance
    $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}"
         . "?range=20y&interval=1mo&includeAdjustedClose=true";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept: application/json'
        ]
    ]);
    $raw = curl_exec($ch); curl_close($ch);
    if (!$raw) {
        // Serve stale cache on network error
        if ($cached) {
            $prices = json_decode($cached['price_data'], true);
            if ($prices) return ['prices' => $prices, 'name' => $cached['ticker_name'] ?? $ticker];
        }
        return null;
    }

    $json   = json_decode($raw, true);
    $result = $json['chart']['result'][0] ?? null;
    if (!$result) return null;

    $meta    = $result['meta']       ?? [];
    $ts      = $result['timestamp']  ?? [];
    $closes  = $result['indicators']['adjclose'][0]['adjclose'] ?? [];
    $name    = $meta['shortName']    ?? $meta['longName'] ?? $ticker;

    $prices = [];
    foreach ($ts as $i => $stamp) {
        if (!isset($closes[$i]) || $closes[$i] === null || $closes[$i] <= 0) continue;
        $ym = date('Y-m', $stamp);
        $prices[$ym] = round(floatval($closes[$i]), 4);
    }
    ksort($prices);
    if (empty($prices)) return null;

    // Cache in DB
    $pj = json_encode($prices);
    $s2 = $conn->prepare(
        "INSERT INTO backtest_price_cache (ticker,price_data,ticker_name,cached_at)
         VALUES (?,?,?,NOW())
         ON DUPLICATE KEY UPDATE price_data=?,ticker_name=?,cached_at=NOW()"
    );
    $s2->bind_param('sssss', $ticker, $pj, $name, $pj, $name);
    $s2->execute();

    return ['prices' => $prices, 'name' => $name];
}

function buildMonthList(string $start, string $end): array {
    $list = [];
    $cur  = $start;
    while ($cur <= $end) {
        $list[] = $cur;
        [$y, $m] = explode('-', $cur);
        $m = intval($m) + 1;
        if ($m > 12) { $m = 1; $y = intval($y) + 1; }
        $cur = sprintf('%04d-%02d', $y, $m);
    }
    return $list;
}

function simulatePortfolio(array $pc, array $pd, array $ml, float $init, array $cfs, int $rw, float $rf): array {
    $assets    = $pc['assets']    ?? [];
    $rebalance = $pc['rebalance'] ?? 'yearly';
    $n         = count($ml);

    // Normalize weights
    $raw_w = [];
    foreach ($assets as $a) {
        $t = strtoupper(trim($a['ticker'] ?? ''));
        if ($t) $raw_w[$t] = max(0.0, floatval($a['weight'] ?? 0));
    }
    $tot_w = array_sum($raw_w);
    if ($tot_w <= 0) return ['error' => 'Portfolio "' . ($pc['name'] ?? '') . '" has no valid weights'];
    $weights = [];
    foreach ($raw_w as $t => $w) $weights[$t] = $w / $tot_w;

    // Build forward-filled price lookup by index
    $prices = []; // [ticker][idx]
    foreach (array_keys($weights) as $t) {
        $last = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $m = $ml[$i];
            if (isset($pd[$t][$m]) && $pd[$t][$m] > 0) $last = $pd[$t][$m];
            $prices[$t][$i] = $last;
        }
    }

    // Find first valid month (all assets have prices)
    $first_idx = null;
    for ($i = 0; $i < $n; $i++) {
        $ok = true;
        foreach (array_keys($weights) as $t) {
            if ($prices[$t][$i] <= 0) { $ok = false; break; }
        }
        if ($ok) { $first_idx = $i; break; }
    }
    if ($first_idx === null) {
        return ['error' => 'No price data overlap for portfolio "' . ($pc['name'] ?? '') . '"'];
    }

    // Parse cashflow rules into per-month schedule
    $cf_schedule = []; // [idx => ['fixed'=>x, 'pct'=>y]]
    foreach ($cfs as $rule) {
        $type = $rule['type']      ?? 'fixed';      // fixed|percent|onetime
        $dir  = $rule['direction'] ?? 'withdrawal'; // withdrawal|contribution
        $sign = ($dir === 'contribution') ? 1.0 : -1.0;
        $amt  = max(0.0, floatval($rule['amount'] ?? 0));

        if ($type === 'onetime') {
            $date_ym = $rule['date'] ?? '';
            $idx = array_search($date_ym, $ml);
            if ($idx !== false && $idx > $first_idx) {
                $cf_schedule[$idx]['fixed'] = ($cf_schedule[$idx]['fixed'] ?? 0) + $sign * $amt;
            }
        } else {
            $freq_months_map = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12];
            $freq  = $freq_months_map[$rule['frequency'] ?? 'yearly'] ?? 12;
            $offset = max(0, intval($rule['offset'] ?? 0));
            for ($i = $first_idx + 1; $i < $n; $i++) {
                $since = $i - $first_idx;
                if ($since < $offset) continue;
                if (($since - $offset) % $freq !== 0) continue;
                $key = ($type === 'percent') ? 'pct' : 'fixed';
                $cf_schedule[$i][$key] = ($cf_schedule[$i][$key] ?? 0) + $sign * $amt;
            }
        }
    }

    // Initialize holdings
    $units = [];
    foreach ($weights as $t => $w) {
        $p0 = $prices[$t][$first_idx];
        $units[$t] = ($p0 > 0) ? ($init * $w) / $p0 : 0.0;
    }

    $growth  = array_fill(0, $n, 0.0);
    $growth[$first_idx] = $init;
    $end_idx = $n - 1;

    for ($i = $first_idx + 1; $i < $n; $i++) {
        // Market value
        $total = 0.0;
        foreach ($units as $t => $u) $total += $u * $prices[$t][$i];

        // Apply cashflows
        $fixed_cf = $cf_schedule[$i]['fixed'] ?? 0.0;
        $pct_cf   = $cf_schedule[$i]['pct']   ?? 0.0;
        $total   += $fixed_cf;
        if ($pct_cf != 0) $total *= (1.0 + $pct_cf / 100.0);
        $total = max(0.0, $total);

        // Rebalance check
        $mo_int = (int)substr($ml[$i], 5, 2);
        switch ($rebalance) {
            case 'monthly':   $do_reb = true; break;
            case 'quarterly': $do_reb = in_array($mo_int, [1, 4, 7, 10]); break;
            case 'yearly':    $do_reb = ($mo_int === 1); break;
            default:          $do_reb = false; break;
        }

        $has_cf = ($fixed_cf != 0 || $pct_cf != 0);
        if (($do_reb || $has_cf) && $total > 0) {
            foreach ($weights as $t => $w) {
                $p = $prices[$t][$i];
                $units[$t] = ($p > 0) ? ($total * $w) / $p : 0.0;
            }
        }

        $growth[$i] = round($total, 2);

        if ($total <= 0) {
            $end_idx = $i;
            for ($j = $i + 1; $j < $n; $j++) $growth[$j] = 0.0;
            break;
        }
    }

    // ── Metrics ─────────────────────────────────────────────────
    $v_start     = $growth[$first_idx];
    $v_end       = $growth[$end_idx];
    $months_span = $end_idx - $first_idx;
    $years       = $months_span > 0 ? $months_span / 12.0 : 0.0;

    // Monthly returns
    $monthly_rets = [];
    for ($i = $first_idx + 1; $i <= $end_idx; $i++) {
        $pv = $growth[$i - 1];
        if ($pv > 0) $monthly_rets[] = ($growth[$i] - $pv) / $pv;
    }

    // CAGR
    $cagr = ($years > 0 && $v_start > 0) ? pow($v_end / $v_start, 1.0 / $years) - 1.0 : 0.0;

    // Annualized volatility
    $vol = 0.0;
    $cnt = count($monthly_rets);
    if ($cnt > 1) {
        $mean = array_sum($monthly_rets) / $cnt;
        $sq   = 0.0;
        foreach ($monthly_rets as $r) $sq += ($r - $mean) * ($r - $mean);
        $vol = sqrt($sq / ($cnt - 1)) * sqrt(12);
    }

    // Sharpe
    $sharpe = ($vol > 0) ? (($cagr - $rf) / $vol) : 0.0;

    // Max drawdown + drawdown series
    $peak      = $v_start;
    $max_dd    = 0.0;
    $max_dd_at = $first_idx;
    $in_dd     = false;
    $rec_months= null;
    $dd_series = [];
    for ($i = $first_idx; $i <= $end_idx; $i++) {
        if ($growth[$i] > $peak) {
            if ($in_dd) { $rec_months = $i - $max_dd_at; $in_dd = false; }
            $peak = $growth[$i];
        }
        $dd = ($peak > 0) ? ($growth[$i] - $peak) / $peak : 0.0;
        $dd_series[] = round($dd * 100, 2);
        if ($dd < $max_dd) { $max_dd = $dd; $max_dd_at = $i; $in_dd = true; }
    }

    // Annual returns
    $yr_s = []; $yr_e = [];
    for ($i = $first_idx; $i <= $end_idx; $i++) {
        $yr = substr($ml[$i], 0, 4);
        if (!isset($yr_s[$yr])) $yr_s[$yr] = $growth[$i];
        $yr_e[$yr] = $growth[$i];
    }
    $annual_rets = [];
    foreach ($yr_s as $yr => $vs) {
        $ve = $yr_e[$yr] ?? $vs;
        $annual_rets[$yr] = ($vs > 0) ? round(($ve / $vs - 1) * 100, 2) : 0.0;
    }

    // Monthly heatmap [year][month_int]
    $heatmap = [];
    for ($i = $first_idx + 1; $i <= $end_idx; $i++) {
        $yr  = substr($ml[$i], 0, 4);
        $mo  = (int)substr($ml[$i], 5, 2);
        $pv  = $growth[$i - 1];
        if ($pv > 0) $heatmap[$yr][$mo] = round(($growth[$i] / $pv - 1) * 100, 2);
    }

    // Rolling returns (CAGR over $rw months)
    $rolling = [];
    for ($i = $first_idx + $rw; $i <= $end_idx; $i++) {
        $v0  = $growth[$i - $rw];
        $v1  = $growth[$i];
        $yrs = $rw / 12.0;
        if ($v0 > 0) $rolling[$ml[$i]] = round((pow($v1 / $v0, 1.0 / $yrs) - 1) * 100, 2);
    }

    // MWRR
    $mwrr = calcMWRR($init, $v_end, $cf_schedule, $first_idx, $end_idx, $months_span);

    return [
        'name'            => htmlspecialchars($pc['name'] ?? 'Portfolio'),
        'color'           => $pc['color'] ?? '#d4af37',
        'growth_series'   => array_values(array_slice($growth, $first_idx, $months_span + 1)),
        'month_labels'    => array_values(array_slice($ml, $first_idx, $months_span + 1)),
        'monthly_returns' => $monthly_rets,
        'heatmap'         => $heatmap,
        'rolling'         => $rolling,
        'dd_series'       => $dd_series,
        'annual_returns'  => $annual_rets,
        'metrics' => [
            'cagr'            => round($cagr  * 100, 2),
            'mwrr'            => round($mwrr  * 100, 2),
            'volatility'      => round($vol   * 100, 2),
            'sharpe'          => round($sharpe, 3),
            'max_drawdown'    => round($max_dd * 100, 2),
            'recovery_months' => $rec_months,
            'best_year'       => !empty($annual_rets) ? max($annual_rets)  : 0,
            'worst_year'      => !empty($annual_rets) ? min($annual_rets)  : 0,
            'final_value'     => round($v_end, 2),
            'total_return'    => ($v_start > 0) ? round(($v_end / $v_start - 1) * 100, 2) : 0,
        ]
    ];
}

function calcMWRR(float $init, float $final, array $cf_sched, int $fi, int $ei, int $span): float {
    // If no cashflows, MWRR = CAGR
    $has_cf = false;
    foreach ($cf_sched as $idx => $cf) {
        if ($idx > $fi && $idx <= $ei) { $has_cf = true; break; }
    }
    $years = $span / 12.0;
    if (!$has_cf || $years <= 0 || $init <= 0) {
        return ($years > 0 && $init > 0) ? pow($final / $init, 1.0 / $years) - 1.0 : 0.0;
    }

    // Build IRR cashflow array (investor perspective)
    // outflows are negative, inflows positive
    $cfs   = [-$init]; // initial investment = investor outflow
    $times = [0];      // in months
    foreach ($cf_sched as $idx => $cf) {
        if ($idx <= $fi || $idx > $ei) continue;
        $amt = ($cf['fixed'] ?? 0.0) + ($cf['pct'] ?? 0.0) * $init / 100.0;
        if (abs($amt) < 0.01) continue;
        // contribution → investor outflow → negative IRR CF
        // withdrawal  → investor inflow  → positive IRR CF
        $cfs[]   = -$amt;
        $times[] = $idx - $fi;
    }
    $cfs[]   = $final;  // final portfolio value → investor inflow
    $times[] = $span;

    // Newton-Raphson (monthly rate)
    $r = 0.005;
    for ($it = 0; $it < 300; $it++) {
        $f = 0.0; $df = 0.0;
        foreach ($cfs as $j => $cf) {
            $t    = $times[$j];
            $disc = pow(1.0 + $r, $t);
            $f   += $cf / $disc;
            $df  -= ($t > 0) ? $t * $cf / ($disc * (1.0 + $r)) : 0;
        }
        if (abs($df) < 1e-14) break;
        $r_new = $r - $f / $df;
        if (abs($r_new - $r) < 1e-10) { $r = $r_new; break; }
        $r = max(-0.9, min(50.0, $r_new));
    }
    return pow(1.0 + $r, 12) - 1.0;
}

function buildCorrelationMatrix(array $results): array {
    $n   = count($results);
    $mat = [];
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            if ($i === $j) { $mat[$i][$j] = 1.0; continue; }
            $x   = $results[$i]['monthly_returns'];
            $y   = $results[$j]['monthly_returns'];
            $len = min(count($x), count($y));
            if ($len < 2) { $mat[$i][$j] = null; continue; }
            $x = array_slice($x, -$len);
            $y = array_slice($y, -$len);
            $mx = array_sum($x) / $len;
            $my = array_sum($y) / $len;
            $num = $sx = $sy = 0.0;
            for ($k = 0; $k < $len; $k++) {
                $dx = $x[$k] - $mx; $dy = $y[$k] - $my;
                $num += $dx * $dy; $sx += $dx * $dx; $sy += $dy * $dy;
            }
            $denom = sqrt($sx * $sy);
            $mat[$i][$j] = $denom > 0 ? round($num / $denom, 3) : null;
        }
    }
    return $mat;
}
