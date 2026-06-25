<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'premium') {
    http_response_code(403);
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$fpdf_path = __DIR__ . '/lib/fpdf/fpdf.php';
if (!file_exists($fpdf_path)) {
    http_response_code(500);
    exit('FPDF library not found. Please run: composer require setasign/fpdf  OR  download fpdf.php from fpdf.org and place it at lib/fpdf/fpdf.php');
}

require $fpdf_path;

$month        = preg_replace('/[^0-9\-]/', '', $_POST['month'] ?? '');
$goal         = floatval($_POST['goal'] ?? 0);
$transactions = json_decode($_POST['transactions'] ?? '[]', true);
if (!is_array($transactions)) $transactions = [];

// ── Aggregates ────────────────────────────────────────────────────────────────
$total_income  = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    $amt = floatval($t['amount'] ?? 0);
    if (($t['type'] ?? '') === 'in') $total_income  += $amt;
    else                              $total_expense += $amt;
}
$balance    = $total_income - $total_expense;
$difference = $balance - $goal;
$progress   = ($goal > 0) ? min(100, ($balance / $goal) * 100) : 0;

$month_label = '';
if ($month) {
    $dt = DateTime::createFromFormat('Y-m', $month);
    $month_label = $dt ? $dt->format('F Y') : $month;
}

// ── Colour helpers ────────────────────────────────────────────────────────────
function hex2rgb(string $hex): array {
    $hex = ltrim($hex, '#');
    return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
}

// ── PDF class ─────────────────────────────────────────────────────────────────
class BudgetPDF extends FPDF {

    public string $month_label = '';

    function Header() {
        // Dark header band
        $this->SetFillColor(2, 6, 23);          // #020617
        $this->Rect(0, 0, 210, 38, 'F');

        // Gold accent bar
        $this->SetFillColor(212, 175, 55);      // #d4af37
        $this->Rect(0, 0, 5, 38, 'F');

        // Title
        $this->SetXY(12, 8);
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(212, 175, 55);
        $this->Cell(0, 10, 'Monthly Wealth Planner', 0, 1, 'L');

        // Subtitle
        $this->SetX(12);
        $this->SetFont('Arial', '', 11);
        $this->SetTextColor(148, 163, 184);     // #94a3b8
        $this->Cell(0, 7, $this->month_label, 0, 1, 'L');

        // Generated date (right-aligned)
        $this->SetXY(0, 14);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(205, 7, 'Generated: ' . date('d M Y'), 0, 0, 'R');

        $this->Ln(18);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFillColor(2, 6, 23);
        $this->Rect(0, $this->GetY() - 2, 210, 20, 'F');
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 10, 'WiseInvest  |  Page ' . $this->PageNo() . '  |  Confidential', 0, 0, 'C');
    }

    /** Rounded-corner filled rectangle (approximated with FPDF arcs) */
    function RoundRect(float $x, float $y, float $w, float $h, float $r, string $style = 'F') {
        $this->RoundedRect($x, $y, $w, $h, $r, $style);
    }

    /** Native FPDF doesn't have RoundedRect, we fake it with a simple filled rect + corner arcs */
    function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = 'F') {
        $k   = $this->k;
        $hp  = $this->h;
        $op  = ($style === 'F') ? 'f' : (($style === 'FD' || $style === 'DF') ? 'B' : 'S');
        $MyArc = '%.3F %.3F %.3F %.3F %.3F %.3F c ';
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $this->_out(sprintf('%.2F %.2F l', ($x + $w - $r) * $k, ($hp - $y) * $k));
        $this->_Arc($x + $w - $r, $y + $r, $r, 270, 360);
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - ($y + $h - $r)) * $k));
        $this->_Arc($x + $w - $r, $y + $h - $r, $r, 0, 90);
        $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($x + $r, $y + $h - $r, $r, 90, 180);
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - ($y + $r)) * $k));
        $this->_Arc($x + $r, $y + $r, $r, 180, 270);
        $this->_out($op);
    }

    function _Arc(float $x1, float $y1, float $r, float $a1, float $a2) {
        $k  = $this->k;
        $hp = $this->h;
        if ($a2 < $a1) $a2 += 360;
        $op = null;
        while ($a1 < $a2) {
            $a  = $a1 + 90;
            if ($a > $a2) $a = $a2;
            $d1 = $a1 * M_PI / 180;
            $d2 = $a  * M_PI / 180;
            $arc = 4 / 3 * (1 - cos(($d2 - $d1) / 2)) / sin(($d2 - $d1) / 2);
            $sx  = $x1 + $r * cos($d1);
            $sy  = $y1 - $r * sin($d1);
            $ex  = $x1 + $r * cos($d2);
            $ey  = $y1 - $r * sin($d2);
            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c',
                ($sx + $arc * ($x1 + $r * cos($d1 + M_PI/2) - $sx)) * $k,
                ($hp - ($sy + $arc * ($y1 - $r * sin($d1 + M_PI/2) - $sy))) * $k,
                ($ex + $arc * ($x1 + $r * cos($d2 - M_PI/2) - $ex)) * $k,
                ($hp - ($ey + $arc * ($y1 - $r * sin($d2 - M_PI/2) - $ey))) * $k,
                $ex * $k,
                ($hp - $ey) * $k
            ));
            $a1 = $a;
        }
    }

    /** Summary card */
    function SummaryCard(float $x, float $y, float $w, string $label, string $value, array $accent, array $bg) {
        // Card background
        $this->SetFillColor(...$bg);
        $this->RoundedRect($x, $y, $w, 28, 3, 'F');
        // Accent left bar
        $this->SetFillColor(...$accent);
        $this->Rect($x, $y, 3, 28, 'F');
        // Label
        $this->SetXY($x + 7, $y + 5);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(148, 163, 184);
        $this->Cell($w - 10, 6, $label, 0, 1, 'L');
        // Value
        $this->SetX($x + 7);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->Cell($w - 10, 10, $value, 0, 1, 'L');
    }
}

