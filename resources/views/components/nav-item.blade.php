@props(['href', 'icon' => null, 'active' => false])

<li class="nav-item">
    <a href="{{ $href }}"
       wire:navigate
        {{ $attributes->class(['nav-link d-flex align-items-center py-2', 'active bg-primary text-white' => $active, 'text-light' => !$active]) }}>
        @if($icon) <i class="bi bi-{{ $icon }} me-2"></i> @endif
        <span>{{ $slot }}</span>
    </a>
</li>
