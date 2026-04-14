<?php
function buildMedicationResource($listObat,$pasien,$dokter,$diagnosa)
{
    $result = [];

    foreach($listObat as $o){

        $result[] = [
            // "resource" => [
                // [
                    "resourceType" => "MedicationRequest",

                    "text" => [
                        "status" => "generated",
                        "div" => $o['nama_obat']." ".$o['aturan']
                    ],

                    "identifier" => [
                        "system" => "id_resep_pulang",
                        "value" => $o['id_resep']
                    ],

                    "subject" => [
                        "display" => $pasien['nama'],
                        "reference" => "Patient/".$pasien['no_rm']
                    ],

                    "dosageInstruction" => [[
                        "doseQuantity" => [
                            "system" => "https://fhir.rsuini.co.id/kfa/dosage",
                            "code" => $o['kode_satuan'],
                            "unit" => $o['satuan'],
                            "value" => $o['jumlah']
                        ],

                        "route" => [
                            "coding" => [[
                                "system" => "https://fhir.rsuini.co.id/kfa/route",
                                "code" => "",
                                "display" => ""
                            ]]
                        ],

                        "timing" => [
                            "repeat" => [
                                "frequency" => $o['frequency'],
                                "period" => 1,
                                "periodUnit" => 1
                            ]
                        ],

                        "additionalInstruction" => [[
                            "text" => $o['aturan']
                        ]]
                    ]],

                    "reasonCode" => [[
                        "coding" => [
                            "system" => "https://fhir.rsuini.co.id/kfa/reason",                            
                            "code" => $diagnosa[0]['code'],
                            "display" => $diagnosa[0]['display']
                        ],
                        "text" => $diagnosa[0]['display']
                    ]],

                    "intent" => "final",

                    "requester" => [
                        "agent" => [
                            "reference" => "Practitioner/".$dokter['kd'],
                            "display" => $dokter['nama']
                        ],
                        "onBehalfOf" => [
                            "reference" => "Organization/".$dokter['org']
                        ]
                    ],

                    "medicationCodeableConcept" => [
                        "coding" => [
                            [
                                "system" => "http://sys-ids.kemkes.go.id/kfa",
                                "code" => "",
                                "display" => ""
                            ],
                            [
                                "system" => "https://fhir.rsuini.co.id/kfa",
                                "code" => $o['kode_obat'],
                                "display" => $o['nama_obat']
                            ]
                        ],
                        "text" => $o['nama_obat']
                    ],

                    "meta" => [
                        "lastUpdated" => date('Y-m-d H:i:s')
                    ]
                // ]
            // ]
        ];

    }

    return $result;
}
?>