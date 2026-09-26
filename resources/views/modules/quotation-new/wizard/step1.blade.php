@section('page-title','New Quotation')
@section('js','quotation-new')
<x-app-layout>
    <main class="bg-white px-3 py-3">

        @include('modules.quotation-new.wizard._wizard-header', ['currentStep' => 1])

        <form id="wizardForm" novalidate>
            @csrf
            <input type="hidden" name="quotation_id" id="quotation_id" value="{{ $quotation->id ?? '' }}">

            <div class="card shadow-sm border-0 mx-auto" style="max-width:860px;">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-4 text-primary border-bottom pb-2">
                        <i class="bi bi-file-earmark-plus me-2"></i>Create Quotation
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Branch</label>
                            <input type="text" name="branch" class="form-control"
                                   value="{{ $quotation->branch }}" placeholder="e.g. CHENNAI">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Department</label>
                            <select name="department" class="tom-select">
                                <option value="">--Select--</option>
                                @foreach(['FCL EXPORT','FCL IMPORT','LCL EXPORT','LCL IMPORT','AIR EXPORT','AIR IMPORT'] as $dept)
                                    <option value="{{ $dept }}" @selected($quotation->department === $dept)>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Quotation Date <span class="text-danger">*</span></label>
                            <input type="date" name="quotation_date" class="form-control datepicker"
                                   value="{{ $quotation->quotation_date }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Client <span class="text-danger">*</span></label>
                            <x-common.customers :value="$quotation->client_id" :required="true"></x-common.customers>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Valid From <span class="text-danger">*</span></label>
                            <input type="date" name="valid_from" class="form-control datepicker"
                                   value="{{ $quotation->valid_from }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Valid To <span class="text-danger">*</span></label>
                            <input type="date" name="valid_to" class="form-control datepicker"
                                   value="{{ $quotation->valid_to }}" required>
                        </div>
                    </div>
                </div>
            </div>

            @include('modules.quotation-new.wizard._wizard-footer', ['step' => 1, 'nextUrl' => null])
        </form>
    </main>
</x-app-layout>
