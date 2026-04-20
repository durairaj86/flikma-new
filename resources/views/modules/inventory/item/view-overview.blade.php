<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Item Information</h6>
        <ul class="list-unstyled">
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">SKU Code:</span>
                <span class="fw-medium">{{ $item->sku_code }}</span>
            </li>
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Name (EN):</span>
                <span class="fw-medium">{{ $item->name_en }}</span>
            </li>
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Name (AR):</span>
                <span class="fw-medium">{{ $item->name_ar }}</span>
            </li>
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Account Type:</span>
                <span class="fw-medium">{{ ucfirst($item->account_type) }}</span>
            </li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Pricing Information</h6>
        <ul class="list-unstyled">
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Cost Price:</span>
                <span class="fw-medium">{{ $item->cost_price ?? 'N/A' }}</span>
            </li>
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Selling Price:</span>
                <span class="fw-medium">{{ $item->selling_price ?? 'N/A' }}</span>
            </li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">System Information</h6>
        <ul class="list-unstyled">
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Created At:</span>
                <span class="fw-medium">{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y H:i:s') }}</span>
            </li>
            <li class="mb-2 d-flex">
                <span class="text-muted me-2" style="width: 120px;">Updated At:</span>
                <span class="fw-medium">{{ \Carbon\Carbon::parse($item->updated_at)->format('d-m-Y H:i:s') }}</span>
            </li>
        </ul>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Close</button>
    <button type="button" class="btn btn-primary edit-item" data-id="{{ $item->id }}">Edit Item</button>
</div>

<script>
    $(document).ready(function() {
        $('.edit-item').on('click', function() {
            const id = $(this).data('id');

            // Close the drawer
            var drawer = bootstrap.Offcanvas.getInstance(document.getElementById('itemDrawer'));
            drawer.hide();

            // Open the edit modal
            webModal.openGlobalModal({
                title: 'Edit Item',
                url: GLOBAL_FN.buildUrl('inventory/items/' + id + '/edit'),
                content: null,
                size: 'md',
                scroll: false,
                minHeight: 'min-height:51vh;',
            });
        });
    });
</script>
