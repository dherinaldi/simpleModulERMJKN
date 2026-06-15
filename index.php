<?php
require "database.php";
require "bundle.php";

require "resource/patient.php";
require "resource/practitioner.php";
require "resource/organization.php";
require "resource/encounter.php";
require "resource/condition.php";
require "resource/procedure.php";
require "resource/observation.php";
require "resource/diagnostic.php";
require "resource/medication.php";
require "resource/composition.php";
require "BpjsMrSender.php";
require "dataRahasia.php"; // ini isinya config

// CONFIG
//var_dump(dataRahasia('DEV')['consid']);die;

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
//default org
$orgId = 'xxxxxxxxx';
//list data pasien format per pasien
$noSep        = "0187R0060426V000005";
$tglSep       = "2026-04-24";
$jnsPelayanan = "R.Jalan";
$kelasRawat   = "-";
$diagnosa     = "S83.3 - Tear of articular cartilage of knee, current";
$noRujukan    = "";
$noKartu      = "0002033378673";
$nama         = "ABDUL GAFAR";
$tglLahir     = "1960-06-03";
$noMr         = "00150457";
$kelamin      = "male";
$nik          = "1308042002850002";
$alamat       = "Alamat nya di sini";
$hp           = '081234567890';
$start        = "2026-04-15 08:55:25";
$end          = "2026-04-15 10:25:30";

$prefix = "rme_bpjs";
//end list pasien

$id2         = $noMr;
$encounterId = $noSep . '-' . $tglSep;
$conditionId = $encounterId . '-ci';
$entries     = [];

/* ------------------ SAMPLE DATA ------------------ */

$pasien = [
    "no_rm"     => $noMr,
    "nama"      => $nama,
    "gender"    => $kelamin,
    "tgl_lahir" => $tglLahir,
    "sep"       => $noSep,
];

//list organisasi RS bisa di buat statis jika pasien tertentu
$org_list = [
    [
        "id"          => "kode-RS",
        "kode_bpjs"   => "00000000",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "RSU UTAMA",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",
    ],
    [
        "id"          => "kode-IGD",
        "kode_bpjs"   => "000000",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "INSTALASI GAWAT DARURAT",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ],
    [
        "id"          => "kode_rs-RJ",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "RAWAT JALAN",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ],
    [
        "id"          => "kode_rs-RB",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "RUANG BERSALIN",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ]
    ,
    [
        "id"          => "kode_rs-FARMGD",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "FARMASI IGD",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ]
    ,
    [
        "id"          => "kode_rs-LAB",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "LABORATORIUM",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ]
    ,
    [
        "id"          => "kode_rs-RO",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "RADIOLOGI",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ]
    ,
    [
        "id"          => "kode_rs-HD",
        "kode_bpjs"   => "kode_rs",
        "kode_kemkes" => "kode_sirs",
        "nama"        => "RUANG HEMODIALISIS",
        "telp"        => "(0272)2233444",
        "use"         => "work",
        "alamat"      => "Jl. in dulu aja ",
        "kota"        => "KAB. KLATEN",
        "provinsi"    => "JAWA TENGAH",
        "kodepos"     => "57463",
        "negara"      => "Indonesia",

    ],

];

foreach ($org_list as $org) {
    $entries[] = entry(organization($org));
}
//data dokter
$id_pr      = "123456789";
$sip        = "sip";
$nik_pr     = "nik";
$nama_pr    = "MUSA ARAFAH";
$phone      = "09999999";
$email      = "email@email.co.id";
$address    = "Jl. in aja dulu";
$city       = "Klaten";
$district   = "Malang";
$state      = "JAWA TIMUR";
$postalCode = "53146";
$gender     = "female";
$birthDate  = "0000-11-01";
//end data dokter

//ini asal muasal encounter 
$kunjungan = [
    "no_reg"     => $noSep,
    "no_rm"      => $noMr,
    "tgl_masuk"  => $start,
    "tgl_keluar" => $end,
];

$diagnosa = [
    [
        "code"    => "156515005",
        "display" => "Cartilage tear - knee",

    ],
    [
        "code"    => "433146000",
        "display" => "Chronic kidney disease stage 5 (disorder)",

    ],
    [
        "code"    => "302497006",
        "display" => "Haemodialysis",

    ],
    [
        "code"    => "M16",
        "display" => "Coxarthrosis [arthrosis of hip]",
    ],
    [
        "code"    => "M43",
        "display" => "Other deforming dorsopathies",
    ],
    [
        "code"    => "M43.0",
        "display" => "Spondylolysis",
    ],

];

//prosedure_area
$procedures = [
    [
        "encounter" => $encounterId,
        "snom_pr"   => "310178001",
        "snom_dsp"  => "Hospital Pharmacist",
        "note"      => "APOTIK/FARMASI UGD",
        "coding"    => [
            [
                "system"  => "https://fhir.rsini.co.id/procedure",
                "code"    => "431.01",
                "display" => "FARMASI UGD",
            ],
        ],
    ],
    [
        "encounter" => $encounterId,
        "snom_pr"   => "62247001",
        "snom_dsp"  => "General Medical Practitioner",
        "note"      => "ADMINISTRASI - BPJS",
        "coding"    => [
            [
                "system"  => "http://snomed.info/sct",
                "code"    => "14734007",
                "display" => "Administrative procedure (procedure)",
            ],
            [
                "system"  => "https://fhir.rsini.co.id/procedure",
                "code"    => "500",
                "display" => "ADMINISTRASI - BPJS",
            ],
        ],
    ],
    [
        "encounter" => $encounterId,
        "snom_pr"   => "404940007",
        "snom_dsp"  => "Nephrologist",
        "note"      => "ADMINISTRASI - BPJS",
        "coding"    => [
            [
                "system"  => "http://snomed.info/sct",
                "code"    => "302497006",
                "display" => "Hemodialysi (procedure)",
            ],
            [
                "system"  => "https://fhir.rsini.co.id/procedure",
                "code"    => "KD billing",
                "display" => "Hemodialisis Single Use",
            ],
        ],
    ],
];

