@extends('admin.layouts.app')
@section('title', 'Create Newsletter')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css">
@endpush
@section('content')
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-0">Create Newsletter</h4>
    </div>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Newsletter details</span></div>
        <div class="card-body p-4">
            <form action="{{ route('admin.newsletters.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nlSubject">Subject *</label>
                        <input type="text" id="nlSubject" name="subject" maxlength="255" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required autofocus>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="recipientType">Recipients *</label>
                        <select name="recipient_type" id="recipientType" class="form-select @error('recipient_type') is-invalid @enderror" required>
                            @foreach(\App\Models\Admin\Newsletter::RECIPIENT_TYPES as $key => $label)
                                <option value="{{ $key }}" @selected(old('recipient_type', 'all') === $key)>{{ $label }}{{ isset($counts[$key]) ? ' (' . $counts[$key] . ')' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Sent to each company's email address. Companies without an email are left out.</div>
                    </div>
                    <div class="col-12" id="selectedCompanies" style="display:none;">
                        <label class="form-label" for="nlCompanies">Select companies</label>
                        <select name="recipient_ids[]" id="nlCompanies" class="form-select" multiple style="height:200px;">
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" @selected(in_array($c->id, old('recipient_ids', [])))>{{ $c->name }} ({{ $c->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (or Cmd) to pick several.</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email body *</label>
                        <textarea name="body" class="form-control summernote @error('body') is-invalid @enderror" rows="15">{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nlSchedule">Schedule sending (optional)</label>
                        <input type="datetime-local" id="nlSchedule" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}">
                        <div class="form-text">Leave empty to save as a draft and send it yourself.</div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create Newsletter</button>
                    <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
    $('.summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview']],
        ],
    });
    const sync = () => $('#selectedCompanies').toggle($('#recipientType').val() === 'selected');
    $('#recipientType').on('change', sync);
    sync();
</script>
@endpush
