<?php
function diagnostic($encounterId, $pasien, $dokter, $start)
{
    $items = [];

    $items =
        [
        [
            "resourceType" => "DiagnosticReport",
            "id"           => "1413R001-6207033-22-cfecdb27-6f63-4854-f3ef-915e2e980c31",
            "subject"      => [
                "reference" => "Patient/1413R001-6207033-22-8580d657-e29c-423a-e111-db6f9beedc65",
                "display"   => "ZULIYATI",
                "noSep"     => "1413R0010925V000031",
            ],
            "category"     => [
                "coding" => [
                    "system"  => "http://hl7.org/fhir/v2/0074",
                    "code"    => "RAD",
                    "display" => "Radiology",
                ],
            ],
            "status"       => "final",
            "performer"    => [
                [
                    "reference" => "Organization/xxx",
                    "display"   => "Ultrasonografi",
                ],
            ],
            "result"       => [
                [
                    "resourceType"      => "Observation",
                    "id"                => "2509230001190",
                    "text"              => [
                        "div" => "Disimpulkan adanya gawat janin",
                    ],
                    "issued"            => "2025-09-23 11:25:29",
                    "effectiveDateTime" => "2025-09-23 11:13:26",
                    "code"              => [
                        "text" => "USG Abdomen",
                    ],
                    "conclusion"        => "Usia kehamilan 32 minggu, air ketuban sangat sedikit, DJJ cepat",
                ],
            ],
        ],

        [
            "resourceType" => "DiagnosticReport",
            "id"           => "1413R001-6207033-22-81374713-d991-042a-0e18-865aa693cc24",
            "category"     => [
                "coding" => [
                    "code"    => "LAB",
                    "display" => "Laboratory",
                ],
            ],
            "result"       => [
                [
                    "id"             => "1",
                    "text"           => "Urine Lengkap",
                    "value"          => "2 mg/dL",
                    "interpretation" => "Low",
                ],
                [
                    "id"             => "2",
                    "text"           => "Trombosit",
                    "value"          => "180",
                    "interpretation" => "Normal",
                ],
                [
                    "id"             => "3",
                    "text"           => "Leukosit",
                    "value"          => "15",
                    "interpretation" => "High",
                ],
                [
                    "id"             => "4",
                    "text"           => "Hemoglobin",
                    "value"          => "11",
                    "interpretation" => "Low",
                ],
            ],
        ],

        [
            "resourceType" => "DiagnosticReport",
            "id"           => "d41d8cd9-8f00-b204-e980-0998ecf8427e",
            "category"     => [
                "coding" => [
                    "code"    => "SUR",
                    "display" => "Surgery",
                ],
            ],
            "result"       => [
                [
                    "text"       => "Seksio Sesaria",
                    "conclusion" => "Stabil",
                ],
            ],
        ],
    ];

    return [
        "resource" => $items,
    ];

}
