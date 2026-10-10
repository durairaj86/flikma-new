{{--
    Top of the right-hand content on Masters pages, beside the Master Data menu.
    Header on : the full header (breadcrumb, title, action icons) is drawn here, starting at the menu's right edge.
    Header off: the page title (+ subtitle) on the left and the page's actions on the right, on one row.
    A page adds its main button with @push('master-title-actions').
--}}
@php
    /* Empty-state copy per Masters page: what the list is for and where it is used. Keyed by URL path. */
    $mEmpty = [
        'masters/services' => ['bi-diagram-3', 'The departments you run, such as FCL Import, FCL Export, LCL Import, Air Export or Land Import.', 'Used on enquiries, quotations and jobs to say which department handles the shipment, and to group your reports.'],
        'masters/package/codes' => ['bi-box-seam', 'Packing types such as Carton, Pallet or Drum.', 'Used in the cargo details of quotations, jobs and bills of lading.'],
        'masters/quotation-terms' => ['bi-file-text', 'Ready-made terms and conditions you can reuse.', 'Used at the bottom of quotations so you do not retype your terms each time.'],
        'masters/hs-tariffs' => ['bi-upc-scan', 'Harmonized System (HS) codes with their duty rate and unit.', 'Used in customs clearance work: pick the commodity code to get the duty percentage and unit on quotations and jobs.'],
        'masters/period-closing' => ['bi-lock', 'Closing dates that lock the books.', 'Once a period is closed, no invoice, payment or journal dated on or before that date can be added or changed.'],
        'masters/container/types' => ['bi-box', 'Container sizes and types such as 20GP, 40GP or 40HC.', 'Used on enquiries, quotations, jobs and bills of lading.'],
        'masters/incoterms' => ['bi-globe2', 'Trade terms such as FOB, CIF or EXW.', 'Used on quotations, jobs and bills of lading to show who pays for and carries the risk of each leg.'],
        'masters/currencies' => ['bi-currency-exchange', 'The currencies you trade in, with their codes.', 'Used on quotations, invoices and payments, and for exchange-rate conversion.'],
        'masters/departments' => ['bi-diagram-2', 'The departments in your company, such as Sales or Operations.', 'Used when you add users and employees, and in payroll.'],
        'masters/users' => ['bi-person-badge', 'Everyone who works for your company: employees, with or without a login.', 'Each employee has a department, role and payroll details; a login is optional.'],
        'masters/transport/directories/seaports' => ['bi-water', 'Sea ports with their codes and countries.', 'Used as the port of loading and discharge on enquiries, quotations, jobs and bills of lading.'],
        'masters/transport/directories/airports' => ['bi-airplane', 'Airports with their codes and countries.', 'Used as the origin and destination on air enquiries, quotations, jobs and airway bills.'],
        'masters/banks' => ['bi-bank', 'Your bank and cash accounts.', 'Used for receiving payments from customers and paying suppliers, and printed on invoices.'],
        'masters/descriptions' => ['bi-card-text', 'The charge descriptions you bill, such as Ocean Freight or Handling.', 'Used as the line items on invoices, quotations and credit notes, each linked to its sale and purchase account.'],
        'masters/salesperson' => ['bi-person-badge', 'Your sales team members.', 'Used to assign customers, enquiries and quotations to a salesperson and to report on their sales.'],
        'masters/units' => ['bi-rulers', 'Units of measure such as Piece, Kg or Box.', 'Used on invoice lines, quotations and cargo details.'],
    ];
    $mEmptyInfo = $mEmpty[trim(request()->path(), '/')] ?? null;