// ── Build PDF ─────────────────────────────────────────────────────────────────
$pdf = new BudgetPDF('P', 'mm', 'A4');
$pdf->month_label = $month_label;
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

$page_w  = 210;
$margin  = 14;
$content = $page_w - $margin * 2;

// ── Summary Cards (2 rows × 3 cols, then last row) ────────────────────────────
$cards = [
    ['Total Income',    '$' . number_format($total_income,  2), [16,185,129],  [2,6,23]],
    ['Total Expense',   '$' . number_format($total_expense, 2), [239,68,68],   [2,6,23]],
    ['Balance',         '$' . number_format($balance,       2), [59,130,246],  [2,6,23]],
    ['Savings Goal',    '$' . number_format($goal,          2), [212,175,55],  [2,6,23]],
    ['Balance vs Goal', '$' . number_format($difference,    2), [139,92,246],  [2,6,23]],
];

$card_w   = ($content - 8) / 3;
$card_gap = 4;
$start_y  = $pdf->GetY() + 4;

foreach ($cards as $i => $c) {
    $col = $i % 3;
    $row = intdiv($i, 3);
    $cx  = $margin + $col * ($card_w + $card_gap);
    $cy  = $start_y + $row * 32;
    $pdf->SummaryCard($cx, $cy, $card_w, $c[0], $c[1], $c[2], $c[3]);
}

$pdf->SetY($start_y + ceil(count($cards) / 3) * 32 + 4);

// ── Progress Bar ──────────────────────────────────────────────────────────────
if ($goal > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(148, 163, 184);
    $pdf->SetX($margin);
    $pdf->Cell($content, 7, 'Savings Progress: ' . round($progress, 1) . '%', 0, 1, 'L');

    $bar_y  = $pdf->GetY();
    $bar_h  = 6;
    // Track
    $pdf->SetFillColor(30, 41, 59);
    $pdf->RoundedRect($margin, $bar_y, $content, $bar_h, 3, 'F');
    // Fill
    $fill_w = max(0, ($content * $progress) / 100);
    if ($fill_w > 0) {
        $pdf->SetFillColor(34, 197, 94);
        $pdf->RoundedRect($margin, $bar_y, $fill_w, $bar_h, 3, 'F');
    }
    $pdf->Ln($bar_h + 8);
}

