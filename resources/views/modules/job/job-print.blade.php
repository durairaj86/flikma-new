{{-- Job print (the quotation print's FastFatoora layout): Customer + Job Details, then titled sections of label / value pairs (routing,
     cargo, customs), containers, packages, documents, remarks + terms, signatures. Data from JobController::printData(). --}}
@extends('modules.common.print-doc.layout', [
    'pageTitle' => __('Job') . ($job->row_no ? ' — ' . $job->row_no : ''),
    'docTitle' => ['JOB', 'مهمة'],
    'refLabelAr' => 'رقم المهمة',
    'refNo' => $job->row_no ?: '-',
    'pill' => $statusLabel,
    'toLabel' => ['Customer', 'العميل'],
    'detailsLabel' => ['Job Details', 'تفاصيل المهمة'],
    'details' => $jobDetails,
    'footerLine' => ['THANK YOU FOR YOUR BUSINESS!', 'شكراً لتعاملكم معنا!'],
])

@section('body')
    {{-- routing / cargo / customs: two label + value pairs per row --}}
    @foreach($sections as [$head, $pairs])
        <div class="section-head" style="margin-top: 22px; page-break-after: avoid;">{{ $head[0] }} | <span class="ar">{{ $head[1] }}</span></div>
        <table class="pairs" style="page-break-inside: avoid;">
            @foreach(array_chunk($pairs, 2) as $row)
                <tr>
                    @foreach($row as [$en, $ar, $value])
                        <td class="k">{{ $en }}<div class="ar" style="font-size: 7.5pt; text-align: left;">{{ $ar }}</div></td>
                        <td class="v">{{ $value }}</td>
                    @endforeach
                    @if(count($row) === 1)<td class="k"></td><td class="v"></td>@endif
                </tr>
            @endforeach
        </table>
    @endforeach

    @if($job->containers->count())
        <table class="items" style="margin-top: 26px;">
            <thead>
            <tr>
                <th align="left">#</th>
                <th align="left">Container No <span class="ar">رقم الحاوية</span></th>
                <th align="left">Size <span class="ar">الحجم</span></th>
                <th align="left">Type <span class="ar">النوع</span></th>
                <th align="left">Seal No <span class="ar">رقم الختم</span></th>
                <th align="right">Gross Wt <span class="ar">الوزن القائم</span></th>
                <th align="right">Net Wt <span class="ar">الوزن الصافي</span></th>
                <th align="right">CBM <span class="ar">م³</span></th>
                <th align="left">Hazardous <span class="ar">خطرة</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($job->containers as $i => $c)
                <tr>
                    <td class="muted">{{ $i + 1 }}</td>
                    <td>{{ $c->container_number ?: ($c->container_no ?: '-') }}</td>
                    <td>{{ $c->container_size ?: '-' }}</td>
                    <td>{{ ucfirst((string) $c->container_type) ?: '-' }}</td>
                    <td>{{ $c->seal_number ?: ($c->seal_no ?: '-') }}</td>
                    <td class="num">{{ (float) $c->gross_weight ? number_format((float) $c->gross_weight, 2) : '-' }}</td>
                    <td class="num">{{ (float) $c->net_weight ? number_format((float) $c->net_weight, 2) : '-' }}</td>
                    <td class="num">{{ (float) $c->volume ? number_format((float) $c->volume, 3) : '-' }}</td>
                    <td>{{ $c->hazardous == 'Yes' ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if($job->packages->count())
        <table class="items" style="margin-top: 26px;">
            <thead>
            <tr>
                <th align="left">#</th>
                <th align="left">Commodity <span class="ar">السلعة</span></th>
                <th align="left">Description <span class="ar">الوصف</span></th>
                <th align="left">HS Code <span class="ar">الرمز</span></th>
                <th align="right">Qty <span class="ar">الكمية</span></th>
                <th align="right">L × W × H <span class="ar">الأبعاد</span></th>
                <th align="right">Weight <span class="ar">الوزن</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($job->packages as $i => $p)
                <tr>
                    <td class="muted">{{ $i + 1 }}</td>
                    <td>{{ commodityType()[$p->commodity_type] ?? ($p->commodity_type ?: '-') }}</td>
                    <td>{{ $p->description_goods ?: '-' }}</td>
                    <td>{{ $p->hs_code ?: '-' }}</td>
                    <td class="num">{{ $p->quantity ?: '-' }}</td>
                    <td class="num">{{ collect([$p->length, $p->width, $p->height])->filter(fn($v) => (float) $v)->implode(' × ') ?: '-' }}</td>
                    <td class="num">{{ (float) $p->package_weight ? number_format((float) $p->package_weight, 2) : '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if($job->documents->count())
        <div class="section-head" style="margin-top: 22px;">Documents | <span class="ar">المستندات</span></div>
        <table class="pairs">
            @foreach($job->documents as $doc)
                <tr><td class="k" style="width: 50%;">{{ $doc->document_type }}</td><td class="v" style="width: 50%; text-align: right; padding-right: 0;">{{ showDate($doc->posted_date) ?: '-' }}</td></tr>
            @endforeach
        </table>
    @endif

    {{-- remarks | terms --}}
    <div class="rule" style="margin-top: 22px;"></div>
    <table style="margin-top: 8px; page-break-inside: avoid;">
        <tr>
            <td width="52%" valign="top" style="padding-right: 22px; border-right: 1px solid #e5e7eb;">
                <div class="label" style="color: #111827; font-size: 9pt;">Remarks | <span class="ar">ملاحظات</span></div>
                <div style="font-size: 8pt; color: #374151; margin-top: 8px; white-space: pre-line;">{{ trim(strip_tags((string) $job->remarks)) ?: 'No remarks for this job.' }}</div>
            </td>
            <td width="48%" valign="top" style="padding-left: 22px;">
                <div class="label" style="color: #111827; font-size: 9pt;">Terms &amp; Conditions | <span class="ar">الشروط والأحكام</span></div>
                <div style="font-size: 8pt; color: #374151; margin-top: 8px; white-space: pre-line;">{{ trim(strip_tags((string) $job->terms)) ?: 'No terms for this job.' }}</div>
            </td>
        </tr>
    </table>

    @include('modules.common.print-doc.signatures', ['left' => 'Authorized Signature', 'right' => 'Customer Signature'])
@endsection
