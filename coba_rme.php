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
require "resource/diagnostic_baru.php";
require "resource/medication.php";
require "resource/composition.php";
require "BpjsMrSender.php";
require "dataRahasia.php"; // ini isinya config

// CONFIG
#var_dump(dataRahasia('DEV')['consid']);die;

$config = [
    "consid"    => dataRahasia('DEV')['consid'],
    "secretkey" => dataRahasia('DEV')['secretkey'],
    "koders"    => dataRahasia('DEV')['koders'],
    "userkey"   => dataRahasia('DEV')['userkey'],
    "base_url"  => dataRahasia('DEV')['base_url'],
];

#var_dump($config);die;

function getSEP($mysqli, $nopen)
{
    $s_query = "SELECT
    pp.NOMOR,
    pp.NORM,
    pp.TANGGAL,
    p.nik,
    tp.RUANGAN,
    mp.NAMA,
    mp.JENIS_KELAMIN,
    mp.TANGGAL_LAHIR,
    `master`.getAlamatPasien(pp.NORM) AS ALAMAT,
    kap.NOMOR AS NOKA,
    k.noSEP,
		k.tglSEP,k.tglRujukan,k.asalRujukan,k.noRujukan, k.ppkRujukan, k.ppkPelayanan,
		k.jenisPelayanan, k.catatan,k.diagAwal,k.poliTujuan, k.eksekutif, k.klsRawat, k.dpjpSKDP, k.dpjpLayan,
		k.noTelp, k.`user`,
		dok.ID, dok.NIP,
		`master`.getNamaLengkapPegawai(dok.NIP) as NAMA_DOKTER
FROM pendaftaran.pendaftaran pp
LEFT JOIN `kemkes-ihs`.patient p ON pp.NORM = p.refId
LEFT JOIN `master`.pasien mp ON mp.NORM = pp.NORM
LEFT JOIN `master`.kartu_asuransi_pasien kap ON kap.NORM = pp.NORM AND kap.jenis = 2
LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = pp.NOMOR
LEFT JOIN pendaftaran.penjamin ppen ON ppen.NOPEN = pp.NOMOR
LEFT JOIN bpjs.kunjungan k ON k.noSEP = ppen.NOMOR
LEFT JOIN master.dokter dok ON tp.DOKTER=dok.ID
CROSS JOIN aplikasi.instansi ins
LEFT JOIN `master`.ppk pk
    ON pk.KODE = ins.PPK
WHERE pp.NOMOR = '$nopen'
  AND pp.STATUS <> 0
LIMIT 1";

    $query = mysqli_query($mysqli, $s_query)
    or die(json_encode([
        "metaData" => [
            "code"    => "500",
            "message" => "Query Error: " . mysqli_error($mysqli),
        ],
        "response" => null,
    ]));

    return $query;
}

$config = dataRahasia('DEV');
$bpjs   = new BpjsMrSender($config);
//default org
$orgId = 'xxxxxxxxx';
//list data pasien format per pasien

$rows = mysqli_num_rows(getSEP($mysqli, '2604240001'));

if ($rows > 0) {
    $data = mysqli_fetch_assoc(getSEP($mysqli, '2604240001'));
//echo $rows;

    $noSep        = $data['noSEP'];
    $tglSep       = date('Y-m-d', strtotime($data['tglSEP']));
    $jnsPelayanan = $data['jenisPelayanan'] == 2 ? "Rawat Jalan" : "Rawat Inap";
    $kelasRawat   = "-";
    $diagnosa     = "E10 - Insulin-dependent diabetes mellitus";
    $noRujukan    = "";
    $noKartu      = $data['NOKA'];
    $nama         = $data['NAMA'];
    $tglLahir     = $data['TANGGAL_LAHIR'];
    $noMr         = $data['NORM'];
    $kelamin      = ($data['JENIS_KELAMIN'] == 1) ? 'male' : 'female';
    $nik          = $data['nik'];
    $alamat       = $data['ALAMAT'];
    $hp           = $data['noTelp'];
    $start        = $data['TANGGAL'];
    $end          = $data['TANGGAL'];
    $ruangan      = $data['RUANGAN'];
    $dok_nip      = $data['NIP'];

}
//end list pasien
$prefix = "rme_bpjs";

