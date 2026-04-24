<?php
function diagnostic($encounterId, $pasien, $dokter, $start)
{
    $items = [];

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
                "code"    => "LAB",
                "display" => "Laboratory",
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
                    "div"    => "<div>Pemeriksaan: HAEMOGLOBIN</div>",
                ],
                "issued"            => $start,
                "effectiveDateTime" => $start,
                "code"              => [
                    "coding" => [
                        "system"  => "http://loinc.org",
                        "code"    => "20509-6",
                        "display" => "Hemoglobin [Mass/volume] in Blood by calculation",
                    ],
                    "text"   => "Hemoglobin [Mass/volume] in Blood by calculation",
                ],
                "performer"         => [
                    "reference" => "Practitioner/".$dokter['kd'],
                    "display"   => $dokter['nama'],
                ],
                "conclusion"        => "normal",
                "valueQuantity"     => [
                    "value"      => "1 - 10",
                    "comparator" => null,
                    "unit"       => "mg",
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
            ],
        ],

    ];

  return [
        "resource" => $items
    ];

}