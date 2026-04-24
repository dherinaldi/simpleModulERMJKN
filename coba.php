<?php

require "database.php";
require "bundle.php";

require "BpjsMrSender.php";
require "dataRahasia.php"; // ini isinya config

//$path = FCPATH . '/bahan/generate-2509250005.json';

$config = [
    "consid"    => dataRahasia('DEV')['consid'],
    "secretkey" => dataRahasia('DEV')['secretkey'],
    "koders"    => dataRahasia('DEV')['koders'],
    "userkey"   => dataRahasia('DEV')['userkey'],
    "base_url"  => dataRahasia('DEV')['base_url'],
];

//var_dump($config);die;

$config = dataRahasia('DEV');
$bpjs   = new BpjsMrSender($config);

$path = __DIR__ . '/bahan/generate-2509250005-modif.json';
//$path = __DIR__ . '/bahan/new_bahan.json';

$json = file_get_contents($path);
$data = json_decode($json, true);

//var_dump($json);die();

$noSep        = "0187R0060426V000005";
$tglSep       = "2026-04-24";

$id = "coba-".$noSep;

$bundle = bundle($id, $noSep, $data);

//var_dump($bundle);die;
//end bungkus

header("Content-Type: application/json");
$tahun = date('Y', strtotime($tglSep));

if (strlen($tahun) > 4) {
    echo "Tahun Kosong";
} elseif (! is_numeric($tahun)) {
    echo "Tahun harus berupa angka";
}
$bulan = date('m', strtotime($tglSep));
if ($bulan == '') {
    echo "Bulan Kosong";
}

// var_dump($bulan,$tahun);die;
$payload = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

echo $payload;
//die;

$result = $bpjs->sendMR($noSep, 2, $bulan, $tahun, $payload);
// DEBUG
echo "<pre>";
print_r($result);