$id2         = $noMr;
$encounterId = $noSep . '-' . $tglSep;
$conditionId = $encounterId . '-ci';
$entries     = [];

//die();

/* ------------------ SAMPLE DATA ------------------ */

$pasien = [
    "no_rm"     => $noMr,
    "nama"      => $nama,
    "gender"    => $kelamin,
    "tgl_lahir" => $tglLahir,
    "sep"       => $noSep,
];

#recek lebih dari satu ruangan / organization
$ids     = array_map('intval', explode(',', $ruangan));
$ruangan = implode(',', $ids);

$s_query = "SELECT ru.ID, ru.JENIS, ru.JENIS_KUNJUNGAN, ru.DESKRIPSI as NAMA_RUANG FROM `master`.ruangan as ru WHERE ru.ID in ('$ruangan');";

$query = mysqli_query($mysqli, $s_query)
or die(json_encode([
    "metaData" => [
        "code"    => "500",
        "message" => "Query Error: " . mysqli_error($mysqli),
    ],
    "response" => null,
]));

//list organisasi RS bisa di buat statis jika pasien tertentu
$rows = mysqli_num_rows($query);

$org_list = [];
if ($rows > 0) {
    while ($data = mysqli_fetch_assoc($query)) {
        $org_list[] =
            [
            "id"          => $data['ID'],
            "kode_bpjs"   => "00000000",
            "kode_kemkes" => "kode_sirs",
            "nama"        => $data['NAMA_RUANG'],
            "telp"        => "(0341)777777",
            "use"         => "work",
            "alamat"      => "Jl. Kartini ",
            "kota"        => "KAB. MALANG",
            "provinsi"    => "JAWA TIMUR",
            "kodepos"     => "65212",
            "negara"      => "Indonesia",
        ];
    }
}

foreach ($org_list as $org) {
    $entries[] = entry(organization($org));
}

$s_query = "SELECT dok.ID, dok.NIP,dok.`STATUS`, peg.GELAR_DEPAN, peg.NAMA,peg.GELAR_BELAKANG, peg.TEMPAT_LAHIR, peg.TANGGAL_LAHIR, peg.ALAMAT
FROM `master`.dokter dok
LEFT JOIN `master`.pegawai peg ON peg.NIP = dok.NIP AND peg.PROFESI =4
WHERE peg.NIP = '$dok_nip' ";

$query = mysqli_query($mysqli, $s_query)
or die(json_encode([
    "metaData" => [
        "code"    => "500",
        "message" => "Query Error: " . mysqli_error($mysqli),
    ],
    "response" => null,
]));

//list organisasi RS bisa di buat statis jika pasien tertentu
$rows = mysqli_num_rows($query);

if ($rows > 0) {
//data dokter
    $data       = mysqli_fetch_assoc($query);
    $id_pr      = $data['ID'];
    $sip        = "sip";
    $nik_pr     = "nik";
    $nama_pr    = $data['GELAR_DEPAN'] . " " . $data['NAMA'] . " " . $data['GELAR_BELAKANG'];
    $phone      = "081234567890";
    $email      = "email@email.co.id";
    $address    = "Jl. in aja dulu";
    $city       = "Malang";
    $district   = "Lawang";
    $state      = "Jawa Timur";
    $postalCode = "65212";
    $gender     = "female";
    $birthDate  = "0000-11-01";
//end data dokter

}
//ini asal muasal encounter 
$kunjungan = [
    "no_reg"     => $noSep,
    "no_rm"      => $noMr,
    "tgl_masuk"  => $start,
    "tgl_keluar" => $end,
];

