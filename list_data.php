<style>
.text-left {
    text-align: left;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

#spinner-overlay {
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(255, 255, 255, 0.8);
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    display: none;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

table.dataTable {
    width: 100% !important;
}

/** .dataTables_wrapper {
    overflow-x: auto;
}*/
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Monitoring Antrol Detail</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row g-3">

            <div class="col-md-3">
                <label for="startDate" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="startDate">
            </div>

            <div class="col-md-3">
                <label for="startDate" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="endDate">
            </div>

            <div class="col-md-3">
                <label for="poli" class="form-label">Poli</label>
                <select id="poli" class="form-control"></select>
            </div>

            <div class="col-md-3">
                <label for="penjamin" class="form-label">Penjamin</label>
                <select id="penjamin" class="form-control"></select>
            </div>

            <div class="col-md-3">
                <label for="jenis_aplikasi" class="form-label">Jenis Aplikasi</label>
                <select id="jenis_aplikasi" class="form-control"></select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" class="form-control">
                    <option value="">-- SEMUA --</option>
                    <option value="1">BELUM</option>
                    <option value="2">SUDAH</option>
                    <option value="99">CHECK IN</option>
                    <option value="0">BATAL</option>
                </select>
            </div>


            <div class="text-center">
                <button id="cari1" class="btn btn-sm btn-success">Tampilkan</button>
                <button id="btnExportExcel" class="btn btn-sm btn-info">⬇ Excel</button>
            </div>
        </div>

        <div class="container-fluid mt-3">

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Monitoring SEP BPJS</h5>
                </div>

                <div class="card-body">

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label>Tanggal Awal</label>
                            <input type="date" id="startDate" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" id="endDate" class="form-control">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button id="cari" class="btn btn-primary w-100">
                                Tampilkan
                            </button>
                        </div>

                    </div>

                    <div class="table-responsive">

                        <table id="tabelCari" class="table table-striped table-bordered table-hover w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th>No Registrasi</th>
                                    <th>Tanggal</th>
                                    <th>No RM</th>
                                    <th>NIK</th>
                                    <th>Nama Pasien</th>
                                    <th>No Kartu</th>
                                    <th>No SEP</th>
                                    <th>Dokter</th>
                                    <th>Opsi</th>
                                    <th>Patient</th>
                                    <th>Practisioner</th>
                                    <th>Encounter</th>
                                    <th>Condition</th>
                                    <th>Procedure</th>
                                    <th>Composition</th>
                                    <th>MedicationRequest</th>
                                    <th>Diagnostic Report</th>
                                </tr>
                            </thead>

                        </table>

                    </div>

                </div>
            </div>

        </div>

        <div id="spinner-overlay"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.6); z-index:9999; align-items:center; justify-content:center;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalSep" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            Detail SEP Pasien
                        </h5>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <table class="table table-bordered">

                            <tr>
                                <th width="200">No Registrasi</th>
                                <td id="md_nopen"></td>
                            </tr>

                            <tr>
                                <th>No RM</th>
                                <td id="md_norm"></td>
                            </tr>

                            <tr>
                                <th>Nama Pasien</th>
                                <td id="md_nama"></td>
                            </tr>

                            <tr>
                                <th>NIK</th>
                                <td id="md_nik"></td>
                            </tr>

                            <tr>
                                <th>No Kartu</th>
                                <td id="md_noka"></td>
                            </tr>

                            <tr>
                                <th>No SEP</th>
                                <td id="md_nosep"></td>
                            </tr>

                            <tr>
                                <th>Ruangan</th>
                                <td id="md_ruangan"></td>
                            </tr>

                            <tr>
                                <th>Dokter</th>
                                <td id="md_dokter"></td>
                            </tr>


                        </table>

                        <pre id="jsonView"></pre>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="btnKirim">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>

                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPatient" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            Detail Patient
                        </h5>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <pre id="jsonViewPatient"></pre>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>

                    </div>

                </div>
            </div>
        </div>


    </section>

    <script>
    $(document).ready(function() {

        const tableCari = $('#tabelCari').DataTable({
            responsive: true,
            ajax: {
                url: 'data.php',
                data: function(d) {
                    // Add dynamic parameters for startDate and endDate
                    d.param = "antrol";
                    d.tanggal = $('#startDate').val(); // Dynamic start date
                    d.tanggal_akhir = $('#endDate').val(); // Dynamic start date
                    d.status = $('#status').val();
                    d.penjamin = $('#penjamin').val();
                    d.poli = $('#poli').val();
                    d.jenis_aplikasi = $('#jenis_aplikasi').val();
                },
                dataSrc: json => (json.metaData.code === "200") ? json.response.hasil : []
            },
            columns: [{
                    data: 'NOPEN'
                },
                {
                    data: 'TANGGAL'
                },
                {
                    data: 'NORM'
                },
                {
                    data: 'NIK'
                },
                {
                    data: 'NAMA'
                },
                {
                    data: 'NOKA'
                },
                {
                    data: 'noSEP'
                },

                {
                    data: 'NAMA_DOKTER'
                },

                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-info btn-edit">
            <i class="bi bi-search"></i> Detail Kirim </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editPatient">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editPractitioner">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editEncounter">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editCondition">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editProcedure">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editComposition">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editMedicationRequest">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                },
                {
                    data: null, // Menggunakan data gabungan
                    render: function(data, type, row) {
                        // Gabungkan name dan email dengan gaya
                        return `
                    <div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                 <button class="btn btn-sm btn-warning btn-editDiagnosticReport">
            <i class="bi bi-search"></i> Cek </button>

                </div>
                    </div>`;
                    }
                }



            ],

            iDisplayLength: 10, // tampilkan 10 data per halaman
        });

        // Spinner untuk loading pencarian
        tableCari.on('preXhr.dt', function() {
            $('#spinner-overlay').fadeIn();
        });
        tableCari.on('xhr.dt', function() {
            $('#spinner-overlay').fadeOut();
        });


        $('#cari').on('click', function() {
            console.log(`filter button`);

            tableCari.ajax.reload(); // Reload DataTable with new parameters
        });

        // Edit button action
        let selectedData = null;

        $('#tabelCari tbody').on('click', '.btn-edit', function() {

            selectedData = tableCari.row($(this).closest('tr')).data();

            $('#md_nopen').text(selectedData.NOPEN);
            $('#md_norm').text(selectedData.NORM);
            $('#md_nama').text(selectedData.NAMA);
            $('#md_nik').text(selectedData.NIK);
            $('#md_noka').text(selectedData.NOKA);
            $('#md_nosep').text(selectedData.noSEP);
            $('#md_ruangan').text(selectedData.RUANGAN);
            $('#md_dokter').text(selectedData.NAMA_DOKTER);

            $('#modalSep').modal('show');

        });

        $('#btnKirim').click(function() {

            if (!selectedData) {
                alert('Data tidak ditemukan');
                return;
            }

            $.ajax({
                url: 'coba_rme.php',
                type: 'POST',
                dataType: 'json',

                data: {
                    nopen: selectedData.NOPEN,
                    norm: selectedData.NORM,
                    nosep: selectedData.noSEP,
                    nik: selectedData.NIK
                },

                beforeSend: function() {

                    $('#btnKirim')
                        .prop('disabled', true)
                        .html('Mengirim...');

                },

                success: function(res) {

                    Swal.fire({
                        title: 'Response JSON',
                        width: '80%',
                        html: `
                <pre style="
                    text-align:left;
                    max-height:500px;
                    overflow:auto;
                    background:#f8f9fa;
                    padding:10px;
                    border-radius:5px;
                "><code>${JSON.stringify(res, null, 4)}<code></pre>
            `,
                        confirmButtonText: 'Tutup'
                    });

                    $('#modalSep').modal('hide');
                },

                error: function() {

                    alert('Gagal menghubungi server');

                },

                complete: function() {

                    $('#btnKirim')
                        .prop('disabled', false)
                        .html('Kirim');

                }
            });

        });

        //untuk menampilkan data per Resource nya
        function tampilResource_bak(res, resourceType) {
            let resources = res.entry
                .filter(x => x.resource.resourceType === resourceType)
                .map(x => x.resource);

            console.log(res);

            console.log('Cari:', resourceType);

            console.log(
                res.entry.map(x => x.resource.resourceType)
            );

            if (resources.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ditemukan',
                    text: `${resourceType} tidak ditemukan`
                });
                return;
            }

            let dataTampil =
                resources.length === 1 ?
                resources[0] :
                resources;

            //console.log(dataTampil);

            Swal.fire({
                title: resourceType,
                width: '80%',
                html: `
            <pre style="
                text-align:left;
                max-height:600px;
                overflow:auto;
                background:#f8f9fa;
                padding:10px;
                border-radius:5px;
            ">
${JSON.stringify(dataTampil, null, 2)}
            </pre>
        `
            });
        }

        function tampilResource(res, resourceType) {

            let resources = [];

            res.entry.forEach(item => {

                if (Array.isArray(item.resource)) {

                    item.resource.forEach(r => {
                        if (r.resourceType === resourceType) {
                            resources.push(r);
                        }
                    });

                } else {

                    if (item.resource?.resourceType === resourceType) {
                        resources.push(item.resource);
                    }

                }

            });

            console.log(resources);

            if (resources.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ditemukan',
                    text: `${resourceType} tidak ditemukan`
                });
                return;
            }

            Swal.fire({
                title: resourceType,
                width: '80%',
                html: `
            <pre style="
                text-align:left;
                max-height:600px;
                overflow:auto;
                background:#f8f9fa;
                padding:10px;
                border-radius:5px;
            ">
${JSON.stringify(resources, null, 2)}
            </pre>
        `
            });
        }

        function loadFHIRResource(nopen, resourceType) {
            //console.log(resourceType);
            $.ajax({
                url: 'coba_rme.php',
                type: 'POST',
                dataType: 'json',

                data: {
                    nopen: nopen
                },

                success: function(res) {
                    tampilResource(res, resourceType);
                },

                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error
                    });

                }
            });

        }

        $('#tabelCari tbody').on('click', '.btn-editPatient', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'Patient');
        });

        $('#tabelCari tbody').on('click', '.btn-editPractitioner', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'Practitioner');
        });

        $('#tabelCari tbody').on('click', '.btn-editEncounter', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'Encounter');
        });

        $('#tabelCari tbody').on('click', '.btn-editCondition', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'Condition');
        });

        $('#tabelCari tbody').on('click', '.btn-editProcedure', function() {
            let data = tableCari.row($(this).closest('tr')).data();

            loadFHIRResource(data.NOPEN, 'Procedure');
        });

        $('#tabelCari tbody').on('click', '.btn-editComposition', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'Composition');
        });

        $('#tabelCari tbody').on('click', '.btn-editMedicationRequest', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'MedicationRequest');
        });

        $('#tabelCari tbody').on('click', '.btn-editDiagnosticReport', function() {
            let data = tableCari.row($(this).closest('tr')).data();
            loadFHIRResource(data.NOPEN, 'DiagnosticReport');
        });



    });
    </script>