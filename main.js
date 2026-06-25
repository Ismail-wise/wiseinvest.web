// ============================================================
// MAIN.JS — Page-level scripts (header handled by header.php)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    /* ── Auto-open first calculator tab ── */
    var firstTab = document.querySelector('.calc-tab-btn');
    if (firstTab) firstTab.click();

});

/* ── Calculator tab switch ── */
function switchCalc(calcId, el) {
    document.querySelectorAll('.calc-workspace').forEach(function (c) { c.style.display = 'none'; });
    document.querySelectorAll('.calc-tab-btn').forEach(function (b) { b.classList.remove('active'); });
    var box = document.getElementById('calc-' + calcId);
    if (box) box.style.display = 'block';
    if (el) el.classList.add('active');
}

/* ── Value / output helpers used by calculators ── */
function val(id) {
    var el = document.getElementById(id);
    return el ? parseFloat(el.value) || 0 : 0;
}
function out(id, txt) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = txt;
}

var FC = { annual: 0, mffc: 0 };

function calculateInflationAdjustedExpense() {
    var annual    = parseFloat(document.getElementById('ia-annual').value);
    var years     = parseFloat(document.getElementById('ia-years').value);
    var inflation = parseFloat(document.getElementById('ia-inflation').value) / 100;

    if (!annual || years < 0 || inflation < 0) {
        document.getElementById('ia-result').innerText = 'Please enter valid values.';
        return;
    }

    var futureExpense = annual * Math.pow(1 + inflation, years);
    document.getElementById('ia-result').innerHTML =
        'You will need <strong>' + futureExpense.toFixed(2) + '</strong> per year in ' + years + ' years.';
}