function getICD10($mysqli, $nopen)
{
    $data    = [];
    $s_query = "SELECT DISTINCT p.NOMOR, pr.*, sct.term as STR
		FROM medicalrecord.diagnosa pr
		LEFT JOIN pendaftaran.pendaftaran p ON p.NOMOR = pr.NOPEN
		LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = p.NOMOR
		LEFT JOIN `master`.ruangan r ON r.ID = tp.RUANGAN
		LEFT JOIN `kemkes-ihs`.snomed_ct sct on sct.conceptId = pr.SNOMED_CT_ID
		WHERE p.NOMOR in ($nopen)
		GROUP BY pr.SNOMED_CT_ID";

    $query = mysqli_query($mysqli, $s_query)
    or die(json_encode([
        "metaData" => [
            "code"    => "500",
            "message" => "Query Error: " . mysqli_error($mysqli),
        ],
        "response" => null,
    ]));

    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = [
            'code'    => $row['SNOMED_CT_ID'],
            'display' => $row['STR'],
        ];
    }

    return $data;

}

$diagnosa = getICD10($mysqli, '2604240001');

//ini unutk lab nya
$lab = [
    "loinc"       => "20509-6",
    "pemeriksaan" => "Hemoglobin",
    "hasil"       => "13",
    "satuan"      => "g/dL",
];
//endlab

function getICD9($mysqli, $nopen, $encounterId)
{
    $dataa   = [];
    $s_query = "SELECT DISTINCT p.ID, p.NOPEN,p.SNOMED_CT_ID, sct.term as SCT
		FROM medicalrecord.prosedur p
		LEFT JOIN pendaftaran.pendaftaran pn ON pn.NOMOR = p.NOPEN
		LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = pn.NOMOR
		LEFT JOIN `master`.ruangan r ON r.ID = tp.RUANGAN
		LEFT JOIN `kemkes-ihs`.`procedure` c ON c.refId = p.ID
		LEFT JOIN `kemkes-ihs`.snomed_ct sct on sct.conceptId = p.SNOMED_CT_ID
		WHERE pn.NOMOR in ('$nopen')
		GROUP BY p.SNOMED_CT_ID;";

    $query = mysqli_query($mysqli, $s_query)
    or die(json_encode([
        "metaData" => [
            "code"    => "500",
            "message" => "Query Error: " . mysqli_error($mysqli),
        ],
        "response" => null,
    ]));

    $dataa = [];

    while ($row = mysqli_fetch_assoc($query)) {

        $dataa[] = [
            "encounter" => $encounterId,
            "snom_pr"   => $row['SNOMED_CT_ID'],
            "snom_dsp"  => $row['SCT'],
            "note"      => $row['SCT'],
            "coding"    => [
                [
                    "system"  => "http://snomed.info/sct",
                    "code"    => $row['SNOMED_CT_ID'],
                    "display" => $row['SCT'],
                ],
            ],
        ];
    }

    return ($dataa);

}

//prosedure_area
$procedures = getICD9($mysqli, '2604240001', $encounterId);

