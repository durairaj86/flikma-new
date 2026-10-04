{{-- Month picker for a widget. Alpine handles open/close; wire:click re-renders only this widget. --}}
<div class="wd-month" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" class="wd-month-btn" :class="{ 'is-open': open }" @click="open = !open" aria-haspopup="listbox" :aria-expanded="open">
        <i class="bi bi-calendar3"></i>
        <span>{{ $months[$month] ?? $month }}</span>
        <i class="bi bi-chevron-down wd-month-caret"></i>
    </button>
    <div class="wd-month-menu" x-cloak x-show="open" x-transition.origin.top.right role="listbox">
        @foreach($months as $val => $label)
            <button type="button" role="option" class="wd-month-item {{ $val === $month ? 'active' : '' }}"
                    wire:click="setMonth('{{ $val }}')" @click="open = false">
                <span>{{ $label }}</span>
                @if($val === $month)<i class="bi bi-check2"></i>@endif
            </button>
        @endforeach
    </div>
</div>
