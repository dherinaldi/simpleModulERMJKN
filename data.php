<?php
require "database.php";

$tanggal = isset($_REQUEST['tanggal']) && strtotime($_REQUEST['tanggal'])
    ? date('Y-m-d', strtotime($_REQUEST['tanggal']))
    : date('Y-m-d');
$tanggal_akhir = isset($_REQUEST['tanggal_akhir']) && strtotime($_REQUEST['tanggal_akhir'])
    ? date('Y-m-d', strtotime($_REQUEST['tanggal_akhir']))
    : date('Y-m-d');

$s_tanggal = " AND (DATE_FORMAT(res.TANGGALKUNJUNGAN,'%Y-%m-%d') BETWEEN '$tanggal' AND '$tanggal_akhir')";

$s_where = '';

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
WHERE 0=0
AND pp.TANGGAL BETWEEN '2026-03-01 00:00:00' AND '2026-04-30 23:59:59'
  AND pp.STATUS <> 0
#LIMIT 100
;";

#echo $s_query;

$query = mysqli_query($mysqli, $s_query)
or die(json_encode([
    "metaData" => [
        "code"    => "500",
        "message" => "Query Error: " . mysqli_error($mysqli),
    ],
    "response" => null,
]));

$rows = mysqli_num_rows($query);

#echo $rows;

if ($rows > 0) {
    while ($data = mysqli_fetch_assoc($query)) {
        $hasil[] = [
            "NOPEN"          => $data['NOMOR'],
            "TANGGAL"        => $data['TANGGAL'],
            "NORM"           => $data['NORM'],
            "NIK"            => $data['nik'],
            "RUANGAN"        => $data['RUANGAN'],
            "NAMA"           => $data['NAMA'],
            "JENIS_KELAMIN"  => $data['JENIS_KELAMIN'],
            "NOKA"           => $data['NOKA'],
            "noSEP"          => $data['noSEP'],
            "tglSEP"         => $data['tglSEP'],
            "tglRujukan"     => $data['tglRujukan'],
            "asalRujukan"    => $data['asalRujukan'],
            "noRujukan"      => $data['noRujukan'],
            "ppkRujukan"     => $data['ppkRujukan'],
            "ppkPelayanan"   => $data['ppkPelayanan'],
            "jenisPelayanan" => $data['jenisPelayanan'],
            "catatan"        => $data['catatan'],
            "diagAwal"       => $data['diagAwal'],
            "poliTujuan"     => $data['poliTujuan'],
            "klsRawat"       => $data['klsRawat'],
            "dpjpSKDP"       => $data['dpjpSKDP'],
            "dpjpLayan"      => $data['dpjpLayan'],
            "NIP_DOKTER"     => $data['NIP'],
            "NAMA_DOKTER"    => $data['NAMA_DOKTER'],
        ];
    }
} else {
    $hasil[] = [
        "NOPEN"          => '',
        "TANGGAL"        => '',
        "NORM"           => '',
        "NIK"            => '',
        "RUANGAN"        => '',
        "NAMA"           => '',
        "JENIS_KELAMIN"  => '',
        "NOKA"           => '',
        "noSEP"          => '',
        "tglSEP"         => '',
        "tglRujukan"     => '',
        "asalRujukan"    => '',
        "noRujukan"      => '',
        "ppkRujukan"     => '',
        "ppkPelayanan"   => '',
        "jenisPelayanan" => '',
        "catatan"        => '',
        "diagAwal"       => '',
        "poliTujuan"     => '',
        "klsRawat"       => '',
        "dpjpSKDP"       => '',
        "dpjpLayan"      => '',
        "NIP_DOKTER"     => '',
        "NAMA_DOKTER"    => '',
    ];
}

$output = [
    "metaData" => [
        "code"    => "200",
        "message" => "Sukses",
    ],
    "response" => [
        "hasil" => $hasil,
    ],
];

header('Content-Type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT);
