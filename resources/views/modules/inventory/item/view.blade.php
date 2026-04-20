<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Item Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>SKU Code</th>
                                    <td>{{ $item->sku_code }}</td>
                                </tr>
                                <tr>
                                    <th>Name (English)</th>
                                    <td>{{ $item->name_en }}</td>
                                </tr>
                                <tr>
                                    <th>Name (Arabic)</th>
                                    <td>{{ $item->name_ar }}</td>
                                </tr>
                                <tr>
                                    <th>Account Type</th>
                                    <td>{{ ucfirst($item->account_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Cost Price</th>
                                    <td>{{ $item->cost_price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Selling Price</th>
                                    <td>{{ $item->selling_price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ \Carbon\Carbon::parse($item->updated_at)->format('d-m-Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary edit-item" data-id="{{ $item->id }}">Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.edit-item').on('click', function() {
            const id = $(this).data('id');
            $('#itemViewModal').modal('hide');

            $.get(`{{ url('/inventory/items') }}/${id}/edit`, function(data) {
                $('#itemFormModal .modal-body').html(data);
                $('#itemFormModal').modal('show');
            });
        });
    });
</script>
