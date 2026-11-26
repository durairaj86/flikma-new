{{-- Shared document print (FastFatoora layout), used by the quotation, supplier invoice and job prints: header with logo / company /
     title + number, a "To" block next to a details block, the document's own body, and a footer line. A4 portrait; printed from a
     hidden frame and turned into the PDF by the page's downloadPDF().
     Variables: $company, $accentColor, $watermark, $pageTitle, $docTitle [en, ar], $refLabelAr, $refNo, $pill (optional),
     $toLabel [en, ar], $partyName, $partyCompany, $partyAddress, $partyPhone, $partyVat, $detailsLabel [en, ar],
     $details [[en, ar, value], ...], $footerLine [en, ar]. --}}
@php
    $companyAddress = collect([$company->address ?? null, $company->city ?? null])->filter()->implode(', ');
@endphp
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>
    <style>
        :root { --accent: {{ $accentColor }}; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { background: #fff; }
        body { font-family: Arial, Helvetica, 'Noto Sans Arabic', Tahoma, sans-serif; color: #1f2937; font-size: 9pt; line-height: 1.35; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { size: A4 portrait; margin: 12mm 10mm; }
        .ar { font-family: 'Noto Sans Arabic', 'Segoe UI', Tahoma, Arial, sans-serif; direction: rtl; unicode-bidi: isolate; }
        .paper { width: 780px; margin: 0 auto; padding: 14px 4px; position: relative; min-height: 1060px; display: flex; flex-direction: column; }
        /* A4 minus the page margins: the footer sits at the bottom of the one page, never on a second one */
        @media print { .paper { min-height: 1000px; padding: 0 4px; } }
        .muted { color: #6b7280; }
        .accent { color: var(--accent); }
        table { border-collapse: collapse; width: 100%; }
        .label { font-size: 8pt; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; color: #6b7280; }
        .label .ar { font-weight: normal; letter-spacing: 0; text-transform: none; }
        .rule { border-top: 1px solid #e5e7eb; }

        /* header */
        .company-name { font-size: 13.5pt; font-weight: bold; color: #111827; }
        .company-line { font-size: 9pt; color: #4b5563; margin-top: 4px; line-height: 1.5; }
        .doc-title { font-size: 21pt; font-weight: bold; letter-spacing: 1px; color: var(--accent); line-height: 1; }
        .doc-title-ar { font-size: 12.5pt; font-weight: bold; color: #111827; margin-top: 8px; }
        .pill { display: inline-block; border: 1.2px solid var(--accent); color: var(--accent); border-radius: 9px; padding: 2px 9px; font-size: 8pt; margin-top: 8px; }

        /* to / details */
        .block-head { padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }
        .detail td { padding: 3px 0; font-size: 9pt; }
        .detail td.k { color: #4b5563; }
        .detail td.v { font-weight: bold; text-align: right; color: #111827; }

        /* lines */
        table.items th { font-size: 7.5pt; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; color: #111827; padding: 0 6px 7px; border-bottom: 2px solid #111827; white-space: nowrap; vertical-align: bottom; }
        table.items th .ar { display: block; font-size: 7.5pt; font-weight: normal; text-transform: none; letter-spacing: 0; color: #374151; margin-top: 2px; }
        /* html2canvas (the PDF) ignores the align attribute: say it in CSS too */
        table.items th[align="left"] { text-align: left; }
        table.items th[align="right"] { text-align: right; }
        table.items th[align="left"] .ar { text-align: left; direction: ltr; }
        table.items th[align="right"] .ar { text-align: right; }
        table.items td { font-size: 9pt; padding: 10px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; color: #1f2937; }
        table.items td.num { text-align: right; white-space: nowrap; }

        /* totals */
        .totals td { padding: 3px 0; font-size: 9pt; }
        .totals td.v { text-align: right; font-weight: bold; color: #111827; }
        .grand td { padding-top: 10px; }

        /* a titled section of label / value pairs, two pairs per row (job print) */
        .section-head { font-size: 9pt; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; color: #111827; padding-bottom: 6px; border-bottom: 2px solid #111827; }
        .section-head .ar { font-weight: normal; letter-spacing: 0; text-transform: none; color: #374151; }
        table.pairs td { font-size: 9pt; padding: 5px 0; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.pairs td.k { color: #4b5563; width: 19%; padding-right: 8px; }
        table.pairs td.v { font-weight: bold; color: #111827; width: 31%; padding-right: 18px; }

        .bank td { font-size: 8pt; padding: 1px 0; color: #374151; }
        .terms li { font-size: 8pt; color: #374151; margin: 0 0 5px 0; list-style: none; }
        .sign { border-top: 1px solid #6b7280; padding-top: 6px; font-size: 8pt; color: #4b5563; }
        .foot { border-top: 1px solid #d1d5db; padding-top: 5px; font-size: 7pt; color: #4b5563; }

        .watermark { position: absolute; top: 34%; left: 50%; transform: translate(-50%, -50%) rotate(-38deg); font-size: 96pt; font-weight: bold;
            color: rgba(239, 68, 68, .09); letter-spacing: 6px; white-space: nowrap; pointer-events: none; z-index: 0; }
        .content { position: relative; z-index: 1; }
    </style>
</head>
<body>
<div class="paper">
    @if($watermark)<div class="watermark">{{ $watermark }}</div>@endif
    <div class="content" style="flex: 1;">
        {{-- header: logo | company | title --}}
        <table>
            <tr>
                <td width="29%" valign="top">
                    @if($company->logo)
                        <img src="{{ companyLogo() }}" alt="" style="max-width: 190px; max-height: 72px;">
                    @else
                        <div style="width: 54px; height: 54px; background: var(--accent); border-radius: 8px; color: #fff; font-size: 24pt; font-weight: bold; text-align: center; line-height: 54px;">{{ strtoupper(mb_substr($company->name ?? 'F', 0, 1)) }}</div>
                    @endif
                </td>
                <td width="38%" valign="top">
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-line">
                        @if($companyAddress){{ $companyAddress }}<br>@endif
                        @if($company->vat_number)VAT NO: <b style="color:#111827">{{ $company->vat_number }}</b>@endif
                        @if($company->vat_number && $company->phone) &nbsp;|&nbsp; @endif
                        @if($company->phone)Tel: {{ $company->phone }}@endif
                    </div>
                </td>
                <td width="33%" valign="top" align="right">
                    {{-- a long title ("SUPPLIER INVOICE") is set smaller so it stays on one line --}}
                    <div class="doc-title" @if(mb_strlen($docTitle[0]) > 10) style="font-size: 16pt; white-space: nowrap;" @endif>{{ $docTitle[0] }}</div>
                    <div class="doc-title-ar ar" style="text-align: right;">{{ $docTitle[1] }}</div>
                    <div style="margin-top: 8px; font-size: 10pt; color: #4b5563;"><span class="ar">{{ $refLabelAr }}</span> / Ref No: <b style="color: var(--accent); font-size: 11pt;">{{ $refNo }}</b></div>
                    @if(!empty($pill))<div class="pill">{{ $pill }}</div>@endif
                </td>
            </tr>
        </table>
        <div class="rule" style="margin-top: 8px;"></div>

        {{-- TO | DETAILS --}}
        <table style="margin-top: 10px;">
            <tr>
                <td width="49%" valign="top" style="padding-right: 22px; border-right: 1px solid #e5e7eb;">
                    <div class="label block-head">{{ $toLabel[0] }} | <span class="ar">{{ $toLabel[1] }}</span></div>
                    <div style="font-size: 10.5pt; font-weight: bold; color: #111827; margin-top: 9px;">{{ $partyName ?: '-' }}</div>
                    @if($partyCompany)<div style="font-size: 9.5pt; margin-top: 3px; text-align: left;" @if(preg_match('/\p{Arabic}/u', $partyCompany)) class="ar" @endif>{{ $partyCompany }}</div>@endif
                    @if($partyAddress)<div class="muted" style="font-size: 9.5pt; margin-top: 9px;">{{ $partyAddress }}</div>@endif
                    <table style="margin-top: 9px;">
                        @if($partyPhone)
                            <tr><td width="49%" class="muted" style="font-size: 8pt;">Mobile No.</td><td style="font-size: 8pt;">: <span dir="ltr">{{ $partyPhone }}</span></td></tr>
                        @endif
                        @if($partyVat)
                            <tr><td class="muted" style="font-size: 8pt; padding-top: 3px;">VAT No.</td><td style="font-size: 8pt; padding-top: 3px;">: {{ $partyVat }}</td></tr>
                        @endif
                    </table>
                </td>
                <td width="51%" valign="top" style="padding-left: 22px;">
                    <div class="label block-head" style="text-align: right;">{{ $detailsLabel[0] }} | <span class="ar">{{ $detailsLabel[1] }}</span></div>
                    <table class="detail" style="margin-top: 7px;">
                        @foreach($details as [$en, $ar, $value])
                            <tr><td class="k">{{ $en }} | <span class="ar">{{ $ar }}</span></td><td class="v">{{ filled($value) ? $value : '-' }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        @yield('body')
    </div>

    {{-- footer --}}
    <table class="foot content" style="margin-top: 18px;">
        <tr>
            <td valign="top">{{ $footerLine[0] }} | <span class="ar">{{ $footerLine[1] }}</span></td>
            <td valign="top" align="right" style="font-size: 8pt; color: #111827; white-space: nowrap;">
                @if($company->email)Email: {{ $company->email }}@endif
                @if($company->email && $company->phone) &nbsp;|&nbsp; @endif
                @if($company->phone)Tel: {{ $company->phone }}@endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
