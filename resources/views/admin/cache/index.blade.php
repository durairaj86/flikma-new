@extends('admin.layouts.app')
@section('title', 'Cache Management')
@section('content')
    @php
        $buttons = [
            'app' => ['btn-outline-primary', 'bi-arrow-repeat', 'Clear app cache'],
            'config' => ['btn-outline-warning', 'bi-gear', 'Clear config cache'],
            'route' => ['btn-outline-info', 'bi-signpost-2', 'Clear route cache'],
            'view' => ['btn-outline-secondary', 'bi-eye', 'Clear view cache'],
            'queue' => ['btn-outline-danger', 'bi-play', 'Queue restart'],
        ];
    @endphp
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Cache Management</h4>
        <p class="text-muted small mb-0">Inspect system info and clear application caches.</p>
    </div>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">System information</span></div>
                <div class="card-body px-4 pb-4 pt-0">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                        <tr><th class="text-secondary ps-0 w-50">Cache driver</th><td class="pe-0"><span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2 py-1">{{ $cacheDriver }}</span></td></tr>
                        <tr><th class="text-secondary ps-0">OPcache</th><td class="pe-0"><span class="badge rounded-pill bg-{{ $opcacheEnabled ? 'success' : 'secondary' }}-subtle text-{{ $opcacheEnabled ? 'success' : 'secondary' }}-emphasis px-2 py-1">{{ $opcacheEnabled ? 'Enabled' : 'Disabled' }}</span></td></tr>
                        <tr><th class="text-secondary ps-0">Laravel version</th><td class="text-dark pe-0">{{ app()->version() }}</td></tr>
                        <tr><th class="text-secondary ps-0">PHP version</th><td class="text-dark pe-0">{{ PHP_VERSION }}</td></tr>
                        <tr><th class="text-secondary ps-0">App version</th><td class="text-dark pe-0">{{ appVersion() }}</td></tr>
                        <tr><th class="text-secondary ps-0">Environment</th><td class="text-dark text-uppercase pe-0">{{ app()->environment() }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Cache commands</span></div>
                <div class="card-body">
                    @if(session('cacheResults'))
                        <div class="mb-3">
                            @foreach(session('cacheResults') as $result)
                                <div class="alert alert-{{ $result['status'] === 'success' ? 'success' : 'danger' }} py-2 small mb-2">
                                    <strong>{{ $result['label'] }}:</strong> {{ $result['output'] ?: 'Done' }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="row g-2">
                        @foreach($buttons as $action => [$class, $icon, $label])
                            <div class="col-md-4">
                                <form action="{{ route('admin.cache.clear') }}" method="POST">@csrf
                                    <input type="hidden" name="action" value="{{ $action }}">
                                    <button type="submit" class="btn {{ $class }} w-100"><i class="bi {{ $icon }}"></i> {{ $label }}</button>
                                </form>
                            </div>
                        @endforeach
                        <div class="col-md-4">
                            <form action="{{ route('admin.cache.clear') }}" method="POST" onsubmit="return confirm('Clear all caches?')">@csrf
                                <input type="hidden" name="action" value="all">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-trash"></i> Clear all caches</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