$result = Procedures(
    $orgId,
    $noMr,
    $nama,
    $nama_pr,
    $id_pr,
    $start,
    $end,
    $procedures
);
$entries[] = $result;
//end procedure

//composition part
$sectionData = [
    [
        "title"   => "Reason for admission",
        "system"  => "http://loinc.org",
        "code"    => "29299-5",
        "display" => "Reason for visit Narrative",
        "text"    => "Pasien datang dengan keluhan tidak enak badan",
        "entry"   => [
            ["reference" => "Encounter/" . $encounterId],
        ],
    ],

    [
        "title"   => "Chief complaint",
        "system"  => "http://loinc.org",
        "code"    => "10154-3",
        "display" => "Chief complaint Narrative",
        "text"    => "diabetes type 1B",
        "entry"   => [
            ["reference" => "Encounter/" . $encounterId],
        ],
    ],
];
//condition part
$result = Conditions($orgId, $noMr, $diagnosa, $start);
foreach ($result["conditions"] as $c) {
    $entries[] = $c;
}

$compositionId     = $noSep . '-' . $start;
$encounter_display = "Admitted to UGD , RS INI SAJA between $start and $end";

//medication part
$listObat = [
    [
        "id_resep"    => "isisaja id resep",
        "nama_obat"   => "Mersibion INJ",
        "kode_obat"   => "I4018",
        "kode_satuan" => "INJECTION",
        "satuan"      => "INJ",
        "jumlah"      => 1,
        "aturan"      => "1,,,",
        "frequency"   => 1,
    ],
    [
        "id_resep"    => "isisaja id resep",
        "nama_obat"   => "LANTUS INJ JKN",
        "kode_obat"   => "KDOBT2",
        "kode_satuan" => "INJECTION",
        "satuan"      => "IU",
        "jumlah"      => 1,
        "aturan"      => "1 X Sehari 12 IU,,,",
        "frequency"   => 1,
    ],
];
//ini dokternya sudah ada di atas, tp iseng aja nambah di sini
$dokter = [
    "kd"   => $id_pr,
    "nama" => $nama_pr,
    "org"  => "id org header/ rs nya",
];
$data_med  = buildMedicationResource($listObat, $pasien, $dokter, $diagnosa);
$entries[] = entry($data_med);
//end medic

//ini unutk lab nya
// "code": "26604007",  "display": "Complete blood count"                },                "text": "Darah Lengkap Analiser"

$lab = [
    [
        "loinc"            => "20509-6",
        "pemeriksaan"      => "Hemoglobin",
        "hasil"            => "13",
        "satuan"           => "g/dL",
        "display"          => "Hemoglobin [Mass/volume] in Blood by calculation",
        "category_code"    => "LAB",
        "category_display" => "Laboratory",
        "image"=>'',
        "conclusion"=>"hasil bacaan HB"
    ],
    [
        "loinc"            => "26604007",
        "pemeriksaan"      => "Darah Lengkap Analiser",
        "hasil"            => "13",
        "satuan"           => "g/dL",
        "display"          => "Complete blood count",
        "category_code"    => "LAB",
        "category_display" => "Laboratory",
        "image"=>'',
        "conclusion"=>"hasil bacaan Darah Lengkap"
    ],
    [
        "loinc"            => "45036003",
        "pemeriksaan"      => "USG Abdomen",
        "hasil"            => "-",
        "satuan"           => "-",
        "display"          => "Ultrasonography of abdomen",
        "category_code"    => "RAD",
        "category_display" => "RADIOLOGI",
        "image"=> [
            [
                "comment"=>"",
                "link"=>[
                    "reference"=>"https:\/\/simgos.sukamarakab.go.id:8888\/RSUD-api\/QRCode\/3a842997e6380e4dc4db4a3f12c59e28.png",
                "display"=>"Laporan Radiologi"
                ]
            ]
        ],
        "conclusion"=>"hasil bacaan USG nya dunk "
    ],
];
//endlab

$data_lab  = diagnostic($encounterId, $pasien, $dokter, $start, $lab);
$entries[] = $data_lab;

/* ------------------ GENERATE RESOURCE ------------------ */

$entries[] = entry(patient($id2, $noMr, $noKartu, $nik, $nama, $kelamin, $tglLahir, $hp, $alamat));
$entries[] = entry(practitioner($id_pr,
    $sip,
    $nik_pr,
    $nama_pr,
    $phone,
    $email,
    $address,
    $city,
    $district,
    $state,
    $postalCode,
    $gender,
    $birthDate));
$entries[] = entry(encounter($encounterId, $id2, $nama, $noSep, $start, $end, $conditionId));
// $entries[]=entry(procedure($noMr));
// $entries[]=entry(observation($lab));
//$entries[]=entry(diagnostic($noMr));
$entries[] = entry(composition($compositionId, $noMr,
    $nama,
    $encounterId,
    $id_pr,
    $nama_pr, $start, $sectionData));

/* ------------------ BUNDLE ------------------ */

$id = $prefix . '-' . $noSep;
//bungkus disini

$bundle = bundle($id, $noSep, $entries);

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
$payload = json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

echo $payload;
die;

$result = $bpjs->sendMR($noSep, 2, $bulan, $tahun, $payload);
// DEBUG
echo "<pre>";

print_r($result);