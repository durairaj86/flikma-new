@php
    // Native locale switch: SetLocale reads session('locale') on every
    // request and calls App::setLocale(), so every __('...') call already
    // wrapped in the views below reads from lang/ar.json when this is 'ar'.
    $currentLocale = app()->getLocale();
    $targetLocale = $currentLocale === 'ar' ? 'en' : 'ar';
@endphp
<a href="{{ route('locale.switch', ['locale' => $targetLocale]) }}"
   class="btn btn-light border-0 rounded-circle header-icon-btn"
   id="language-toggle-btn"
   title="{{ $targetLocale === 'ar' ? 'التبديل إلى العربية' : 'Switch to English' }}"
   aria-label="{{ $targetLocale === 'ar' ? 'Switch to Arabic' : 'Switch to English' }}">
    <span class="fw-bold small text-secondary">{{ strtoupper($currentLocale) }}</span>
</a>

<style>
    /* Self-contained sizing so the button looks right whether it lands in
       the full header (which defines .header-icon-btn itself) or the slim
       topbar (which doesn't). */
    #language-toggle-btn.header-icon-btn {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
</style>