#print_r($procedures);die();

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

    [
        "title"   => "Known allergies",
        "system"  => "http://loinc.org",
        "code"    => "48765-2",
        "display" => "Allergies and adverse reactions",
        "text"    => "Known allergies UAT",
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

$s_query = "SELECT DISTINCT far.ID, far.KUNJUNGAN, far.FARMASI, brg.NAMA NAMA_BARANG, far.RACIKAN, far.GROUP_RACIKAN, p.NOMOR, far.TANGGAL, far.JUMLAH, far.DOSIS, far.KETERANGAN, far.HARI, far.FREKUENSI, far.`STATUS`
		FROM layanan.farmasi far
		LEFT JOIN pendaftaran.kunjungan k ON k.NOMOR = far.KUNJUNGAN
		LEFT JOIN pendaftaran.pendaftaran p ON p.NOMOR = k.NOPEN
		LEFT JOIN pendaftaran.tujuan_pasien tp ON tp.NOPEN = p.NOMOR
		LEFT JOIN `master`.ruangan r ON r.ID = tp.RUANGAN
		LEFT JOIN inventory.barang brg on brg.ID = far.FARMASI
		WHERE k.NOPEN in ('2604240001')
		limit 10
		";

$query = mysqli_query($mysqli, $s_query)
or die(json_encode([
    "metaData" => [
        "code"    => "500",
        "message" => "Query Error: " . mysqli_error($mysqli),
    ],
    "response" => null,
]));

//list organisasi RS bisa di buat statis jika pasien tertentu
$rows = mysqli_num_rows($query);

//medication part
$listObat = [];

if ($rows > 0) {
    while ($data = mysqli_fetch_assoc($query)) {
        $listObat[] = [
            "id_resep"    => $data['ID'],
            "kode_obat"   => $data['FARMASI'],
            "kode_satuan" => "",
            "nama_obat"   => $data['NAMA_BARANG'],
            "satuan"      => "",
            "jumlah"      => $data['JUMLAH'],
            "aturan"      => $data['DOSIS'] . " " . $data['KETERANGAN'],
            "frequency"   => $data['FREKUENSI'],
        ];
    }
}

#var_dump($listObat);die();

//ini dokternya sudah ada di atas, tp iseng aja nambah di sini
$dokter = [
    "kd"   => $id_pr,
    "nama" => $nama_pr,
    "org"  => "id org header/ rs nya",
];
$data_med  = buildMedicationResource($listObat, $pasien, $dokter, $diagnosa);
$entries[] = entry($data_med);

#var_dump($data_med);die();
//end medic

#diagnostic report Rad dan Lab 

function diagnostic_data($mysqli, $nopen)
{
    $data    = [];
    $s_query = "SELECT  hl.ID, hl.KUNJUNGAN, hl.TINDAKAN, hl.TANGGAL , hrad.TINDAKAN_MEDIS, hrad.KLINIS, hrad.KESAN, hrad.USUL, hrad.HASIL, hrad.BTK, hrad.KRITIS,ttl.LOINC_TERMINOLOGI LOINC, tin.NAMA NAMA_TINDAKAN, lt.nama_pemeriksaan NAMA_PEMERIKSAAN
   FROM layanan.tindakan_medis hl
	 	LEFT JOIN layanan.hasil_rad hrad ON hrad.TINDAKAN_MEDIS = hl.ID
		LEFT JOIN `master`.dokter dok ON dok.ID = hrad.DOKTER
		LEFT JOIN pendaftaran.kunjungan kjgn ON kjgn.NOMOR = hl.KUNJUNGAN
		LEFT JOIN `kemkes-ihs`.tindakan_to_loinc ttl ON ttl.TINDAKAN = hl.TINDAKAN
        LEFT JOIN `kemkes-ihs`.loinc_terminologi lt ON lt.ID = ttl.LOINC_TERMINOLOGI
		LEFT JOIN `master`.tindakan tin ON tin.ID = hl.TINDAKAN
		WHERE kjgn.NOPEN in ('$nopen') AND hrad.ID IS NOT NULL AND tin.JENIS = 7
		group by hrad.TINDAKAN_MEDIS
		;
		;";

    $query = mysqli_query($mysqli, $s_query)
    or die(json_encode([
        "metaData" => [
            "code"    => "500",
            "message" => "Query Error: " . mysqli_error($mysqli),
        ],
        "response" => null,
    ]));

    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = [
            "loinc"            => $row['LOINC'],
            "pemeriksaan"      => $row['NAMA_TINDAKAN'],
            "hasil"            => $row['HASIL'],
            "satuan"           => "-",
            "display"          => $row['NAMA_PEMERIKSAAN'],
            "category_code"    => "RAD",
            "category_display" => "RADIOLOGI",
            "image"            => [
                [
                    "comment" => "",
                    "link"    => [
                        "reference" => "https:\/\/api.rsudlawang.com\/RSUD-api\/QRCode\/cdce7f934ff75fd572840339d4508cff.png",
                        "display"   => "Laporan Radiologi",
                    ],
                ],
            ],
            "conclusion"       => $row['HASIL'],
        ];
    }
    return $data;

}

