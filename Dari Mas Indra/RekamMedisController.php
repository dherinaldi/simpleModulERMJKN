<?php

class RekamMedisController extends CI_Controller
{

    protected $kodeRS = '1324R003';

    public function __construct()
    {
        parent::__construct();

        $this->load->library('BpjsRekamMedis');
    }

    function uuid()
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    public function index()
    {
        echo json_encode($this->bpjsrekammedis->show_headers(), JSON_PRETTY_PRINT);
    }

    public function insert()
    {

        $getData = $this->getMR(841487);
        $dataMR = json_encode($this->MRbundle($getData));
        $data_encrypt = $this->bpjsrekammedis->encrypt($dataMR);

        $stTGL = strtotime($getData['TGLREG']);
        $arr = ["request" => [
            "noSep" =>  $getData['NO_SEP'],
            "jnsPelayanan" => "2",
            "bulan" => date('n', $stTGL),
            "tahun" => date('Y', $stTGL),
            "dataMR" => $data_encrypt
        ]];

        // echo $dataMR;
        // return false;

        $result = $this->bpjsrekammedis->insertMedicalRecord(json_encode($arr));
        echo toJson($result);
    }

    function getMR($idxdaftar)
    {
        $sql = "SELECT pd.IDXDAFTAR, pd.NOMR, pd.TGLREG, pd.JAMREG, pd.NO_SEP, pd.KDPOLY, pd.STATUS_KELUAR, pd.MASUKPOLY, pd.KELUARPOLY,
                px.NAMA AS NAMA_PASIEN, px.JENISKELAMIN, px.TGLLAHIR, 
                px.NOKTP, px.NO_KARTU, px.STATUS_KAWIN, px.PENANGGUNGJAWAB_PHONE_1 AS NOTELP, 
                px.ALAMAT, kec.namakecamatan AS KECAMATAN, kot.namakota AS KOTA, prov.namaprovinsi AS PROVINSI,
                po.nama AS POLY, dok.NAMADOKTER, dok.NO_SIP, dok.NIK,
                dt.ICD_10DOK AS ICD10     
                FROM t_pendaftaran pd 
                LEFT JOIN m_pasien px ON pd.IDPASIEN = px.id
                LEFT JOIN m_kecamatan kec ON px.KDKECAMATAN = kec.idkecamatan
                LEFT JOIN m_kota kot ON px.KOTA = kot.idkota
                LEFT JOIN m_provinsi prov ON px.KDPROVINSI = prov.idprovinsi
                LEFT JOIN m_poly po ON pd.KDPOLY = po.kode
                LEFT JOIN m_dokter dok ON pd.KDDOKTER = dok.KDDOKTER
                LEFT JOIN t_diagnosadanterapi dt ON pd.IDXDAFTAR = dt.IDXDAFTAR
                WHERE pd.IDXDAFTAR=" . $idxdaftar;
        return $this->db->query($sql)->row_array();
    }

