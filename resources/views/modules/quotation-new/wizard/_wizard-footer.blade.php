<div class="d-flex justify-content-between align-items-center mt-4 mx-auto" style="max-width:860px;">
    <div>
        @if($step > 1)
            <a href="{{ url('sales/quotations-new/'.$quotation->id.'/step'.($step - 1)) }}"
               class="btn btn-warning btn-round px-4">
                <i class="bi bi-chevron-left me-1"></i> Previous
            </a>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ url('sales/quotations-new') }}" class="btn btn-outline-danger btn-round px-3">
            <i class="bi bi-x me-1"></i> Cancel
        </a>
        @if($step < 5)
            <button type="button" class="btn btn-primary btn-round px-4" id="btn-next">
                Next <i class="bi bi-chevron-right ms-1"></i>
            </button>
        @else
            <button type="button" class="btn btn-success btn-round px-4" id="btn-finalise">
                <i class="bi bi-check-circle me-1"></i> Finish
            </button>
        @endif
    </div>
</div>
