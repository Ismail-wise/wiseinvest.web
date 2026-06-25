<?php
/**
 * Run this script once from the command line to download FPDF:
 *   php lib/fpdf/install.php
 *
 * Or use Composer:
 *   composer require setasign/fpdf
 *   Then update the require path in generate_budget_pdf.php to:
 *   require __DIR__ . '/vendor/setasign/fpdf/fpdf.php';
 */

$url  = 'http://www.fpdf.org/en/script/dl.php?id=dl&f=fpdf18.zip';
$dest = __DIR__;

echo "Downloading FPDF...\n";

$zip_data = @file_get_contents($url);
if (!$zip_data) {
    // Fallback: direct file download
    $url      = 'https://raw.githubusercontent.com/Setasign/FPDF/master/fpdf.php';
    $php_data = @file_get_contents($url);
    if (!$php_data) {
        die("Could not download FPDF. Please manually download fpdf.php from http://fpdf.org and place it in " . $dest . "\n");
    }
    file_put_contents($dest . '/fpdf.php', $php_data);
    echo "FPDF downloaded successfully to " . $dest . "/fpdf.php\n";
    exit(0);
}

$tmp = tempnam(sys_get_temp_dir(), 'fpdf') . '.zip';
file_put_contents($tmp, $zip_data);

$zip = new ZipArchive();
if ($zip->open($tmp) !== true) {
    die("Failed to open zip. Please manually download fpdf.php from http://fpdf.org\n");
}

for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (basename($name) === 'fpdf.php') {
        copy("zip://$tmp#$name", $dest . '/fpdf.php');
        echo "FPDF installed at " . $dest . "/fpdf.php\n";
        break;
    }
}

$zip->close();
unlink($tmp);