@endphp
<style>
    /* Empty list: the card shrinks to the message instead of keeping its table height. */
    .ms-help { display: inline-block; font-size: .95rem; font-weight: 400; color: #98a2b3; cursor: help; vertical-align: middle; }
    .ms-help:hover, .ms-help:focus { color: #4f46e5; outline: 0; }
    .ms-empty { min-height: 0 !important; }
    .ms-empty .overflow-auto, .ms-empty > div { min-height: 0 !important; }
    .ms-empty table.dataTable > thead { display: none; }
    .ms-empty-state { text-align: center; padding: 36px 16px 30px; white-space: normal; }
    .ms-empty-state .ms-empty-icon { width: 56px; height: 56px; border-radius: 50%; background: #eef2ff; color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px; }
    .ms-empty-state h6 { font-weight: 700; margin-bottom: 6px; }
    .ms-empty-state p { margin: 0 auto 4px; max-width: 520px; color: #667085; font-size: .875rem; }
</style>
<script>
    /* Nothing in the list yet: explain what this master is for and offer the create button. */
    (function () {
        var info = @json($mEmptyInfo);
        var title = @json(strip_tags((string) View::yieldContent('page-title')));
        // "?" next to the page title with the same explanation as the empty state, shown once the list has records.
        function helpIcon(show) {
            if (!info) return;
            var h = $('.master-title-block h4').first();
            if (!h.length) h = $('header h5').first();
            if (!h.length) return;
            var el = h.find('.ms-help');
            if (!el.length) {
                el = $('<span class="ms-help ms-1" tabindex="0" role="img" aria-label="' + @json(__('What is this?')) + '"><i class="bi bi-question-circle"></i></span>').appendTo(h);
                el.attr({ 'data-bs-toggle': 'tooltip', 'data-bs-placement': 'bottom', 'data-bs-title': info[1] + ' ' + info[2] });
                if (window.bootstrap) new bootstrap.Tooltip(el[0]);
            }
            el.toggleClass('d-none', !show);
        }
        $(document).on('draw.dt', '#dataTable', function (e, settings) {
            try {
                var api = new $.fn.dataTable.Api(settings);
                var pi = api.page.info();
                var card = $(this).closest('.shadow.bdr-r-10');
                var none = pi.recordsTotal === 0;
                card.toggleClass('ms-empty', none);
                helpIcon(info && !none);
                if (!none) return;
                var cell = $(this).find('td.dataTables_empty');
                if (!cell.length || cell.find('.ms-empty-state').length) return;
                var newBtn = $('#new');
                var label = $.trim(newBtn.text());
                var html = '<div class="ms-empty-state">' +
                    '<span class="ms-empty-icon"><i class="bi ' + (info ? info[0] : 'bi-inbox') + '"></i></span>' +
                    '<h6>' + @json(__('No records yet')) + '</h6>' +
                    '<p>' + (info ? info[1] : '') + '</p>' +
                    (info ? '<p class="mb-0">' + info[2] + '</p>' : '') +
                    (newBtn.length ? '<button type="button" class="btn btn-primary rounded-pill px-4 mt-3 ms-empty-create"><i class="bi bi-plus-lg me-1"></i> ' + label + '</button>' : '') +
                    '</div>';
                cell.html(html);
            } catch (err) { /* never block a table draw */ }
        });
        $(document).on('click', '.ms-empty-create', function () { $('#new').trigger('click'); });
    })();
</script>
<style>
    /* The list card hugs its rows instead of stretching down to the bottom of the page. */
    section.d-flex.flex-column > .shadow.bdr-r-10.flex-grow-1 { flex-grow: 0 !important; align-self: stretch; min-height: 230px; }
</style>
@if(headerEnabledForUser())
    @php
        $mSeg = request()->segments();
        $segment1 = $mSeg[0] ?? '';
        $segment2 = $mSeg[1] ?? '';
        $segment3 = $mSeg[2] ?? '';
        $segment4 = $mSeg[3] ?? '';
    @endphp
    <div class="mx-n4 mb-3">
        @include('includes.header')
    </div>
    @hasstack('master-title-actions')
        <div class="d-flex justify-content-end mb-3">@stack('master-title-actions')</div>
    @endif
@elseif(!View::hasSection('hide-master-title'))
    <div class="pt-3 pb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap" id="masterTitleRow">
        <div class="master-title-block">
            <h4 class="fw-bold text-dark mb-0">@yield('page-title')</h4>
            @hasSection('page-subtitle')
                <div class="text-muted small mt-1">@yield('page-subtitle')</div>
            @endif
        </div>
        @hasstack('master-title-actions')
            <div class="d-flex align-items-center gap-2">@stack('master-title-actions')</div>
        @endif
    </div>
    <style>
        /* Header off, pages with status tabs (Users, Seaports, Airports): title + search + New button first, tabs under them, then the table. */
        section.d-flex.flex-column:has(#listTabs) > * { order: 1; }
        section.d-flex.flex-column:has(#listTabs) > :has(#listTabs) { order: 2; }
        section.d-flex.flex-column:has(#listTabs) > .shadow { order: 3; }
    </style>
    <script>
        /* Header off: fold the title into the page's own toolbar row (search + New button), so the title sits on
           the left and the search / button on the right, like the Opening Balance page. Pages whose toolbar has
           tabs keep the title on its own row. */
        document.addEventListener('DOMContentLoaded', function () {
            var titleRow = document.getElementById('masterTitleRow');
            var btn = document.getElementById('new');
            if (!titleRow || !btn) return;
            var row = btn.closest('.d-flex.justify-content-between');
            if (!row || row === titleRow || row.querySelector('.nav, .nav-link')) return;
            var block = titleRow.querySelector('.master-title-block');
            var right = document.createElement('div');
            right.className = 'd-flex align-items-center gap-2';
            if (!row.querySelector('.search-box')) {
                // A toolbar with a description instead of a search box: the description moves under the title.
                var note = row.querySelector('p.text-muted');
                if (!note) return;
                var holder = note.parentElement;
                note.classList.add('mt-1');
                block.appendChild(note);
                if (holder && holder !== row && !holder.children.length) holder.remove();
            }
            while (row.firstChild) right.appendChild(row.firstChild);
            row.classList.remove('pb-3', 'mb-3', 'align-items-start');
            row.classList.add('align-items-center', 'flex-wrap', 'gap-3', 'pt-3', 'pb-3');
            row.appendChild(block);
            row.appendChild(right);
            titleRow.remove();
        });
    </script>
@endif
