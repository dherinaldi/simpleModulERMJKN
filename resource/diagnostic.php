<?php
function diagnostic($encounterId, $pasien, $dokter, $start, $lab)
{
    $items = [];   

    foreach ($lab as $i => $lb) {
        $items[] = [
            "resourceType" => "DiagnosticReport",
            "id"           => $encounterId,
            "status"       => "final",
            "subject"      => [
                "reference" => "Patient/kd_faskes-koderskemkes-1-8782a056-6a7c-4e15-893c-e94bb6128046",
                "display"   => $pasien['nama'],
                "noSep"     => $pasien['sep'],
            ],
            "category"     => [
                "coding" => [
                    "system"  => "http://hl7.org/fhir/v2/0074",
                    "code"    => $lb['category_code'],
                    "display" => $lb['category_display'],
                ],
                "text"   => null,
            ],
            "performer"    => [
                [
                    "reference" => "Organization/1010-LAB",
                    "display"   => "LABORATORIUM",
                ],
            ],
            "result"       => [
                [
                    "resourceType"      => "Observation",
                    "id"                => "L260211001",
                    "status"            => "final",
                    "text"              => [
                        "status" => "generated",
                        "div"    => "<div>Pemeriksaan: " . $lb['pemeriksaan'] . "</div>",
                    ],
                    "issued"            => $start,
                    "effectiveDateTime" => $start,
                    "code"              => [
                        "coding" => [
                            "system"  => "http://loinc.org",
                            "code"    => $lb['loinc'],
                            "display" => $lb['display'],
                        ],
                        "text"   => $lb['pemeriksaan'],
                    ],
                    "performer"         => [
                        "reference" => "Practitioner/" . $dokter['kd'],
                        "display"   => $dokter['nama'],
                    ],
                    "conclusion"        => "normal",
                    "valueQuantity"     => [
                        "value"      => $lb['hasil'],
                        "comparator" => null,
                        "unit"       => $lb['satuan'],
                        "system"     => "",
                        "code"       => "",
                    ],
                    "referenceRange"    => [
                        "low"  => [
                            "value" => "",
                            "unit"  => "",
                        ],
                        "high" => [
                            "value" => "",
                            "unit"  => "",
                        ],
                    ],
                    "interpretation"    => [
                        "coding" => [
                            "system"  => "http://hl7.org/fhir/v2/0078",
                            "code"    => "H",
                            "display" => "High",
                        ],
                        "text"   => "H",
                    ],
                    "image"             => $lb['image'],

                ],
            ],

        ];
    }

    return [
        "resource" => $items,
    ];

}
