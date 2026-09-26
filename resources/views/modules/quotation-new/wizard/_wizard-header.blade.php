@php
    $steps = [
        1 => 'Create Quotation',
        2 => 'Port Details',
        3 => 'Container / Consignment',
        4 => 'Charge Details',
        5 => 'Summary',
    ];
@endphp
<div class="d-flex justify-content-center mb-4 mt-2">
    <div class="wizard-steps d-flex align-items-center gap-0">
        @foreach($steps as $num => $label)
            <div class="d-flex align-items-center">
                <div class="wizard-step text-center" style="min-width:120px;">
                    <div class="wizard-dot mx-auto
                        {{ $num < $currentStep ? 'wizard-dot-done' : ($num === $currentStep ? 'wizard-dot-active' : 'wizard-dot-pending') }}">
                        @if($num < $currentStep)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <div class="wizard-label small mt-1
                        {{ $num === $currentStep ? 'fw-semibold text-primary' : 'text-muted' }}">
                        {{ $label }}
                    </div>
                </div>
                @if($num < count($steps))
                    <div class="wizard-line {{ $num < $currentStep ? 'wizard-line-done' : '' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