    function Patient($data)
    {
        extract($data);
        return [
            "resource" => [
                "resourceType" => "Patient",
                "id" => $patient_id,
                "identifier" => [
                    [
                        "use" => "usual",
                        "type" => [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/v2/0203",
                                    "code" => "MR",
                                ],
                            ],
                        ],
                        "value" => $NOMR,
                        "assigner" => ["display" => "RS RADJIMAN WEDIODININGRAT"],
                    ],
                    [
                        "use" => "official",
                        "type" => [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/v2/0203",
                                    "code" => "MB",
                                ],
                            ],
                        ],
                        "value" => $NO_KARTU,
                        "assigner" => ["display" => "BPJS KESEHATAN"],
                    ],
                    [
                        "use" => "official",
                        "type" => [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/v2/0203",
                                    "code" => "NNIDN",
                                ],
                            ],
                        ],
                        "value" => $NOKTP,
                        "assigner" => ["display" => "KEMENDAGRI"],
                    ],
                ],
                "active" => true,
                "name" => [["use" => "official", "text" => $NAMA_PASIEN]],
                "maritalStatus" => [
                    "coding" => [
                        [
                            "system" => "http://hl7.org/fhir/v3/MaritalStatus",
                            "code" => ($STATUS_KAWIN == 2 ? 'M' : 'U'),
                        ],
                    ],
                ],
                "telecom" => [
                    ["system" => "phone", "value" =>  $NOTELP, "use" => "mobile"],
                ],
                "gender" => ($JENISKELAMIN == 'P' ? 'female' : 'male'),
                "birthDate" => $TGLLAHIR,
                "deceasedBoolean" => false,
                "address" => [
                    [
                        "line" => [$ALAMAT],
                        "city" => $KOTA,
                        "district" => $KECAMATAN,
                        "state" => $PROVINSI,
                        "postalCode" => "",
                        "text" =>  $ALAMAT,
                        "use" => "home",
                        "type" => "both",
                    ],
                ],
                "managingOrganization" => [
                    "reference" => "Organization/" . $organization_id,
                    "display" => "RS Radjiman Wediodiningrat",
                ],
            ],
        ];
    }

    function Composition($data)
    {
        extract($data);
        return [
            "resource" => [
                "resourceType" => "Composition",
                "id" => $composition_id,
                "status" => "final",
                "type" => [
                    "coding" => [["system" => "http://loinc.org", "code" => "81218-0"]],
                    "text" => "Discharge Summary",
                ],
                "subject" => [
                    "reference" => "Patient/" . $patient_id,
                    "display" => $NAMA_PASIEN,
                ],
                "encounter" => [
                    "reference" => "Encounter/" . $encounter_id,
                ],
                "date" => date('Y-m-d H:i:s', strtotime($JAMREG)),
                "author" => [
                    [
                        "reference" => "Practitioner/" . $practitioner_id,
                        "display" =>  $NAMADOKTER,
                    ],
                ],
                "title" => "Discharge Summary",
                "confidentiality" => "N",
                "section" => [
                    "0" => [
                        "title" => "Reason for admission",
                        "code" => [
                            "coding" => [
                                [
                                    "system" => "http://loinc.org",
                                    "code" => "29299-5",
                                    "display" => "Reason for visit Narrative",
                                ],
                            ],
                        ],
                        "text" => ["status" => "additional", "div" => ""],
                    ],
                    // skip
                ],
            ],
        ];

        $skip = [
            "1" => [
                "title" => "Chief complaint",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://loinc.org",
                            "code" => "10154-3",
                            "display" => "Chief complaint Narrative",
                        ],
                    ],
                ],
                "text" => ["status" => "additional", "div" => ""],
            ],
            "2" => [
                "title" => "Admission diagnosis",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://loinc.org",
                            "code" => "42347-5",
                            "display" => "Admission diagnosis Narrative",
                        ],
                    ],
                ],
                "text" => [
                    "status" => "additional",
                    "div" => "LUKA BAKAR 52% TBSA, ",
                ],
                "entry" => [
                    [
                        "reference" =>
                        "urn:uuid:541a72a8-df75-4484-ac89-ac4923f03b81",
                    ],
                ],
            ],
            "4" => [
                "title" => "Medications on Discharge",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://loinc.org",
                            "code" => "75311-1",
                            "display" =>
                            "Hospital discharge medications Narrative",
                        ],
                    ],
                ],
                "text" => [
                    "status" => "additional",
                    "div" => "(CLINDAMYCIN)CLINDAMYCIN CAPSULE 300 MG 10 CAP, ",
                ],
                "mode" => "working",
                "entry" => [
                    [
                        "reference" =>
                        "MedicationRequest/0901R001-1196708-1-ef852407-45aa-43c7-b5e8-98d63b43c182",
                    ],
                    [
                        "reference" =>
                        "MedicationRequest/0901R001-1196708-1-ef852407-45aa-43c7-b5e8-98d63b43c182",
                    ],
                ],
            ],
            "5" => [
                "title" => "Plan of care",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://loinc.org",
                            "code" => "18776-5",
                            "display" => "Plan of care",
                        ],
                    ],
                ],
                "text" => ["status" => "additional", "div" => ""],
                "mode" => "working",
                "entry" => [
                    [
                        "reference" =>
                        "MedicationRequest/124a6916-5d84-4b8c-b250-10cefb8e6e86",
                    ],
                ],
            ],
            "7" => [
                "title" => "Known allergies",
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://loinc.org",
                            "code" => "48765-2",
                            "display" => "Allergies and adverse reactions",
                        ],
                    ],
                ],
                "text" => ["status" => "additional", "div" => ""],
                "entry" => [
                    [
                        "reference" =>
                        "AllergyIntolerance/47600e0f-b6b5-4308-84b5-5dec157f7637",
                    ],
                ],
            ],
        ];
    }

    public function MRbundle($data)
    {
        extract($data);
        $baseId = $this->kodeRS . '-' . $IDXDAFTAR . '-';
        $data['bundle_id'] =  $baseId . $this->uuid();
        $data['patient_id'] =  $baseId . $this->uuid();
        $data['encounter_id'] =  $baseId . $this->uuid();
        $data['organization_id'] =  $baseId . $this->uuid();
        $data['composition_id'] = $baseId . $this->uuid();
        $data['practitioner_id'] =  $baseId . $this->uuid();
        $data['condition_id'] =  $baseId . $this->uuid();
        $data['medication_id'] =  $baseId . $this->uuid();
        $data['diagnostic_report_id'] =  $baseId . $this->uuid();
        $data['procedure_id'] =  $baseId . $this->uuid();
        $data['device_id'] =  $baseId . $this->uuid();

        return [
            'resourceType' => 'Bundle',
            'id' => $data['bundle_id'],
            'meta' => ['lastUpdated' => date('Y-m-d H:i:s')],
            'identifier' => [
                'system' => 'sep',
                'value' => $NO_SEP
            ],
            'type' => 'document',
            'entry' => [
                $this->Patient($data),
                $this->Organization($data),
                // $this->Practitioner($data),
                $this->Condition($data),
                $this->Encounter($data),
                // $this->Medication($data),
                // $this->Composition($data),
                // $this->DiagnosticReport($data),
                // $this->Procedure($data),
                // $this->Device($data)


            ]
        ];
    }

    function Encounter($data)
    {
        extract($data);

        if ($STATUS_KELUAR == 2) {
            $class_code = "IMP";
            $class_diplay = "inpatient encounter";
            $stEnd = ''; // ???
        } elseif ($KDPOLY == 30) { // IGD
            $class_code = "AMB";
            $class_diplay = "ambulatory";
            $stEnd = strtotime($TGLREG . ' ' . $KELUARPOLY);
        } else {
            $class_code = "AMB";
            $class_diplay = "ambulatory";
            $stEnd =  strtotime($TGLREG . ' ' . $KELUARPOLY);
        }
        $stStart = strtotime($JAMREG);
        // End Kosong
        if ($stEnd < $stStart) {
            $stEnd = strtotime($JAMREG . ' + 30 minute');
        }

        return [
            "resource" => [
                "resourceType" => "Encounter",
                "id" => $encounter_id,
                "identifier" => [
                    [
                        "system" =>
                        "http://api.bpjs-kesehatan.go.id:8080/Vclaim-rest/SEP/",
                        "value" => $NO_SEP,
                    ],
                ],
                "subject" => [
                    "reference" => "Patient/" . $patient_id,
                    "display" => $NAMA_PASIEN,
                    "noSep" => $NO_SEP,
                ],
                "class" => [
                    "system" => "http://hl7.org/fhir/v3/ActCode",
                    "code" => $class_code,
                    "display" => $class_diplay,
                ],
                //-- skip
                "incomingReferral" => [
                    [
                        "identifier" => [
                            [
                                "system" => "nomor_rujukan_bpjs",
                                "value" => "0187R0060326B000001",
                            ],
                            // [
                            //     "system" => "nomor_rujukan_internal_rs",
                            //     "value" => "belum di buat",
                            // ],
                        ],
                    ],
                ],
                "reason" => [
                    [
                        "coding" => [
                            [
                                "code" => "F20.1",
                                "display" => "Hebephrenic schizophrenia",
                                "system" => "http://hl7.org/fhir/sid/icd-10",
                            ],
                        ],
                        "text" => "LUKA BAKAR 52% TBSA",
                    ],
                ],
                "diagnosis" => [
                    [
                        "condition" => [
                            "reference" => "Condition/" . $condition_id,
                            "role" => [
                                "coding" => [
                                    [
                                        "system" => "http://hl7.org/fhir/diagnosis-role",
                                        "code" => "DD",
                                        "display" => "Discharge Diagnosis",
                                    ],
                                ],
                            ],
                            "rank" => 1,
                        ],
                    ],
                ],
                "hospitalization" => [
                    "dischargeDisposition" => [
                        [
                            "coding" => [
                                [
                                    "code" => "home",
                                    "display" => "Home",
                                    "system" =>
                                    "http://hl7.org/fhir/discharge-disposition",
                                ],
                            ],
                        ],
                    ],
                ],
                "period" => [
                    "end" => date('Y-m-d H:i:s', $stEnd),
                    "start" => date('Y-m-d H:i:s', $stStart),
                ],
                "status" => "finished",
                "text" => [
                    "div" => "Admitted to " . $POLY . " RS Dr.Radjiman Wediodiningrat between " . date('j F Y H:i', $stStart) . " and " . date('j F Y H:i', $stEnd),
                    "status" => "generated",
                ],
            ],
        ];
    }

    function Organization($data)
    {
        extract($data);
        return [
            "resource" => [
                "resourceType" => "Organization",
                "id" => $organization_id,
                "identifier" => [
                    [
                        "use" => "official",
                        "system" => "urn:oid:bpjs",
                        "value" => "1324R003",
                    ],
                    [
                        "use" => "official",
                        "system" => "urn:oid:kemkes",
                        "value" => "3507026",
                    ],
                ],
                "type" => [
                    [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/organization-type",
                                "code" => "prov",
                                "display" => "Healthcare Provider",
                            ],
                        ],
                    ],
                ],
                "name" => "RS Radjiman Wediodiningrat",
                "alias" => ["RSRW"],
                "telecom" => [
                    ["system" => "phone", "value" => "0341-426015", "use" => "work"],
                ],
                "address" => [
                    [
                        "use" => "work",
                        "text" => "Jl. Ahmad Yani Kecamatan Lawang, Kabupaten Malang, Jawa Timur",
                        "line" => ["Jl. A. Yani"],
                        "city" => "Kabupaten Malang",
                        "state" => "Jawa Timur",
                        "postalCode" => "65216",
                        "country" => "IDN",
                    ],
                ],
                "contact" => [
                    [
                        "purpose" => [
                            "coding" => [
                                [
                                    "system" =>
                                    "http://hl7.org/fhir/contactentity-type",
                                    "code" => "PATINF",
                                ],
                            ],
                        ],
                        "telecom" => [["system" => "phone", "value" => "0341-426015"]],
                    ],
                ],
            ],
        ];
    }

    function Practitioner($data)
    {
        extract($data);
        return [
            "resource" => [
                "resourceType" => "Practitioner",
                "id" => $practitioner_id,
                "identifier" => [
                    [
                        "use" => "official",
                        "system" => "urn:oid:nomor_sip",
                        "value" => $NO_SIP,
                    ],
                    [
                        "use" => "official",
                        "type" => [
                            "coding" => [
                                [
                                    "system" => "http://hl7.org/fhir/v2/0203",
                                    "code" => "NNIDN",
                                ],
                            ],
                        ],
                        "value" => $NIK,
                        "assigner" => ["display" => "KEMDAGRI"],
                    ],
                ],
                "name" => [
                    [
                        "use" => "official",
                        "text" =>  $NAMADOKTER,
                    ],
                ],
                "gender" => "male",
                //--- skip
            ],
        ];
        $skip = [
            "telecom" => [
                ["system" => "phone", "value" => "0816-970-112", "use" => "work"],
                [
                    "system" => "email",
                    "value" => "suzanna.immanuel@gmail.com",
                    "use" => "work",
                ],
                ["system" => "fax", "value" => "", "use" => "work"],
                ["system" => "home", "value" => "", "use" => "home"],
            ],
            "address" => [
                [
                    "use" => "home",
                    "line" => [
                        "Jl. Pasir Putih Vii/7 Rt.09/10 Ancol Kec.pademangan Jakut",
                    ],
                    "city" => null,
                    "postalCode" => "64714",
                    "country" => null,
                ],
            ],
            "gender" => "female",
            "birthDate" => "1953-03-11 00:00:00",
        ];
    }

    function Medication($data)
    {
        extract($data);
        return [
            "resource" => [
                [
                    "resourceType" => "MedicationRequest",
                    "text" => ["div" => "TETAGAM P SOLUTION, INJECTION 250 IU/1 ML"],
                    "identifier" => [
                        "system" => "id_resep_pulang",
                        "value" => $medication_id,
                    ],
                    "subject" => [
                        "display" => $NAMA_PASIEN,
                        "reference" => "Patient/" . $patient_id,
                    ],
                    "intent" => "final",
                    "medicationCodeableConcept" => [
                        "coding" => [
                            [
                                "code" => "DRx0006657",
                                "system" => "http://rscm.co.id/drug",
                            ],
                        ],
                        "text" => "TETAGAM P SOLUTION, INJECTION 250 IU/1 ML",
                    ],
                    "dosageInstruction" => [
                        [
                            "doseQuantity" => [
                                "code" => "AMP",
                                "system" => "http://unitsofmeasure.org",
                                "unit" => "AMP",
                                "value" => "1",
                            ],
                            "route" => [
                                "coding" => [
                                    [
                                        "code" => "002",
                                        "display" => "INTRAVENOUS",
                                        "system" => "http://snomed.info/sct",
                                    ],
                                ],
                            ],
                            "timing" => [
                                "repeat" => [
                                    "frequency" => "1",
                                    "period" => 1,
                                    "periodUnit" => "na",
                                ],
                            ],
                            "additionalInstruction" => [["text" => "1 kali"]],
                        ],
                    ],
                    "reasonCode" => [
                        [
                            "coding" => [
                                ["code" => "", "display" => "", "system" => ""],
                            ],
                            "text" => "",
                        ],
                    ],
                    "requester" => [
                        "agent" => [
                            "display" => $NAMADOKTER,
                            "reference" =>
                            "Practitioner/" . $practitioner_id,
                        ],
                        "onBehalfOf" => [
                            "reference" =>
                            "Organization/" . $organization_id,
                        ],
                    ],
                    "meta" => ["lastUpdated" => date('Y-m-d H:i:s', strtotime($TGLREG . ' ' . $MASUKPOLY))],
                ],
                // skip
            ],
        ];

        $skip = [
            "resourceType" => "MedicationRequest",
            "text" => ["div" => "ZINC TAB DISPERSIBLE 20 MG"],
            "identifier" => [
                "system" => "id_resep_pulang",
                "value" =>
                "0901R001-1196708-1-afb5967c-576c-4042-b412-770b21336557",
            ],
            "subject" => [
                "display" => "BASONI",
                "reference" =>
                "Patient/0901R001-1196708-1-af272919-8ed1-4aa2-8808-ece97328007c",
            ],
            "intent" => "final",
            "medicationCodeableConcept" => [
                "coding" => [
                    [
                        "code" => "DRx0017609",
                        "system" => "http://rscm.co.id/drug",
                    ],
                ],
                "text" => "ZINC TAB DISPERSIBLE 20 MG",
            ],
            "dosageInstruction" => [
                [
                    "doseQuantity" => [
                        "code" => "TAB",
                        "system" => "http://unitsofmeasure.org",
                        "unit" => "TAB",
                        "value" => "2",
                    ],
                    "route" => [
                        "coding" => [
                            [
                                "code" => "001",
                                "display" => "ORAL",
                                "system" => "http://snomed.info/sct",
                            ],
                        ],
                    ],
                    "timing" => [
                        "repeat" => [
                            "frequency" => "1",
                            "period" => 1,
                            "periodUnit" => "d",
                        ],
                    ],
                    "additionalInstruction" => [["text" => "1 kali per hari"]],
                ],
            ],
            "reasonCode" => [
                [
                    "coding" => [
                        ["code" => "", "display" => "", "system" => ""],
                    ],
                    "text" => "",
                ],
            ],
            "requester" => [
                "agent" => [
                    "display" => "Lully Kurniawan, drg",
                    "reference" =>
                    "Practitioner/0901R001-1196708-1-8e4a4653-2565-4eab-9daa-2d9933a9d9ed",
                ],
                "onBehalfOf" => [
                    "reference" =>
                    "Organization/0901R001-1196708-1-0a803fcc-22bd-40c3-b017-f797c288e96f",
                ],
            ],
            "meta" => ["lastUpdated" => "2018-08-18 07:13:42"],
        ];
    }

    function Condition($data)
    {
        extract($data);
        return [
            "resource" => [
                "resourceType" => "Condition",
                "id" => $condition_id,
                "clinicalStatus" => "active",
                "verificationStatus" => "confirmed",
                "category" => [
                    [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/condition-category",
                                "code" => "encounter-diagnosis",
                                "display" => "Encounter Diagnosis",
                            ],
                        ],
                    ],
                ],
                "code" => [
                    "coding" => [
                        [
                            "system" => "http://hl7.org/fhir/sid/icd-10",
                            "code" => "T31.4",
                            "display" => "Burns involving 40-49% of body surface",
                        ],
                    ],
                    "text" => "Burns involving 40-49% of body surface",
                ],
                "subject" => [
                    "reference" => "Patient/" . $patient_id,
                ],
                "onsetDateTime" => date('Y-m-d H:i:s', strtotime($TGLREG . ' ' . $MASUKPOLY)),
            ],
        ];
    }

    function DiagnosticReport($data)
    {
        extract($data);
        return [
            "resource" => [
                [
                    "resourceType" => "DiagnosticReport",
                    "id" => $diagnostic_report_id,
                    "subject" => [
                        "reference" => "Patient/" . $patient_id,
                        "display" => $NAMA_PASIEN,
                        "noSep" => $NO_SEP,
                    ],
                    "category" => [
                        "coding" => [
                            "system" => "http://hl7.org/fhir/v2/0074",
                            "code" => "RAD",
                            "display" => "Radiology",
                        ],
                    ],
                    "status" => "final",
                    "performer" => [
                        [
                            "reference" =>
                            "Organization/0901R001-1229344-2-58238900-03d7-474e-b507-4c9b72d64a09",
                            "display" => "Radiologi Dan Kedokteran Nuklir",
                        ],
                    ],
                    "result" => [
                        [
                            "resourceType" => "Observation",
                            "id" => "DX00150004994364",
                            "status" => "final",
                            "text" => [
                                "status" => "generated",
                                "div" =>
                                "Teknik: Radiografi toraks dalam proyeksi PA.((((((((Deskripsi:((((Jantung tidak membesar, cardiothoracic ratio </OBX.5.1.1>lt; 50%.</OBX.5.1.2></OBX.5.1>((((Aorta dan mediastinum superior tidak melebar.((((Trakea relatif di tengah. Kedua hilus tidak menebal.((((Corakan vaskular kedua paru masih baik. Tidak tampak infiltrat/nodul. ((((Lengkung diafragma dan sinus kostofrenikus normal.((((Tulang-tulang yang tervisualisasi optimal kesan intak.((((((((((((</div>",
                            ],
                            "issued" => "2018-12-29 13:03:37",
                            "effectiveDateTime" => "2018-12-29 12:34:11",
                            "code" => [
                                "coding" => [
                                    "system" => "http://snomed.info/sct",
                                    "code" => "PROCx000025499",
                                    "display" => "THORAX",
                                ],
                                "text" => "THORAX",
                            ],
                            "performer" => [
                                "reference" =>
                                "Practitioner/0901R001-1229344-2-19ebfac8-4bc3-45f2-90ac-0acbf5c6d717",
                                "display" => "dr. Benny Zulkarnaien, SpRad (K).",
                            ],
                            "image" => [
                                [
                                    "comment" => "",
                                    "link" => ["reference" => "", "display" => ""],
                                ],
                            ],
                            "conclusion" =>
                            "Tak tampak kelainan radiologis pada jantung dan paru.((((",
                        ],
                    ],
                ],
            ],
        ];
    }

    function Procedure($data)
    {
        extract($data);
        return [
            "resource" => [
                [
                    "resourceType" => "Procedure",
                    "id" => $procedure_id,
                    "text" => [
                        "status" => "generated",
                        "div" => "Generated Narrative with Details",
                    ],
                    "status" => "completed",
                    "code" => [
                        "coding" => [
                            [
                                "system" => "http://snomed.info/sct",
                                "code" => "PROCx000032816",
                                "display" => "Triage",
                            ],
                        ],
                    ],
                    "subject" => [
                        "reference" => "Patient/" . $patient_id,
                        "display" => $NAMA_PASIEN,
                    ],
                    "context" => [
                        "reference" => "Encounter/" . $encounter_id,
                        "display" => "NURUL encounter on 31 Desember 2018 17:08",
                    ],
                    "performedPeriod" => [
                        "start" => "2018-12-31 17:08:00",
                        "end" => "2018-12-31 17:08:00",
                    ],
                    "performer" => [
                        [
                            "role" => [
                                "coding" => [
                                    [
                                        "system" => "http://snomed.info/sct",
                                        "code" => "310512001",
                                        "display" => "Medical oncologist",
                                    ],
                                ],
                            ],
                            "actor" => [
                                "reference" =>
                                "Practitioner/0901R001-1193709-34-cbda45a0-305b-42c1-8a7c-ef6268ec8c0f",
                                "display" => "Septi Sari Yanti",
                            ],
                        ],
                    ],
                    "reasonCode" => [["text" => "DiagnosticReport/f201"]],
                    "bodySite" => [
                        [
                            "coding" => [
                                [
                                    "system" => "http://snomed.info/sct",
                                    "code" => "272676008",
                                    "display" => "Sphenoid bone",
                                ],
                            ],
                        ],
                    ],
                    "focalDevice" => [
                        [
                            "action" => [
                                "coding" => [
                                    [
                                        "system" => "http://hl7.org/fhir/device-action",
                                        "code" => "implanted",
                                    ],
                                ],
                            ],
                            "manipulated" => [
                                "reference" => "Device/example-pacemaker",
                            ],
                        ],
                    ],
                    "note" => [["text" => ""]],
                ],
                // skip
            ],
        ];

        $skip = [
            "resourceType" => "Procedure",
            "id" => "0901R001-1193709-34-519258f6-22d2-4ed0-962f-b1e666d2c9ac",
            "text" => [
                "status" => "generated",
                "div" => "Generated Narrative with Details",
            ],
            "status" => "completed",
            "code" => [
                "coding" => [
                    [
                        "system" => "http://snomed.info/sct",
                        "code" => "PROCx000013190",
                        "display" => "Administrasi IGD",
                    ],
                ],
            ],
            "subject" => [
                "reference" =>
                "Patient/0901R001-1193709-34-af125c4b-cd0f-4877-b76a-532a3656da97",
                "display" => "NURUL",
            ],
            "context" => [
                "reference" =>
                "Encounter/0901R001-1193709-34-8d887b38-feb9-4edf-b818-c49336448c90",
                "display" => "NURUL encounter on 31 Desember 2018 17:08",
            ],
            "performedPeriod" => [
                "start" => "2018-12-31 17:08:00",
                "end" => "2018-12-31 17:08:00",
            ],
            "performer" => [
                [
                    "role" => [
                        "coding" => [
                            [
                                "system" => "http://snomed.info/sct",
                                "code" => "310512001",
                                "display" => "Medical oncologist",
                            ],
                        ],
                    ],
                    "actor" => [
                        "reference" =>
                        "Practitioner/0901R001-1193709-34-cbda45a0-305b-42c1-8a7c-ef6268ec8c0f",
                        "display" => "Septi Sari Yanti",
                    ],
                ],
            ],
            "reasonCode" => [["text" => "DiagnosticReport/f201"]],
            "bodySite" => [
                [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "272676008",
                            "display" => "Sphenoid bone",
                        ],
                    ],
                ],
            ],
            "focalDevice" => [
                [
                    "action" => [
                        "coding" => [
                            [
                                "system" => "http://hl7.org/fhir/device-action",
                                "code" => "implanted",
                            ],
                        ],
                    ],
                    "manipulated" => [
                        "reference" => "Device/example-pacemaker",
                    ],
                ],
            ],
            "note" => [["text" => ""]],
        ];
    }

    function Device($data)
    {
        extract($data);
        return [
            "resource" => [
                [
                    "resourceType" => "Device",
                    "id" => $device_id,
                    "text" => ["status" => "generated", "div" => ""],
                    "identifier" => [
                        [
                            "system" =>
                            "http://acme.com/devices/pacemakers/octane/serial",
                            "value" => "MDVx024590",
                        ],
                    ],
                    "type" => [
                        "coding" => [
                            [
                                "system" => "http://acme.com/devices",
                                "code" => "MDVx024590",
                                "display" => "SKINTACT EASYTAB",
                            ],
                        ],
                    ],
                    "lotNumber" => "",
                    "manufacturer" => "",
                    "manufactureDate" => "",
                    "expirationDate" => "",
                    "model" => "",
                    "patient" => [
                        "reference" =>
                        "Patient/0901R001-1180006-12-f37a5d7f-3d36-4897-952c-49dac09efc71",
                    ],
                    "contact" => [
                        ["system" => "phone", "value" => "ext 4352", "use" => "work"],
                    ],
                ],
                // skip
            ],
        ];

        $skip = [
            "resourceType" => "Device",
            "id" => "0901R001-1180006-12-a620a1d0-3926-4533-90b0-bc002f1d3b47",
            "text" => ["status" => "generated", "div" => ""],
            "identifier" => [
                [
                    "system" =>
                    "http://acme.com/devices/pacemakers/octane/serial",
                    "value" => "MDVx001160",
                ],
            ],
            "type" => [
                "coding" => [
                    [
                        "system" => "http://acme.com/devices",
                        "code" => "MDVx001160",
                        "display" => "PEN NEEDLE 31 GA BD",
                    ],
                ],
            ],
            "lotNumber" => "ALKES",
            "manufacturer" => "ANUGRAH ARGON MEDICA, PT",
            "manufactureDate" => "",
            "expirationDate" => "",
            "model" => "",
            "patient" => [
                "reference" =>
                "Patient/0901R001-1180006-12-f37a5d7f-3d36-4897-952c-49dac09efc71",
            ],
            "contact" => [
                [
                    "system" => "phone",
                    "value" => "021 3861271",
                    "use" => "work",
                ],
            ],
        ];
    }

    // /
}
