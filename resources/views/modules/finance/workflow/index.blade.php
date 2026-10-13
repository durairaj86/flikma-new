@section('page-title', __('Finance Entries Workflow'))
@section('page-subtitle', __('How every finance entry is saved, which tables it touches, and what changed'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">
        <style>
            .fw h5 { font-weight: 700; margin: 2rem 0 .25rem; }
            .fw .lead-s { color: #667085; font-size: .9rem; margin-bottom: .9rem; }
            .fw-nav a { border-radius: 50rem; font-size: .8rem; }
            .fw-flow { display: flex; flex-wrap: wrap; align-items: stretch; gap: .35rem; margin: .6rem 0 1rem; }
            .fw-box { border: 1px solid #d0d5dd; border-radius: 12px; padding: .55rem .8rem; background: #f9fafb; font-size: .82rem; min-width: 130px; max-width: 230px; }
            .fw-box b { display: block; font-size: .84rem; }
            .fw-box small { color: #667085; }
            .fw-box.doc { background: #eef4ff; border-color: #b2ccff; }
            .fw-box.act { background: #fffaeb; border-color: #fedf89; }
            .fw-box.tbl { background: #ecfdf3; border-color: #a6f4c5; }
            .fw-box.view { background: #f4f3ff; border-color: #d9d6fe; }
            .fw-box.rep { background: #fdf2fa; border-color: #fcceee; }
            .fw-box.warn { background: #fef3f2; border-color: #fecdca; }
            .fw-arrow { align-self: center; color: #98a2b3; font-size: 1.2rem; }
            .fw-stack { display: flex; flex-direction: column; gap: .35rem; justify-content: center; }
            .fw table { font-size: .82rem; }
            .fw table th { background: #f2f4f7; font-weight: 600; white-space: nowrap; }
            .fw .dr { color: #175cd3; font-weight: 600; } .fw .cr { color: #b54708; font-weight: 600; }
            .fw .chip { display: inline-block; border-radius: 50rem; padding: .05rem .55rem; font-size: .72rem; font-weight: 600; }
            .chip.done { background: #d1fadf; color: #05603a; } .chip.todo { background: #fef0c7; color: #93370d; } .chip.gap { background: #fee4e2; color: #b42318; }
            .fw-card { border: 1px solid #eaecf0; border-radius: 12px; padding: .8rem 1rem; height: 100%; }
            .fw-card h6 { font-weight: 700; margin-bottom: .25rem; } .fw-card code { font-size: .75rem; }
            .fw-code { background: #101828; color: #e4e7ec; border-radius: 10px; padding: .7rem .9rem; font-size: .78rem; overflow-x: auto; }
        </style>
        <div class="fw">
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2">
                <h4 class="fw-bold text-dark mb-0">{{ __('Finance Entries Workflow') }}</h4>
                <span class="text-muted small">{{ __('Ledger reports read the view') }}: <b>{{ $useLedger ? __('ON') : __('OFF (old queries)') }}</b></span>
            </div>
            <p class="lead-s mt-1">{{ __('Every approved document is turned into one balanced journal entry: a header in finance and its debit / credit lines in finance_subs. Reports, settlements and job totals are all derived from that journal.') }}</p>
            <div class="fw-nav d-flex flex-wrap gap-2 mb-2">
                @foreach(['overview' => 'Big picture', 'saving' => 'How an entry is saved', 'posting' => 'Posting by document', 'settle' => 'Settlement', 'tables' => 'Tables', 'reports' => 'Reports', 'changes' => 'What changed', 'gaps' => 'Gaps & decisions', 'live' => 'Live data'] as $id => $label)
                    <a href="#{{ $id }}" class="btn btn-sm btn-outline-secondary">{{ __($label) }}</a>
                @endforeach
            </div>

            {{-- 1. big picture --}}
            <h5 id="overview">1. {{ __('Big picture') }}</h5>
            <p class="lead-s">{{ __('One direction only: a document becomes a journal entry; everything else is read from the journal.') }}</p>
            <div class="fw-flow">
                <div class="fw-box doc"><b>{{ __('Document') }}</b><small>{{ __('Invoice, collection, payment, credit / debit note, expense, opening balance, payroll') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>{{ __('Approve') }}</b><small>{{ __('Draft documents post nothing. Posting happens on approval.') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>{{ __('Poster') }}</b><small>{{ __('Controller code (one per document type) checks the period, builds balanced lines.') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-stack">
                    <div class="fw-box tbl"><b>finance</b><small>{{ __('Entry header: voucher no, type, date, totals, source') }}</small></div>
                    <div class="fw-box tbl"><b>finance_subs</b><small>{{ __('Lines: account, debit, credit, partner, job, tax') }}</small></div>
                </div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-stack">
                    <div class="fw-box view"><b>finance_ledgers</b><small>{{ __('VIEW over the journal. Posted lines + account details. Reports read this.') }}</small></div>
                    <div class="fw-box tbl"><b>finance_allocations</b><small>{{ __('Which receipt / payment / credit note settled which invoice line') }}</small></div>
                    <div class="fw-box tbl"><b>job_links</b><small>{{ __('Every document of a job in one table (job screens)') }}</small></div>
                </div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box rep"><b>{{ __('Reports') }}</b><small>{{ __('Trial Balance, P&L, Balance Sheet, Tax, Supplier Ledger, Aging, Job reports') }}</small></div>
            </div>

            {{-- 2. saving --}}
            <h5 id="saving">2. {{ __('How an entry is saved') }}</h5>
            <p class="lead-s">{{ __('Same steps for every document type (today each type has its own copy of this code).') }}</p>
            <div class="fw-flow">
                <div class="fw-box doc"><b>1. {{ __('User saves') }}</b><small>{{ __('Document table + its sub-lines (e.g. customer_invoices + customer_invoice_subs), status Draft') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>2. {{ __('User approves') }}</b><small>{{ __('Status changes to Approved; a number is assigned (e.g. IN/26/0002)') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>3. {{ __('Period check') }}</b><small>{{ __('A closed period (period_closings) refuses the change') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>4. {{ __('Old entry removed') }}</b><small>{{ __('If the document was posted before, its old finance + lines are deleted (planned: replaced by a reversal entry)') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box tbl"><b>5. {{ __('Header inserted') }}</b><small>finance: is_approved = 1, posted_at, linked_type / linked_id</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box tbl"><b>6. {{ __('Lines inserted') }}</b><small>finance_subs: total debit = total credit (base currency)</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box act"><b>7. {{ __('Side effects') }}</b><small>{{ __('Observers refresh job_links and the job invoice summary; payments update paid_amount and status') }}</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box view"><b>8. {{ __('Hourly sync') }}</b><small>finance:sync-columns fills entry date, partner, tax, source; finance:sync-allocations fills allocations</small></div>
            </div>

            {{-- 3. posting table --}}
            <h5 id="posting">3. {{ __('Posting by document') }}</h5>
            <p class="lead-s">{{ __('Exactly which accounts each document debits and credits (base currency; account ids are fixed in the code today).') }}</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead><tr><th>{{ __('Document') }}</th><th>{{ __('Voucher') }}</th><th>{{ __('Posts when') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Source link') }}</th></tr></thead>
                    <tbody>
                    @foreach([
                        ['Customer Invoice', 'CI', 'Approved', 'Accounts Receivable 1130 (invoice total)', 'Income account per line (e.g. 4160) + Output VAT 2130', 'customer_invoice'],
                        ['Supplier Invoice', 'SI', 'Approved', 'Expense / cost account per line (e.g. 5110) + Input VAT 1150', 'Accounts Payable 2110 (invoice total)', 'supplier_invoice'],
                        ['Collection (receipt)', 'CV', 'Approved', 'Bank / cash account chosen (received amount); Bank Charges 5260; Other Charges 5280', 'Accounts Receivable 1130, one line per invoice settled', 'collection (+ collection_invoices)'],
                        ['Payment', 'PV', 'Approved', 'Accounts Payable 2110, one line per invoice paid; Bank Charges 5260; Other Charges 5280; extra transactions', 'Bank / cash account chosen (whole amount going out)', 'payment (+ payment_invoices)'],
                        ['Credit Note', 'CN', 'Approved', 'Income account per line + Output VAT 2130 (reversal)', 'Accounts Receivable 1130', 'credit_note (against an invoice)'],
                        ['Debit Note', 'DN', 'Approved', 'Accounts Payable 2110', 'Cost account per line + Input VAT 1150 (reversal)', 'debit_note (against a supplier invoice)'],
                        ['Expense', 'EV', 'Approved', 'Expense account per line + Input VAT 1150', 'Bank / cash account chosen; currency difference account when rates differ', 'expense'],
                        ['Opening Balance', 'OB', 'Saved (no approval)', 'Customer / supplier balances on 1130 / 2110, or any other account', 'Opposite side; one automatic balancing line on Owner Capital 3100 if needed', 'opening_balance'],
                        ['Payroll', 'PR / LV', 'Salary paid', 'Staff Salaries 5200', 'Bank / cash; Employee Loan Receivable 1180 for loan deductions', 'payroll'],
                        ['Journal Voucher', 'JV', 'Never (gap)', '-', '-', 'Approving a journal voucher only changes its status. It does NOT create a finance entry today.'],
                        ['Proforma Invoice / drafts', '-', 'Never', 'Not an accounting event', 'Not an accounting event', 'Shown through job_links only'],
                    ] as [$doc, $v, $when, $dr, $cr, $src])
                        <tr class="{{ $v === 'JV' ? 'table-danger' : '' }}">
                            <td><b>{{ __($doc) }}</b></td><td>{{ $v }}</td><td>{{ __($when) }}</td><td class="dr">{{ __($dr) }}</td><td class="cr">{{ __($cr) }}</td><td class="text-muted">{{ __($src) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="lead-s">{{ __('Worked example (invoice IN/26/0002, 2,000 + 15% VAT):') }}</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" style="max-width:760px">
                    <thead><tr><th>{{ __('Entry') }}</th><th>{{ __('Account') }}</th><th class="text-end">{{ __('Debit') }}</th><th class="text-end">{{ __('Credit') }}</th><th>{{ __('Notes') }}</th></tr></thead>
                    <tbody>
                    <tr><td rowspan="3">CI IN/26/0002</td><td>1130 Accounts Receivable</td><td class="text-end dr">2,300.00</td><td></td><td>{{ __('settleable line, carries the customer') }}</td></tr>
                    <tr><td>4160 Service Income</td><td></td><td class="text-end cr">2,000.00</td><td></td></tr>
                    <tr><td>2130 Output VAT Payable</td><td></td><td class="text-end cr">300.00</td><td>{{ __('tax line: 15%, output, base 2,000') }}</td></tr>
                    <tr><td rowspan="2">CV COL/26/0001</td><td>1120 Bank</td><td class="text-end dr">1,725.00</td><td></td><td>{{ __('money received') }}</td></tr>
                    <tr><td>1130 Accounts Receivable</td><td></td><td class="text-end cr">1,725.00</td><td>{{ __('settles part of the invoice') }}</td></tr>
                    <tr><td rowspan="3">CN (credit note)</td><td>4160 Service Income</td><td class="text-end dr">500.00</td><td></td><td></td></tr>
                    <tr><td>2130 Output VAT Payable</td><td class="text-end dr">75.00</td><td></td><td></td></tr>
                    <tr><td>1130 Accounts Receivable</td><td></td><td class="text-end cr">575.00</td><td>{{ __('settles the rest of the invoice') }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- 4. settlement --}}
            <h5 id="settle">4. {{ __('Settlement: invoice, receipt, credit note') }}</h5>
            <p class="lead-s">{{ __('Outstanding is never typed in. It is the invoice line minus every allocation made up to the date you ask about.') }}</p>
            <div class="fw-flow">
                <div class="fw-box doc"><b>{{ __('Invoice line') }}</b><small>finance_subs, 1130, debit 2,300<br>{{ __('is_reconcilable = 1') }}</small></div>
                <div class="fw-arrow">&larr;</div>
                <div class="fw-stack">
                    <div class="fw-box tbl"><b>finance_allocations #1</b><small>{{ __('collection line, 1,725, 6 Oct') }}</small></div>
                    <div class="fw-box tbl"><b>finance_allocations #2</b><small>{{ __('credit note line, 575, 6 Oct') }}</small></div>
                </div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box view"><b>{{ __('Outstanding as of a date') }}</b><small>2,300 &minus; allocations up to that date<br>= 0 on 6 Oct, 2,300 on 5 Oct</small></div>
                <div class="fw-arrow">&rarr;</div>
                <div class="fw-box rep"><b>{{ __('Used by') }}</b><small>{{ __('Customer / supplier balance, aging, invoice status, paid_amount (kept as a cache)') }}</small></div>
            </div>
            <p class="lead-s mb-1">{{ __('Where the allocation rows come from today:') }}</p>
            <ul class="small">
                <li>collection_invoices &rarr; {{ __('receipt line settles the customer invoice line') }}</li>
                <li>payment_invoices &rarr; {{ __('payment line settles the supplier invoice line') }}</li>
                <li>credit_notes.invoice_id / debit_notes.invoice_id &rarr; {{ __('the note line settles the invoice line') }}</li>
                <li>{{ __('Planned: reversing a receipt marks its allocation reversed from that date; earlier dates stay unchanged.') }}</li>
            </ul>

            {{-- 5. tables --}}
            <h5 id="tables">5. {{ __('Tables and how they relate') }}</h5>
            <div class="row g-3">
                @foreach([
                    ['finance', 'Journal entry header', 'voucher_no, voucher_type, reference_date, total_debit/credit (+ base), is_approved, posted_at, linked_type/linked_id, job_id; added: state, is_provisional, reverses_id, period, source_type/source_id', 'one row per posted document. Has many finance_subs.'],
                    ['finance_subs', 'Journal lines (the ledger)', 'finance_id, account_id, debit, credit, base_debit, base_credit, customer_id, supplier_id, job_id, is_tax_line; added: entry_date, partner_type/id, tax_code/rate/type, taxable_base, source, is_reconcilable', 'one row per account line. Belongs to finance and accounts.'],
                    ['finance_ledgers (view)', 'Reporting interface', 'finance_subs + finance + accounts: transaction_date, account_code/name/type, partner, job, tax, source, posting_status, is_provisional', 'read-only. Posted entries only. No stored data.'],
                    ['finance_allocations', 'Settlements', 'settled_line_id, settling_line_id, allocation_type, allocation_date, amount, base_amount, fx_gain_loss, status, reversed_at', 'links two finance_subs lines (invoice line, receipt / note line).'],
                    ['accounts', 'Chart of accounts', 'code, name, type (Asset / Liability / Equity / Income / Expense), is_active, is_posting', 'global (company NULL) or per company.'],
                    ['collection_invoices / payment_invoices', 'Settlement records the screens keep', 'collection_id / payment_id, invoice id, amount', 'source of the allocation rows today.'],
                    ['job_links', 'Documents per job', 'job_id, linkable (morph), doc_type, state, amount, paid', 'kept by model observers; not part of the journal.'],
                    ['period_closings', 'Locked periods', 'company_id, year, closing_date, is_closed', 'checked before a document is saved or approved.'],
                ] as [$t, $title, $cols, $rel])
                    <div class="col-md-6 col-xl-4"><div class="fw-card"><h6><code>{{ $t }}</code> <span class="text-muted fw-normal">- {{ __($title) }}</span></h6><div class="small text-muted mb-1">{{ $cols }}</div><div class="small">{{ __($rel) }}</div></div></div>
                @endforeach
            </div>
            <div class="fw-flow mt-3">
                <div class="fw-box doc"><b>{{ __('Source document') }}</b><small>customer_invoices, supplier_invoices, collections, payments, credit_notes, debit_notes, expenses</small></div>
                <div class="fw-arrow">1 &rarr; 1</div>
                <div class="fw-box tbl"><b>finance</b><small>linked_type + linked_id (source_type + source_id)</small></div>
                <div class="fw-arrow">1 &rarr; n</div>
                <div class="fw-box tbl"><b>finance_subs</b><small>finance_id</small></div>
                <div class="fw-arrow">n &rarr; 1</div>
                <div class="fw-box tbl"><b>accounts</b><small>account_id</small></div>
                <div class="fw-arrow">&nbsp;</div>
                <div class="fw-box tbl"><b>finance_allocations</b><small>settled_line_id / settling_line_id &rarr; finance_subs.id</small></div>
            </div>

            {{-- 6. reports --}}
            <h5 id="reports">6. {{ __('Which report reads what') }}</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead><tr><th>{{ __('Report') }}</th><th>{{ __('Reads') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @foreach([
                        ['Trial Balance, Profit & Loss, Balance Sheet', 'finance_ledgers (one grouped query)', 'done'],
                        ['Tax Summary, Input Tax, Output Tax', 'finance_ledgers (tax lines, accounts 1150 / 2130)', 'done'],
                        ['Supplier Ledger', 'finance_ledgers, payable account 2110 only (fixes the always-zero balance)', 'done'],
                        ['Customer Statement, Supplier Statement', 'finance_ledgers, receivable 1130 / payable 2110 lines of the partner (same numbers as before)', 'done'],
                        ['Customer / Supplier Aging (and the customer / supplier detail screens)', 'finance_allocations as of the chosen date: receipts, payments AND credit / debit notes, reversals honoured', 'done'],
                        ['Job Balance (screen, cards, export)', 'actual income = posted CI minus CN, cost = posted SI minus DN, VAT excluded (finance_ledgers by job_id)', 'done'],
                        ['Provisional Report', 'provisional = proforma invoices and expenses (documents, VAT excluded); actual = the books as in Job Balance; profit uses actual when it exists, else provisional', 'done'],
                        ['Job Income, Job Report', 'stay on documents on purpose: Job Income is an invoicing view (approved vs draft, per invoice line, VAT-inclusive invoice totals); Job Report is the job list', 'todo'],
                        ['Provisional Report', 'proforma + expense documents; planned: ledger lines flagged provisional', 'todo'],
                    ] as [$r, $src, $st])
                        <tr><td><b>{{ __($r) }}</b></td><td>{{ __($src) }}</td><td><span class="chip {{ $st }}">{{ $st === 'done' ? __('Switched') : __('Not yet') }}</span></td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 7. what changed --}}
            <h5 id="changes">7. {{ __('What was done in the finance redesign') }}</h5>
            <p class="lead-s">{{ __('All additive: nothing was dropped or renamed, and the old report code is still there behind a switch.') }}</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead><tr><th>{{ __('Phase') }}</th><th>{{ __('What') }}</th><th>{{ __('Command / file') }}</th><th></th></tr></thead>
                    <tbody>
                    @foreach([
                        ['0', 'Baseline: read-only integrity checks (entries balance, headers = lines, control accounts, allocations, view parity)', 'php artisan finance:reconcile', 'done'],
                        ['1', 'New columns and 9 indexes on finance / finance_subs, unique voucher number, line-to-entry foreign key, backfill', 'migration 2026_10_22_000001 ; finance:sync-columns (hourly)', 'done'],
                        ['2', 'finance_allocations table, filled from collections, payments, credit and debit notes', 'migration ...000002 ; finance:sync-allocations (hourly)', 'done'],
                        ['3', 'finance_ledgers view + read-only FinanceLedger model', 'migration ...000003', 'done'],
                        ['5', 'Trial Balance, P&L, Balance Sheet, Tax reports and Supplier Ledger read the view; old vs new parity command', 'finance:report-parity ; .env FINANCE_REPORTS_USE_LEDGER', 'done'],
                        ['5b', 'Customer / Supplier Statement and Aging read the ledger and allocations', 'finance:report-parity ; LedgerReports::settledAsOf', 'done'],
                        ['5c', 'Job Balance and Provisional Report read actual income / cost from the books (VAT excluded); Job Income and Job Report stay document based', 'finance:job-compare ; .env FINANCE_JOB_REPORTS_BASIS=documents to go back', 'done'],
                        ['6', 'PostingService: one place that posts, reversal entries instead of delete-and-reinsert, allocations written at posting time', '-', 'todo'],
                        ['7', 'Clean-up: paid_amount as a derived cache, NOT NULL / foreign keys, stop writing linked_*', '-', 'todo'],
                    ] as [$p, $w, $c, $s])
                        <tr><td><b>{{ $p }}</b></td><td>{{ __($w) }}</td><td><code>{{ $c }}</code></td><td><span class="chip {{ $s }}">{{ $s === 'done' ? __('Done') : __('Pending') }}</span></td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="lead-s mb-1">{{ __('Full design, SQL and reconciliation plan:') }} <code>docs/finance-ledger-design.md</code></p>

            {{-- 8. gaps --}}
            <h5 id="gaps">8. {{ __('Known gaps and decisions still open') }}</h5>
            <div class="row g-3">
                @foreach([
                    ['gap', 'Journal vouchers never post', 'Approving a JV changes its status only; no finance entry is created, so manual journals do not reach any report. Needs a poster (design phase 6).'],
                    ['gap', 'Account ids are fixed in code', 'AR 5, AP 18, Input VAT 7, Output VAT 20, bank charges 54... A company with another chart posts to the wrong accounts. Planned: company account roles.'],
                    ['gap', 'Edit = delete and re-insert', 'Editing an approved document removes its entry and writes a new one, so there is no audit trail. Planned: reversal entries.'],
                    ['done', 'Job Balance profit included VAT', 'Job Balance used to add invoice totals including VAT on both sides, so profit was overstated by the net VAT (job JOB-26-0010: 575 against 350 in the books) and counted draft invoices. Now fixed: it reads the books, VAT excluded. Set FINANCE_JOB_REPORTS_BASIS=documents to see the old figures.'],
                    ['done', 'Aging ignored credit notes', 'The old aging counted receipts only, so an invoice settled by a receipt plus a credit note still showed the credit-note part as overdue (invoice 32: 575). Fixed by reading allocations.'],
                    ['todo', 'Provisional expenses (rule applied, please confirm)', 'An expense posts to the ledger (EV) as well as being the provisional cost on the Provisional Report. Rule now used: an expense is the provisional estimate until the supplier invoice arrives, so job actual cost counts supplier invoices and debit notes only, never EV. Statutory reports (Trial Balance, P&L) still include EV. Confirm this is how you work.'],
                    ['todo', 'Posters do not fill the new columns', 'Until phase 6, the hourly sync fills entry date, partner, tax and source for new entries; the view falls back to reference_date meanwhile.'],
                    ['todo', 'base_paid_amount on invoices', 'Shows 575 on invoices fully paid at 2,300. Unexplained; separate from the ledger.'],
                ] as [$k, $t, $d])
                    <div class="col-md-6"><div class="fw-card"><h6><span class="chip {{ $k }}">{{ $k === 'gap' ? __('Gap') : ($k === 'done' ? __('Fixed') : __('Open')) }}</span> {{ __($t) }}</h6><div class="small">{{ __($d) }}</div></div></div>
                @endforeach
            </div>

            {{-- 9. live --}}
            <h5 id="live">9. {{ __('Live data (this company)') }}</h5>
            <div class="row g-3 mb-3">
                @foreach($counts as $table => $c)
                    <div class="col-6 col-md-4 col-xl-2"><div class="fw-card"><div class="small text-muted">{{ __($c['label']) }}</div><div class="fs-4 fw-bold">{{ $c['rows'] === null ? '-' : number_format($c['rows']) }}</div><code>{{ $table }}</code></div></div>
                @endforeach
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" style="max-width:640px">
                    <thead><tr><th>{{ __('Voucher type') }}</th><th class="text-end">{{ __('Entries') }}</th><th class="text-end">{{ __('Posted') }}</th><th class="text-end">{{ __('Total debit (base)') }}</th></tr></thead>
                    <tbody>
                    @forelse($byType as $r)
                        <tr><td><b>{{ $r->voucher_type }}</b></td><td class="text-end">{{ $r->entries }}</td><td class="text-end">{{ $r->posted }}</td><td class="text-end">{{ number_format($r->amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">{{ __('No entries yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="fw-code">php artisan finance:reconcile &nbsp;&nbsp;# {{ __('integrity checks, read-only') }}<br>php artisan finance:report-parity &nbsp;&nbsp;# {{ __('old vs new report numbers, read-only') }}<br>php artisan finance:sync-columns &nbsp;&nbsp;# {{ __('fill new columns for entries posted since the last run') }}<br>php artisan finance:sync-allocations &nbsp;&nbsp;# {{ __('create missing settlements') }}</div>
        </div>
    </main>
</x-app-layout>
