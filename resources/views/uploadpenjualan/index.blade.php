@extends('layouts.main')

@section('container')

<div class="page-heading">
    <div class="page-title">
        <h3>Penjualan</h3>
        <p class="text-subtitle text-muted">List Data Penjualan</p>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-body">

            
            <button id="uploadStockGudang" class="btn btn-success mb-5" data-bs-toggle="modal" data-bs-target="#formUpload"><i class="bi bi-cloud-arrow-up"></i> Upload Penjualan</button>

            <div id="loadingContainer" class="text-center" style="display: none;">
                <span id="loadingSpinner" class="spinner-border spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </span>
            </div>
            

            <div class="table-responsive">
                <table class="table" id="table-penjualan">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Kode Customer</th>
                            <th>Nama Customer</th>
                            <th>Tanggal Invoice</th>
                            <th>Kode Item</th>
                            <th>Nama Item</th>
                            <th>Warehouse</th>
                            <th>QTY</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>


<!-- MODAL UPLOAD -->
<div class="modal fade" id="formUpload" tabindex="-1" aria-labelledby="formUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <form id="upload-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Upload Excel File</label>
                    <input type="file" name="file" class="form-control" id="file">
                </div>
                <button type="submit" class="btn btn-success">Upload</button>
            </form>

             <div id="loading" class="text-center" style="display: none;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Please wait...</p>
            </div>

            <div id="response-table-container" class="mt-4" style="display: none;">
                <table class="table table-bordered" id="response-table">
                    <thead>
                        <tr>
                            <th>Nomor Invoice</th>
                            <th>Kode Customer</th>
                            <th>Nama Customer</th>
                            <th>Tanggal Invoice</th>
                            <th>Kode Item</th>
                            <th>Nama Item</th>
                            <th>Warehouse</th>
                            <th>QTY</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Status Validasi</th>
                            <th>Pesan Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
        
        <div class="modal-footer">
            <button id="uploadJsonExcel" class="btn btn-primary">Submit</button>
        </div>

      </div>
    </div>
  </div>

@endsection

@push('scripts')

<script>
    $(document).ready(function() {

        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $('#table-penjualan').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/uploadpenjualan', // URL ke controller yang memproses DataTables
            columns: [
                { data: 'no_invoice' },
                { data: 'kode_customer' },
                { data: 'nama_customer' },
                { data: 'tgl_invoice' },
                { data: 'kode_item' },
                { data: 'nama_item' },
                { data: 'warehouse' },
                { data: 'qty' },
                { data: 'price' },
                { data: 'total' },
            ]
        });


        $('#upload-form').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $('#loading').show();
                $('#response-table-container').hide();

                $.ajax({
                    url: '/uploadpenjualan/validateUpload',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#loading').hide();
                        $('#response-table tbody').empty();

                        if (response.success && response.data.length > 0) {
                            $('#response-table-container').show();
                            response.data.forEach(function(item) {
                                var row = `<tr>
                                    <td>${item.no_invoice}</td>
                                    <td>${item.kode_customer}</td>
                                    <td>${item.nama_customer}</td>
                                    <td>${item.tgl_invoice}</td>
                                    <td>${item.kode_item}</td>
                                    <td>${item.nama_item}</td>
                                    <td>${item.warehouse}</td>
                                    <td>${item.qty}</td>
                                    <td>${item.price}</td>
                                    <td>${item.total}</td>
                                    <td>${item.status_validation}</td>
                                    <td>${item.message_validation}</td>
                                </tr>`;
                                $('#response-table tbody').append(row);
                            });
                        } else {
                            $('#response-table-container').hide();
                        }
                    },
                    error: function(xhr) {
                        $('#loading').hide();
                        console.error(xhr.responseJSON.message || "An error occurred.");
                    }
                });
        });


        $('#uploadJsonExcel').on('click', function(e) {
                e.preventDefault();

                // Collect data from the response table
                let dataToSubmit = [];
                $('#response-table tbody tr').each(function() {
                    let row = {
                        no_invoice: $(this).find('td').eq(0).text(),
                        kode_customer: $(this).find('td').eq(1).text(),
                        nama_customer: $(this).find('td').eq(2).text(),
                        tgl_invoice: $(this).find('td').eq(3).text(),
                        kode_item: $(this).find('td').eq(4).text(),
                        nama_item: $(this).find('td').eq(5).text(),
                        warehouse: $(this).find('td').eq(6).text(),
                        qty: $(this).find('td').eq(7).text(),
                        price: $(this).find('td').eq(8).text(),
                        total: $(this).find('td').eq(9).text(),
                        status_validation: $(this).find('td').eq(10).text(),
                        message_validation: $(this).find('td').eq(11).text()
                    };
                    dataToSubmit.push(row);
                });

                if (dataToSubmit.length === 0) {
                    Swal.fire('Warning', 'No data to upload.', 'warning');
                    return;
                }

                $.ajax({
                    url: '/uploadpenjualan/upload',
                    type: 'POST',
                    data: JSON.stringify(dataToSubmit),
                    contentType: 'application/json',
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success');
                        $('#response-table tbody').empty();
                        $('#response-table-container').hide();
                        resetModal();
                    },
                    error: function(xhr) {
                        // console.error(xhr.responseJSON.message || "An error occurred.");
                        Swal.fire('Error', xhr.responseJSON.message || 'An error occurred.', 'error'); 
                        resetModal();
                 }
            });
        });


    });
</script>
@endpush