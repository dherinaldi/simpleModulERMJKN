<?php
function diagnostic($pasien)
{

    return [
        "resourceType" => "DiagnosticReport",
        "status"       => "final",
        "category"     => [
            "coding" => [
                [
                    "system" => "http://hl7.org/fhir/v2/0074",
                    "code"   => "LAB",
                ],
            ],
        ],
        "subject"      => [
            "reference" => "Patient/" . $pasien,
        ],
    ];

}
