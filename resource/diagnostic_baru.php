<?php
function diagnostic($encounterId, $pasien, $dokter, $start, $lab)
{
    $reports = [];

    // Kelompokkan berdasarkan kategori
    $grouped = [];

    foreach ($lab as $item) {
        $grouped[$item['category_code']][] = $item;
    }

    foreach ($grouped as $category => $items) {

        $result = [];

        foreach ($items as $row) {

            if ($category == 'RAD') {

                $result[] = [
                    "resourceType"      => "Observation",
                    "id"                => md5($row['loinc']),
                    "issued"            => date('Y-m-d H:i:s'),
                    "effectiveDateTime" => $start,
                    "code"              => [
                        "coding" => [
                            [
                                "system" => "http://loinc.org",
                                "code"   => $row['loinc'],
                                "display"=> $row['display']
                            ]
                        ],
                        "text" => $row['pemeriksaan']
                    ],
                    "conclusion"        => $row['conclusion'],
                    "presentedForm"     => $row['image']
                ];

            } else {

                $result[] = [
                    "resourceType"      => "Observation",
                    "id"                => md5($row['loinc']),
                    "issued"            => date('Y-m-d H:i:s'),
                    "effectiveDateTime" => $start,                    
                    "code"  => [
                        "coding" => [
                            [
                                "system" => "http://loinc.org",
                                "code"   => $row['loinc'],
                                "display"=> $row['display']
                            ]
                        ]
                    ],
                    "text"  => $row['pemeriksaan'],
                    "value" => $row['hasil'].' '.$row['satuan']
                ];
            }
        }

        $reports[] = [
            "resourceType" => "DiagnosticReport",
            "id"           => md5($encounterId.$category),

            "subject"      => [
                "reference" => "Patient/".$pasien['no_rm'],
                "display"   => $pasien['nama']
            ],

            "category"     => [
                "coding" => [
                    [
                        "system"  => "http://hl7.org/fhir/v2/0074",
                        "code"    => $category,
                        "display" => $items[0]['category_display']
                    ]
                ]
            ],

            "status"       => "final",

            "performer"    => [
                [
                    "reference" => "Practitioner/".$dokter['kd'],
                    "display"   => $dokter['nama']
                ]
            ],

            "issued"       => $start,

            "result"       => $result
        ];
    }

    return $reports    ;
}