function diagnostic_data_lab($mysqli, $nopen)
{
    $data    = [];
    $s_query = "select hl.ID, hl.KUNJUNGAN, hl.TINDAKAN, hl.TANGGAL,ttl.LOINC_TERMINOLOGI LOINC,lt.nama_pemeriksaan NAMA_PEMERIKSAAN,lt.display DISPLAY_PEMERIKSAAN, tin.NAMA,hlab.HASIL, hlab.NILAI_NORMAL, hlab.SATUAN, hlab.KETERANGAN, tin.NAMA NAMA_TINDAKAN

FROM layanan.tindakan_medis hl
				LEFT JOIN layanan.hasil_lab hlab on hlab.TINDAKAN_MEDIS = hl.ID
				LEFT JOIN layanan.catatan_hasil_lab chl ON chl.KUNJUNGAN = hl.KUNJUNGAN
				LEFT JOIN `kemkes-ihs`.tindakan_to_loinc ttl ON ttl.TINDAKAN = hl.TINDAKAN
				LEFT JOIN `kemkes-ihs`.loinc_terminologi lt ON lt.ID = ttl.LOINC_TERMINOLOGI
				LEFT JOIN pendaftaran.kunjungan kjgn ON kjgn.NOMOR = hl.KUNJUNGAN
				LEFT JOIN `master`.tindakan tin ON tin.ID = hl.TINDAKAN
				WHERE kjgn.NOPEN in ('$nopen') and tin.JENIS =8 and hlab.HASIL !='';
		;
		;";

    $query = mysqli_query($mysqli, $s_query)
    or die(json_encode([
        "metaData" => [
            "code"    => "500",
            "message" => "Query Error: " . mysqli_error($mysqli),
        ],
        "response" => null,
    ]));

    /* "loinc"            => "26604007",
    "pemeriksaan"      => "Darah Lengkap Analiser",
    "hasil"            => "13",
    "satuan"           => "g/dL",
    "display"          => "Complete blood count",
    "category_code"    => "LAB",
    "category_display" => "Laboratory",
    "image"            => '',
    "conclusion"       => "hasil bacaan Darah Lengkap"; */

    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = [
            "loinc"            => $row['LOINC'],
            "pemeriksaan"      => $row['NAMA_TINDAKAN'],
            "hasil"            => $row['HASIL'],
            "satuan"           => $row['SATUAN'],
            "display"          => $row['DISPLAY_PEMERIKSAAN'],
            "category_code"    => "LAB",
            "category_display" => "Laboratory",
            "conclusion"       => "hasil bacaan Lab"
        ];
    }
    return $data;

}

$lab = [];

// tambah radiologi
$lab = array_merge(
    $lab,
    diagnostic_data($mysqli, '2604240001')
);

$lab = array_merge(
    $lab,
    diagnostic_data_lab($mysqli, '2604240001')
);




$entries[] = entry(diagnostic($encounterId, $pasien, $dokter, $start, $lab));

echo "<pre>";
#var_dump(entry(diagnostic($encounterId, $pasien, $dokter, $start, $lab)));


//print_r($entries);

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
// $entries[]=entry(diagnostic($noMr));

$entries[] = entry(composition($compositionId, $noMr,
    $nama,
    $encounterId,
    $id_pr,
    $nama_pr, $start, $sectionData));

/* echo "<pre>";
echo json_encode(composition($compositionId, $noMr,
    $nama,
    $encounterId,
    $id_pr,
    $nama_pr, $start, $sectionData));
echo "</pre>";
die(); */

/* ------------------ BUNDLE ------------------ */

#$cek_data = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

#echo $cek_data;die();

$id = $prefix . '-' . $noSep;
//bungkus disini
$bundle = bundle($id, $noSep, $entries);
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

#$result = $bpjs->sendMR($noSep, 2, $bulan, $tahun, $payload);

// DEBUG
echo "<pre>";
print_r($result);
