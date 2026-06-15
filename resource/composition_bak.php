<?php
function Composition($compositionId, $noMr,
    $nama,
    $encounterId,
    $id_pr,
    $nama_pr, $start, $sectionData = []) {
    $sections = [];
    $no       = 1;

    foreach ($sectionData as $s) {
        $sections[(string) $no] = [
            "title" => $s['title'],
            "code"  => [
                "coding" => [[
                    "system"  => $s['system'],
                    "code"    => $s['code'],
                    "display" => $s['display'],
                ]],
            ],
            "text"  => [
                "status" => "additional",
                "div"    => $s['text'],
            ],
            "entry" => $s['entry'],
        ];

        if (isset($s['mode'])) {
            $sections[(string) $no]['mode'] = $s['mode'];
        }

        $no++;
    }

    return [
        "resourceType"    => "Composition",
        "id"              => $compositionId,
        "status"          => "final",
        "type"            => [
            "coding" => [[
                "system"  => "http://loinc.org",
                "code"    => "81218-0",
                "display" => "Discharge Summary",
            ]],
            "text"   => "Discharge Summary",
        ],
        "subject"         => [
            "reference" => "Patient/" . $noMr,
            "display"   => $nama,
        ],
        "encounter"       => [
            "reference" => "Encounter/" . $encounterId,
        ],
        "date"            => $start,
        "author"          => [[
            "reference" => "Practitioner/" . $id_pr,
            "display"   => $nama_pr,
        ]],
        "title"           => "Discharge Summary",
        "confidentiality" => "N",
        "section"         => $sections,
    ];
}