// ── Transactions Table ────────────────────────────────────────────────────────
if (!empty($transactions)) {
    // Section heading
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(212, 175, 55);
    $pdf->SetX($margin);
    $pdf->Cell($content, 8, 'Transaction Details', 0, 1, 'L');

    // Header row
    $col_w = [$content * 0.38, $content * 0.22, $content * 0.18, $content * 0.22];
    $headers = ['Description', 'Category', 'Type', 'Amount'];

    $pdf->SetFillColor(15, 23, 42);   // #0f172a
    $pdf->SetTextColor(212, 175, 55);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetX($margin);
    foreach ($headers as $k => $h) {
        $pdf->Cell($col_w[$k], 9, $h, 0, 0, 'L', true);
    }
    $pdf->Ln();

    // Divider
    $pdf->SetDrawColor(30, 41, 59);
    $pdf->SetLineWidth(0.3);
    $pdf->Line($margin, $pdf->GetY(), $margin + $content, $pdf->GetY());

    // Rows
    $row_num = 0;
    foreach ($transactions as $t) {
        $is_income = ($t['type'] ?? '') === 'in';
        $amt       = floatval($t['amount'] ?? 0);

        // Alternating row bg
        if ($row_num % 2 === 0) {
            $pdf->SetFillColor(2, 6, 23);
        } else {
            $pdf->SetFillColor(10, 18, 35);
        }

        $pdf->SetTextColor(226, 232, 240);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX($margin);

        $pdf->Cell($col_w[0], 8, mb_strimwidth($t['desc'] ?? '', 0, 40, '...'), 0, 0, 'L', true);
        $pdf->Cell($col_w[1], 8, mb_strimwidth($t['cat']  ?? '', 0, 22, '...'), 0, 0, 'L', true);

        // Type badge colour
        if ($is_income) {
            $pdf->SetTextColor(16, 185, 129);
        } else {
            $pdf->SetTextColor(239, 68, 68);
        }
        $pdf->Cell($col_w[2], 8, $is_income ? 'Income' : 'Expense', 0, 0, 'L', true);

        // Amount
        if ($is_income) {
            $pdf->SetTextColor(16, 185, 129);
        } else {
            $pdf->SetTextColor(239, 68, 68);
        }
        $sign = $is_income ? '+' : '-';
        $pdf->Cell($col_w[3], 8, $sign . '$' . number_format($amt, 2), 0, 0, 'R', true);

        $pdf->Ln();

        // Row separator
        $pdf->SetDrawColor(30, 41, 59);
        $pdf->SetLineWidth(0.2);
        $pdf->Line($margin, $pdf->GetY(), $margin + $content, $pdf->GetY());

        $row_num++;
    }

    $pdf->Ln(4);

    // Totals row
    $pdf->SetFillColor(15, 23, 42);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX($margin);
    $pdf->SetTextColor(148, 163, 184);
    $pdf->Cell($col_w[0] + $col_w[1] + $col_w[2], 9, 'Net Balance', 0, 0, 'L', true);
    $pdf->SetTextColor($balance >= 0 ? 16 : 239, $balance >= 0 ? 185 : 68, $balance >= 0 ? 129 : 68);
    $pdf->Cell($col_w[3], 9, '$' . number_format($balance, 2), 0, 0, 'R', true);
    $pdf->Ln();
}

// ── Goal Status Banner ────────────────────────────────────────────────────────
$pdf->Ln(6);
if ($goal > 0) {
    $on_track = $balance >= $goal;
    if ($on_track) {
        $pdf->SetFillColor(16, 185, 129, 30);
        $pdf->SetFillColor(5, 46, 22);
        $banner_text = 'Goal Achieved!  You are $' . number_format(abs($difference), 2) . ' ahead of your savings goal.';
        $text_color  = [16, 185, 129];
    } else {
        $pdf->SetFillColor(69, 10, 10);
        $banner_text = 'Keep Going!  You need $' . number_format(abs($difference), 2) . ' more to reach your savings goal.';
        $text_color  = [239, 68, 68];
    }
    $by  = $pdf->GetY();
    $pdf->RoundedRect($margin, $by, $content, 12, 3, 'F');
    $pdf->SetXY($margin + 4, $by + 2);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(...$text_color);
    $pdf->Cell($content - 8, 8, $banner_text, 0, 1, 'L');
}

// ── Output ────────────────────────────────────────────────────────────────────
$filename = 'budget-' . ($month ?: date('Y-m')) . '.pdf';
$pdf->Output('D', $filename);
