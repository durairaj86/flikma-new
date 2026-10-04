@section('hide-topbar', true)
@section('page-title', 'Billing & Subscription')
@section('page-subtitle', 'Manage your plan, payment history, and subscription status.')
@section('js', 'billing')
<x-app-layout>
<main class="gmail-content bg-white">
@include('includes.inline-page-title')
<style>
    .bl-hero { border-radius: 1rem; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .12), rgba(var(--bs-primary-rgb), .03)); }
    .bl-hero-icon { width: 56px; height: 56px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

    .bl-plan-option {
        border: 2px solid var(--bs-border-color);
        border-radius: 1rem;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bs-body-bg);
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .bl-plan-option:hover { border-color: var(--bs-primary-border-subtle); background: var(--bs-tertiary-bg); }
    .bl-plan-option.is-selected { border-color: var(--bs-primary); background: rgba(var(--bs-primary-rgb), 0.03); }

    .bl-radio-circle {
        width: 24px;
        height: 24px;
        border: 2px solid var(--bs-border-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bl-plan-option.is-selected .bl-radio-circle { border-color: var(--bs-primary); }
    .bl-radio-circle::after {
        content: '';
        width: 12px;
        height: 12px;
        background: var(--bs-primary);
        border-radius: 50%;
        display: none;
    }
    .bl-plan-option.is-selected .bl-radio-circle::after { display: block; }

    .bl-plan-details { flex-grow: 1; min-width: 0; }
    .bl-plan-price-tag { font-weight: 800; font-size: 1.15rem; white-space: nowrap; }

    .bl-feature-item { font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--bs-secondary-color); }
    .bl-feature-item i { color: var(--bs-primary); margin-right: 0.5rem; }
    [dir="rtl"] .bl-feature-item i { margin-right: 0; margin-left: 0.5rem; }

    .bl-sidebar-card { border-radius: 1rem; border: 1px solid var(--bs-border-color); }
</style>
@php
    $hasActivePaidPlan = $company && $company->subscription_status === 'active'
        && !$company->is_in_trial
        && !$subscriptionExpired
        && $company->package_id;
@endphp

{{-- Flikma's shell gives the content area no top/left padding of its own, so the
     page supplies its own gutter here — same wrapper as billing/ai-usage. --}}
<div class="container-fluid px-4 py-4">

{{-- The shell header is the single page header: the title comes from
     @section('page-title'), the line under it from @section('page-subtitle'), and
     the plan badge is pushed into the header's action slot. No in-page title. --}}
@push('page-title-action')
    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
        <i class="bi bi-credit-card me-1"></i>{{ $planLabel }} {{ __('Plan') }}
    </span>
@endpush

@if(request('payment') === 'success')
<div class="alert alert-info d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="bi bi-hourglass-split fs-5"></i>
    <div>{{ __('Payment received — we\'re confirming it now. Your plan will update within a minute.') }}</div>
</div>
@elseif(request('payment') === 'cancelled')
<div class="alert alert-warning d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle fs-5"></i>
    <div>{{ __('Checkout was cancelled — no payment was made.') }}</div>
</div>
@elseif(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill"></i>
    <div>{{ session('success') }}</div>
</div>
@elseif(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>{{ session('error') }}</div>
</div>
@elseif(session('info'))
<div class="alert alert-info d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
    <i class="bi bi-info-circle-fill"></i>
    <div>{{ session('info') }}</div>
</div>
@endif

{{-- Status hero --}}
<div class="bl-hero p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <div class="bl-hero-icon bg-primary text-white flex-shrink-0">
            <i class="bi {{ $hasActivePaidPlan ? 'bi-credit-card' : ($subscriptionExpired ? 'bi-exclamation-triangle' : 'bi-hourglass-split') }}"></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0">{{ $planLabel }} {{ __('Plan') }}</h4>
                @if($isInTrial)
                    <span class="badge bg-info text-white rounded-pill px-3">{{ __('Trial') }}</span>
                @elseif($hasActivePaidPlan)
                    <span class="badge bg-success text-white rounded-pill px-3">{{ __('Active') }}</span>
                @else
                    <span class="badge bg-danger text-white rounded-pill px-3">{{ __('Expired') }}</span>
                @endif
            </div>
            @if($isInTrial)
                <p class="text-muted small mb-0">
                    <i class="bi bi-clock me-1"></i>{{ __('Free trial ends :date', ['date' => $company->trial_ends_at?->format('d M Y')]) }}
                </p>
            @elseif($hasActivePaidPlan)
                <p class="text-muted small mb-0">
                    <i class="bi bi-arrow-repeat me-1"></i>{{ __('Renews on :date', ['date' => $subscriptionExpiry?->format('d M Y')]) }}
                </p>
            @else
                <p class="text-muted small mb-0">{{ __('Your subscription has lapsed — choose a plan below to reactivate.') }}</p>
            @endif
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="bl-subscription-tab" data-bs-toggle="tab" data-bs-target="#bl-subscription" type="button" role="tab">{{ __('Subscription') }}</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bl-transactions-tab" data-bs-toggle="tab" data-bs-target="#bl-transactions" type="button" role="tab">{{ __('Transactions') }}</button>
    </li>
</ul>

{{-- Alpine scope wraps both the tab panes and the contact modal, because the
     modal binds :action/:value to selectedPlanId and cycle. In the reference
     build the modal sits outside this scope, so those bindings never resolve
     and the upgrade form cannot submit. --}}
<div x-data="{
    selectedPlanId: '{{ $currentPlan?->id ?? $plans[0]->id ?? '' }}',
    cycle: '{{ $company?->package_id ? 'yearly' : 'monthly' }}',
    plans: {{ Illuminate\Support\Js::from($plans) }},
    get selectedPlan() {
        return this.plans.find(p => p.id == this.selectedPlanId) || this.plans[0] || null;
    },
    get currentPrice() {
        const plan = this.selectedPlan;
        return this.cycle === 'monthly' ? Number(plan?.monthly_price || 0) : Number(plan?.yearly_price || 0);
    },
    priceFor(planId) {
        const plan = this.plans.find(p => p.id == planId);
        if (!plan) return 0;
        return this.cycle === 'monthly' ? Number(plan.monthly_price) : Number(plan.yearly_price);
    },
    monthlyFor(planId) {
        const plan = this.plans.find(p => p.id == planId);
        return plan ? Number(plan.monthly_price) : 0;
    },
    savingFor(planId) {
        const plan = this.plans.find(p => p.id == planId);
        if (!plan) return 0;
        const monthly = Number(plan.monthly_price), yearly = Number(plan.yearly_price);
        return monthly > 0 ? Math.round(((monthly * 12 - yearly) / (monthly * 12)) * 100) : 0;
    },
    isCurrentPlan(planId) {
        return String(planId) === '{{ $company?->package_id }}' && {{ $hasActivePaidPlan ? 'true' : 'false' }};
    },
    openContactModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('blContactModal')).show();
    }
}">

<div class="tab-content">
    {{-- Subscription tab --}}
    <div class="tab-pane fade show active" id="bl-subscription" role="tabpanel">
        <div class="row g-4">
            {{-- Left: Plan selection --}}
            <div class="col-lg-7">
                <h5 class="fw-bold mb-3">{{ __('Select your plan') }}</h5>

                @if(empty($plans))
                    <div class="text-center py-5 border rounded-4">
                        <i class="bi bi-box text-secondary" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2 mb-0">{{ __('No plans available right now.') }}</p>
                    </div>
                @else
                    <div class="mb-4">
                        <template x-for="plan in plans" :key="plan.id">
                            <div class="bl-plan-option"
                                 :class="String(selectedPlanId) === String(plan.id) ? 'is-selected' : ''"
                                 @click="selectedPlanId = plan.id">
                                <div class="bl-radio-circle"></div>
                                <div class="bl-plan-details">
                                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                        <h6 class="fw-bold mb-0 text-capitalize" x-text="plan.name"></h6>
                                        <div class="bl-plan-price-tag">
                                            <span>SAR <span x-text="Number(priceFor(plan.id)).toLocaleString()"></span></span>
                                            <span class="text-muted small fw-normal">/ <span x-text="cycle === 'monthly' ? '{{ __('month') }}' : '{{ __('year') }}'"></span></span>
                                            <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size:0.62rem; vertical-align:middle;"
                                                  x-show="cycle === 'yearly' && savingFor(plan.id) > 0"
                                                  x-text="'-' + savingFor(plan.id) + '%'"></span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:0.62rem; vertical-align:middle;"
                                                  x-show="isCurrentPlan(plan.id)">{{ __('Current') }}</span>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0 mt-1" x-text="plan.tagline || ''"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif

                <h5 class="fw-bold mb-3">{{ __('Billing Cycle') }}</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bl-plan-option"
                             :class="cycle === 'monthly' ? 'is-selected' : ''"
                             @click="cycle = 'monthly'">
                            <div class="bl-radio-circle"></div>
                            <div class="bl-plan-details">
                                <h6 class="fw-bold mb-0">{{ __('Monthly Plan') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Ideal for short-term trials.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bl-plan-option"
                             :class="cycle === 'yearly' ? 'is-selected' : ''"
                             @click="cycle = 'yearly'">
                            <div class="bl-radio-circle"></div>
                            <div class="bl-plan-details">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0">{{ __('Annual Plan') }}</h6>
                                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.65rem;">-{{ config('billing.annual_discount_percent') }}%</span>
                                </div>
                                <p class="text-muted small mb-0">{{ __('Commit for a year & save.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3">{{ __('What you\'ll unlock') }} →</h5>
                <div class="row g-2">
                    <template x-if="selectedPlan">
                        <template x-for="feature in (selectedPlan.feature_labels || [])" :key="feature">
                            <div class="col-md-6">
                                <div class="bl-feature-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span x-text="feature"></span>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template x-if="!selectedPlan || !(selectedPlan.feature_labels || []).length">
                        <div class="col-12">
                            <p class="text-muted small mb-0">{{ __('Select a plan to see the features it unlocks.') }}</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Right: Summary --}}
            <div class="col-lg-5">
                <div class="card bl-sidebar-card shadow-sm border-0 sticky-top" style="top: 1rem;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">{{ __('Subscription Summary') }}</h5>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">{{ __('Selected Plan') }}</span>
                            <span class="fw-bold text-capitalize" x-text="selectedPlan ? selectedPlan.name : '—'"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-secondary">{{ __('Billing Cycle') }}</span>
                            <span class="fw-bold text-capitalize" x-text="cycle === 'monthly' ? '{{ __('monthly') }}' : '{{ __('annual') }}'"></span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-end mb-4">
                            <div>
                                <h6 class="fw-bold mb-0">{{ __('Total') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Due now') }}</p>
                            </div>
                            <div class="text-end">
                                <span class="fs-3 fw-bold">SAR <span x-text="Number(currentPrice).toLocaleString()"></span></span>
                                <span class="text-muted small">/ <span x-text="cycle === 'monthly' ? '{{ __('month') }}' : '{{ __('year') }}'"></span></span>
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-primary w-100 py-3 fw-bold shadow-sm"
                                :disabled="isCurrentPlan(selectedPlanId)"
                                @click="isCurrentPlan(selectedPlanId) ? null : openContactModal()">
                            <i class="bi bi-envelope me-1"></i>
                            <span x-text="isCurrentPlan(selectedPlanId) ? '{{ __('Current Plan') }}' : '{{ $hasActivePaidPlan ? __('Switch Plan') : __('Subscribe Now') }}'"></span>
                        </button>

                        <div class="text-center mt-3">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-shield-check me-1"></i>{{ __('Secure — our team will confirm your upgrade.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions tab --}}
    <div class="tab-pane fade" id="bl-transactions" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($payments->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-receipt text-secondary" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2 mb-0">{{ __('No payments yet.') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th class="px-3">{{ __('Date') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Invoice') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th class="px-3 text-end">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                    <tr>
                                        <td class="px-3 small text-muted">{{ $payment->created_at->format('d M Y') }}</td>
                                        <td class="fw-medium">{{ $payment->package?->name ?? '—' }}</td>
                                        <td class="small text-muted">{{ $payment->invoice_number ?? '—' }}</td>
                                        <td class="text-end fw-semibold">SAR {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td class="px-3 text-end">
                                            @if($payment->payment_status === 'paid')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">{{ __('Paid') }}</span>
                                            @elseif($payment->payment_status === 'pending')
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ __('Pending') }}</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill">{{ ucfirst($payment->payment_status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Contact to upgrade modal --}}
<div class="modal fade" id="blContactModal" tabindex="-1" aria-labelledby="blContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="blContactModalLabel">{{ __('Contact us to upgrade') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form :action="'{{ route('billing.contact', ['package' => 'PLAN_ID']) }}'.replace('PLAN_ID', selectedPlanId)" method="POST">
                @csrf
                <input type="hidden" name="billing_cycle" :value="cycle">
                <div class="modal-body">
                    <template x-if="selectedPlan">
                        <div class="bg-light rounded-3 p-3 mb-3 small">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">{{ __('Plan') }}</span>
                                <span class="fw-bold text-capitalize" x-text="selectedPlan.name"></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">{{ __('Billing Cycle') }}</span>
                                <span class="fw-bold text-capitalize" x-text="cycle === 'monthly' ? '{{ __('monthly') }}' : '{{ __('annual') }}'"></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">{{ __('Total') }}</span>
                                <span class="fw-bold">SAR <span x-text="Number(currentPrice).toLocaleString()"></span></span>
                            </div>
                        </div>
                    </template>

                    <div class="mb-3">
                        <label class="form-label" for="blContactName">{{ __('Your Name') }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="blContactName" name="name"
                               value="{{ old('name', auth()->user()->name ?? '') }}" required maxlength="255">
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="blContactEmail">{{ __('Email') }}</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="blContactEmail" name="email"
                               value="{{ old('email', auth()->user()->email ?? '') }}" required maxlength="255">
                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="blContactPhone">{{ __('Phone') }} <span class="text-muted">({{ __('optional') }})</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="blContactPhone" name="phone"
                               value="{{ old('phone') }}" maxlength="20">
                        @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-1">
                        <label class="form-label" for="blContactMessage">{{ __('Message') }} <span class="text-muted">({{ __('optional') }})</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="blContactMessage" name="message"
                                  rows="3" maxlength="2000">{{ old('message') }}</textarea>
                        @error('message') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-envelope me-1"></i>{{ __('Contact Us') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
</div>
</main>
</x-app-layout>

<script>
    (function () {
        @if(session('success'))
        if (window.toastr) {
            toastr.success(@json(session('success')));
        }
        @endif

        @if($errors->any())
        bootstrap.Modal.getOrCreateInstance(document.getElementById('blContactModal')).show();
        @endif
    })();
</script>
