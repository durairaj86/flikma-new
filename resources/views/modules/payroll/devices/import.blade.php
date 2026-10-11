@section('page-title', __('Import Punches'))
@section('hide-topbar', true)
<x-app-layout>
<div class="pr-wrap">

<div class="container-fluid px-2 pt-4 pt-md-0">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-4">
    <div class="min-w-0">
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('payroll.dashboard') }}">{{ __('Payroll') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('payroll.punch-log') }}">{{ __('Punch Log') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Import from Excel') }}</li>
            </ol>
        </nav>
        <h1 class="h4 fw-bold text-dark mb-0">{{ __('Import Punches from Excel / CSV') }}</h1>
        <div class="text-muted small">{{ __('Use this when a machine cannot connect online. Export its log to Excel or CSV and upload it here.') }}</div>
    </div>
    <a href="{{ route('payroll.punch-log') }}" class="btn btn-outline-secondary btn-sm flex-shrink-0"><i class="bi bi-arrow-left me-1"></i>{{ __('Back to Punch Log') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3"><ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <form method="POST" action="{{ route('payroll.punch-import.upload') }}" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-3 h-100">
                @csrf
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">1</span>{{ __('Upload the file') }}</h6>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv,.txt" required>
                    <div class="form-text">{{ __('.xlsx, .xls or .csv — up to 5 MB and 10,000 rows. The first sheet is used.') }}</div>
                    <div class="mt-3 small text-muted">{{ __('Nothing is saved yet. You will see every row checked first, and confirm before anything is imported.') }}</div>
                </div>
                <div class="card-footer bg-white border-0 pb-3 d-flex gap-2">
                    <button class="btn btn-primary btn-sm px-4"><i class="bi bi-upload me-1"></i>{{ __('Upload & check') }}</button>
                    <a href="{{ route('payroll.punch-import.template') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download me-1"></i>{{ __('Download sample CSV') }}</a>
                </div>
            </form>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100"><div class="card-body small">
                <h6 class="fw-bold mb-3">{{ __('What the file must look like') }}</h6>
                <ul class="mb-3">
                    <li>{{ __('First row = column headings. One punch (tap) per row.') }}</li>
                    <li><strong>{{ __('Employee ID') }}</strong> — {{ __('the ID used on the machine (matched to the "Machine ID" or Employee Code on the employee profile).') }}</li>
                    <li><strong>{{ __('Date & time') }}</strong> — {{ __('in one column (2026-10-11 08:02:10, 11/10/2026 08:02) or a Date column plus a Time column. Excel date cells are fine.') }}</li>
                    <li><strong>{{ __('Direction') }}</strong> — {{ __('optional: IN / OUT (or 0 / 1). Without it the first punch of a day is check-in and the last is check-out.') }}</li>
                    <li>{{ __('Other columns (name, department …) are ignored.') }}</li>
                </ul>
                <h6 class="fw-bold">{{ __('How it is checked') }}</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-danger fw-bold">{{ __('Error') }}</td><td>{{ __('Empty ID, unreadable or future date, date older than 2 years, unknown direction. These rows are never imported.') }}</td></tr>
                    <tr><td class="text-secondary fw-bold">{{ __('Duplicate') }}</td><td>{{ __('Same person and time already in the file or already stored — skipped, so uploading twice is safe.') }}</td></tr>
                    <tr><td class="text-warning fw-bold">{{ __('Unmapped') }}</td><td>{{ __('ID not linked to any employee — saved in the Punch Log; applied once you link it.') }}</td></tr>
                    <tr><td class="text-secondary fw-bold">{{ __('Locked') }}</td><td>{{ __('Payroll for that month is already generated — saved, not applied.') }}</td></tr>
                </table>
            </div></div>
        </div>
    </div>
</div>
</div>
</x-app-layout>
