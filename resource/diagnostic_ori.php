<?php
function diagnostic($encounterId,$pasien,$dokter,$start)
{

    $data = [
        "resourceType" => "DiagnosticReport",
        "id"           => $encounterId,
        "category"     => [
            "coding" => [
                "system"  => "http://hl7.org/fhir/v2/0074",
                "code"    => "LAB",
                "display" => "Laboratory",
            ],
        ],
        "status"       => "final",
        "result"       => [
            [
                "resourceType"      => "Observation",
                "id"                => "obs-1",
                "status"            => "final",
                "text"              => [
                    "status" => "generated",
                    "div"    => "<div>OB Paratyphi B: Negatif</div>",
                ],
                "issued"            => $start,
                "effectiveDateTime" => $start,
                "code"              => [
                    "text" => "OB Paratyphi B",
                ],
                "valueQuantity"     => [
                    "value" => "Negatif",
                ],
            ],
            [
                "resourceType"      => "Observation",
                "id"                => "obs-2",
                "status"            => "final",
                "text"              => [
                    "status" => "generated",
                    "div"    => "<div>OA Paratyphi A: Negatif</div>",
                ],
                "issued"            => $start,
                "effectiveDateTime" => $start,
                "code"              => [
                    "text" => "OA Paratyphi A",
                ],
                "valueQuantity"     => [
                    "value" => "Negatif",
                ],
            ],
        ],
        "subject"      => [
            "reference" => "Patient/".$pasien['no_rm'],
        ],
        "performer"    => [
            [
                "display" => $dokter['nama'],
            ],
        ],
    ];

    return $data;

}
