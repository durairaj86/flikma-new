<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Item Details') }}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>{{ __('SKU Code') }}</th>
                                    <td>{{ $item->sku_code }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Name (English)') }}</th>
                                    <td>{{ $item->name_en }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Name (Arabic)') }}</th>
                                    <td>{{ $item->name_ar }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Account Type') }}</th>
                                    <td>{{ ucfirst($item->account_type) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Cost Price') }}</th>
                                    <td>{{ $item->cost_price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Selling Price') }}</th>
                                    <td>{{ $item->selling_price ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Created At') }}</th>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Updated At') }}</th>
                                    <td>{{ \Carbon\Carbon::parse($item->updated_at)->format('d-m-Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-primary edit-item" data-id="{{ $item->id }}">{{ __('Edit') }}</button>
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
