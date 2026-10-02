<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice — {{ $customerInvoice->row_no }}</title>
    <style>
        @font-face {
            font-family: 'Body';
            src: url('file://{{ public_path('fonts/LiberationSans-Regular.ttf') }}');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Body';
            src: url('file://{{ public_path('fonts/LiberationSans-Bold.ttf') }}');
            font-weight: bold;
        }
        @font-face {
            font-family: 'Body';
            src: url('file://{{ public_path('fonts/LiberationSans-Italic.ttf') }}');
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: 'Body';
            src: url('file://{{ public_path('fonts/LiberationSans-BoldItalic.ttf') }}');
            font-weight: bold;
            font-style: italic;
        }
        @font-face {
            font-family: 'NotoArabic';
            src: url('file://{{ public_path('fonts/NotoSansArabic-Regular.ttf') }}');
            font-weight: normal;
        }
        @font-face {
            font-family: 'NotoArabic';
            src: url('file://{{ public_path('fonts/NotoSansArabic-Bold.ttf') }}');
            font-weight: bold;
        }
        .ar, [class*="-ar"] { font-family: 'NotoArabic', 'Body', sans-serif; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Body', 'NotoArabic', sans-serif; color: #111827; font-size: 9pt; line-height: 1.4; background: #ffffff; }
        @page { margin: 16px; }

        /* Ensure the page wrapper takes full height for sticky footer effect */
        .page { width: 750px; margin: 0 auto; /*padding: 20px;*/ background: #fff; position: relative; min-height: 98vh; display: flex; flex-direction: column; justify-content: space-between; }
        .content-body { flex: 1; }

        .ar { direction: rtl; text-align: right; unicode-bidi: bidi-override; }
        .muted { color: #4b5563; }
        .dark-text { color: var(--accent); }
        .accent-color { color: #0d9488; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; word-wrap: break-word; }

        .watermark {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 90pt; font-weight: 900; text-transform: uppercase; color: rgba(150, 150, 150, 0.08);
            z-index: 999; white-space: nowrap; pointer-events: none;
        }

        /* Clean Modern Minimalist Line Items with enforced padding */
        table.items th { font-size: 8pt; font-weight: bold; padding: 8px 4px; border-bottom: 2px solid var(--accent); color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px; background: transparent; }

        /* Enforced cell padding for row height spacing */
        table.items td { font-size: 9pt; padding: 8px 4px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; background: transparent; }
    </style>
    @php $__colorCssTpl = ':root { --accent: __COLOR__; }'; @endphp
    <style id="dynamic-color-style">{!! str_replace('__COLOR__', $accentColor, $__colorCssTpl) !!}</style>
    <script id="color-css-template" type="application/json">{!! json_encode($__colorCssTpl) !!}</script>
</head>
<body>
<div class="page">
    <div class="content-body">
        @if($customerInvoice->status == 1)
            <div class="watermark">DRAFT</div>
        @elseif($customerInvoice->status == 4)
            <div class="watermark" style="color: rgba(220,38,38,0.08);">CANCELLED</div>
        @endif

        @php
            $docTitle = match (true) {
                $customerInvoice->status == 1 => 'DRAFT INVOICE',
                $customerInvoice->status == 4 => 'CANCELLED INVOICE',
                default => 'TAX INVOICE',
            };
            $docTitleAr = match (true) {
                $customerInvoice->status == 1 => 'فاتورة مسودة',
                $customerInvoice->status == 4 => 'فاتورة ملغاة',
                default => 'فاتورة ضريبية',
            };
        @endphp

        {{-- Top Header: Modern Side-by-Side Layout --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px solid #e5e7eb;margin-bottom: 10px">
            <tr>
                <td width="65%" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="216" valign="top">
                                @if($company->logo)
                                    <img src="{{ $company->logo }}" style="max-width: 200px; max-height: 60px; border-radius: 6px;">
                                @else
                                    <div style="width: 42px; height: 42px; background: var(--accent); border-radius: 6px; color: #fff; font-size: 20pt; font-weight: bold; text-align: center; line-height: 42px;">
                                        {{ strtoupper(substr($company->name ?? 'F', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td valign="top" style="padding-left: 14px;">
                                <div class="dark-text" style="font-size: 13pt; font-weight: bold;">{{ $company->name }}</div>
                                <div class="muted" style="font-size: 9pt; margin-top: 4px; line-height: 1.5;">
                                    @if($company->address){{ $company->address }}, @endif{{ $company->city }}<br>
                                    @if($company->vat_number)VAT NO: <strong class="dark-text">{{ $company->vat_number }}</strong>, <br>CR NO: <strong class="dark-text">{{ $company->cr_number }}</strong>@if($company->phone),@endif @endif
                                    @if($company->phone) Tel: {{ $company->phone }}@endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="35%" valign="top" align="right" style="text-align: right;">
                    <div style="font-size: 18pt; font-weight: 900; letter-spacing: 1px; color: var(--accent);">{{ $docTitle }}</div>
                    <div class="ar dark-text" style="font-size: 12pt; font-weight: bold; margin-top: 2px;">{{ $docTitleAr }}</div>
                    <div style="font-size: 10pt; margin-top: 8px;">
                        <span class="muted"><span class="ar">رقم الفاتورة</span> / Invoice No:</span>
                        <strong class="dark-text" style="font-size: 11pt;">{{ $customerInvoice->row_no ?: '-' }}</strong>
                    </div>
                    <div style="margin-top:5px"></div>
                </td>
            </tr>
        </table>

        {{-- Meta & Bill To Info Grid --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" >
            <tr>
                <td width="50%" valign="top" style="padding-right: 20px;">
                    <div style="font-size: 9pt; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;">
                        BILLED TO <span class="ar" style="font-weight: normal; direction: ltr; display: inline-block;">| الفاتورة إلى</span>
                    </div>
                    <div style="font-weight: bold; font-size: 10pt; margin-top: 8px;" class="dark-text">{{ $customerInvoice->customer?->name_en }}</div>
                    @if($customerInvoice->customer?->name_ar)<div class="dark-text" style="font-size: 9pt; margin-top: 2px;">{{ $customerInvoice->customer->name_ar }}</div>@endif
                    <div class="muted" style="font-size: 9pt; margin-top: 6px; line-height: 1.5;">
                        {{ $customerInvoice->customer?->address1_en }}@if($customerInvoice->customer?->city_en), {{ $customerInvoice->customer->city_en }}@endif
                    </div>
                    <table cellpadding="3" cellspacing="0" border="0" style="font-size: 8pt; margin-top: 6px;">
                        @if($customerInvoice->customer?->vat_number)
                            <tr><td class="muted" style="width: 35%;">VAT Number</td><td  class="dark-text">: {{ $customerInvoice->customer->vat_number }}</td></tr>
                            <tr><td class="muted" style="width: 35%;">CR Number</td><td  class="dark-text">: {{ $customerInvoice->customer->cr_number }}</td></tr>
                        @endif
                        <tr data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">
                            @if($customerInvoice->customer?->phone)
                                <td class="muted">Mobile No.</td><td class="dark-text">: {{ $customerInvoice->customer->phone }}</td>
                            @endif
                        </tr>
                    </table>
                </td>
                <td width="50%" valign="top" style="padding-left: 20px; border-left: 1px solid #f3f4f6;">
                    <div style="font-size: 9pt; font-weight: bold;text-align: end; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;">
                        INVOICE DETAILS <span class="ar" style="font-weight: normal; direction: ltr; display: inline-block;">| تفاصيل الفاتورة</span>
                    </div>
                    <table cellpadding="5" cellspacing="0" border="0" width="100%" style="font-size: 9pt; margin-top: 8px;">
                        <tr>
                            <td class="muted" style="width: 45%;padding-bottom:5px">Invoice Date | <span class="ar" style="font-size: 7.5pt;"> التاريخ</span></td>
                            <td style="font-weight: bold;" align="right" class="dark-text">{{ $customerInvoice->invoice_date ? \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d/m/Y') : '-' }}<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('H:i') }}</span></td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding-bottom:5px">Due Date | <span class="ar" style="font-size: 7.5pt;"> رقم الطلب</span></td>
                            <td style="font-weight: bold;" align="right" class="dark-text">{{ $customerInvoice->due_date ? \Carbon\Carbon::parse($customerInvoice->due_date)->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="padding-bottom:5px">Currency | <span class="ar" style="font-size: 7.5pt;"> العملة</span></td>
                            <td style="font-weight: bold;" align="right" class="dark-text">{{ $customerInvoice->currency }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Line Items Table --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="items" style="margin-top: 24px;">
            <thead>
            <tr>
                <th align="left" style="width: 4%;">#</th>
                <th align="left" style="width: 25%;">DESCRIPTION<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">وصف المنتج/الخدمة</span></th>
                <th align="left" style="width: 8%;{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}"
                    data-toggle="hsn_sac">HSN/SAC<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">الرمز</span></th>
                <th align="left" style="width: 8%;{{ ($settings->unit ?? true) ? '' : 'display:none' }}"
                    data-toggle="unit">UNIT<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">الوحدة</span></th>
                <th align="right" style="width: 7%;">QTY<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">الكمية</span></th>
                <th align="right" style="width: 12%;{{ ($settings->rate ?? true) ? '' : 'display:none' }}"
                    data-toggle="rate">UNIT PRICE<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">سعر الوحدة</span></th>
                <th align="right" style="width: 8%;{{ ($settings->discount ?? false) ? '' : 'display:none' }}"
                    data-toggle="discount">DISC<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">الخصم</span></th>
                <th align="right" style="width: 13%;">BEFORE VAT<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">قبل الضريبة</span></th>
                <th align="right" style="width: 7%;">VAT<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">النسبة</span></th>
                <th align="right" style="width: 11%;">VAT AMT<br><span class="ar" style="font-weight: normal; font-size: 7.5pt; direction: ltr; display: inline-block;">قيمة الضريبة</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($customerInvoice->customerInvoiceSubs as $index => $item)
                <tr>
                    <td class="muted">{{ $index + 1 }}</td>
                    <td>
                        <div class="dark-text">{{ $item->description }}</div>
                        @if($item->comment)<div class="muted" data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? 'font-size: 7.5pt; margin-top: 4px; line-height: 1.3;' : 'display:none' }}">{{ $item->comment }}</div>@endif
                    </td>
                    <td data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">-</td>
                    <td data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">{{ $item->unit ?: '-' }}</td>
                    <td align="right">{{ amountFormat($item->quantity) }}</td>
                    <td align="right" data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">{{ amountFormat($item->unit_price) }}</td>
                    <td align="right" data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">{{ amountFormat($customerInvoice->discount_total ?? 0) }}</td>
                    <td align="right">{{ amountFormat($item->total) }}</td>
                    <td align="right">{{ rtrim(rtrim(number_format((float) $item->tax_percent, 2), '0'), '.') }}%</td>
                    <td align="right">{{ amountFormat($item->tax_amount) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

            {{-- Totals and QR Code Section --}}
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
                <tr>
                    {{-- QR Code (Left) --}}
                    <td width="60%" valign="top">
                        @include('modules.print.partials.zatca-qr')
                    </td>

                    {{-- Financial Totals (Right) --}}
                    <td width="40%" valign="top" align="right">
                        <table cellpadding="4" cellspacing="0" border="0" width="100%" style="font-size: 9pt;">
                            <tr>
                                <td class="muted" align="right">Total Before VAT | <span class="ar" style="font-size: 7.5pt;">قبل الضريبة</span></td>
                                <td align="right" style="width: 45%; font-weight: bold;" class="dark-text">{{ amountFormat($customerInvoice->sub_total) }}</td>
                            </tr>
                            <tr>
                                <td class="muted" align="right" style="padding:5px 0 5px 0">Total VAT ({{ rtrim(rtrim(number_format((float) ($customerInvoice->sub_total > 0 ? $customerInvoice->tax_total / $customerInvoice->sub_total * 100 : 0), 2), '0'), '.') }}%) | <span class="ar" style="font-size: 7.5pt;">الضريبة</span></td>
                                <td align="right" style="font-weight: bold;" class="dark-text">{{ amountFormat($customerInvoice->tax_total) }}</td>
                            </tr>
                            <tr data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? '' : 'display:none' }}">
                                <td class="muted" align="right">Outstanding | <span class="ar" style="font-size: 7.5pt;">الرصيد المستحق</span></td>
                                <td align="right" style="font-weight: bold;" class="dark-text">{{ amountFormat($customerBalance) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding: 0;"><div style="border-top: 2px solid var(--accent); margin: 6px 0;"></div></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; font-size: 9.5pt;" class="dark-text" align="right">GRAND TOTAL <span class="ar" style="font-size: 8pt; display: block;">المجموع الكلي</span></td>
                                <td align="right" style="font-weight: 900; font-size: 13pt;" class="dark-text">{{ amountFormat($customerInvoice->grand_total) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        {{-- Amount in words --}}
        <div style="margin-top: 15px; border-top: 1px dashed #e5e7eb; border-bottom: 1px dashed #e5e7eb; padding: 10px 0;">
            <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px;">Amount in Words <span class="ar" style="direction: ltr; display: inline-block;">| المبلغ كتابة</span></div>
            <div class="ar" style="font-size: 9pt; margin-top: 3px; font-weight: bold;">{{ convert(round((float) $customerInvoice->grand_total, 2), $customerInvoice->currency) }}</div>
            <div style="font-size: 9pt; margin-top: 2px;" class="dark-text">{{ amountInWords(round((float) $customerInvoice->grand_total, 2)) }}</div>
        </div>

        {{-- Bank Details & Notes Section (Full Width Layout split cleanly) --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 15px;">
            <tr>
                <td width="50%" valign="top" style="padding-right: 15px;">
                    <div style="font-weight: bold; font-size: 9pt; color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px;">Bank Details <span class="ar" style="font-size: 8pt; direction: ltr; display: inline-block;">| بيانات التحويل</span></div>
                    @if($bank)
                        <table cellpadding="3" cellspacing="0" border="0" style="font-size: 7.5pt; margin-top: 6px;">
                            <tr><td class="muted" style="width: 35%;">Bank Name</td><td style="font-weight: bold;" class="dark-text">: {{ $bank->name }}</td></tr>
                            <tr><td class="muted">Account Name</td><td class="dark-text">: {{ $bank->account_name ?: $company->name }}</td></tr>
                            <tr><td class="muted">Account No.</td><td class="dark-text">: {{ $bank->account_no }}</td></tr>
                            <tr><td class="muted">IBAN No.</td><td style="font-weight: bold;" class="dark-text">: {{ $bank->iban }}</td></tr>
                            @if($bank->swift)<tr><td class="muted">SWIFT Code</td><td class="dark-text">: {{ $bank->swift }}</td></tr>@endif
                        </table>
                    @else
                        <div class="muted" style="font-size: 7.5pt; margin-top: 6px;">No bank account on file.</div>
                    @endif
                </td>
                <td width="50%" valign="top" style="padding-left: 15px; border-left: 1px solid #f3f4f6;">
                    <div style="font-weight: bold; font-size: 9pt; color: var(--accent); text-transform: uppercase; letter-spacing: 0.5px;">Notes &amp; Terms <span class="ar" style="font-size: 8pt; direction: ltr; display: inline-block;">| ملاحظات</span></div>
                    <div class="muted" style="font-size: 7.5pt; margin-top: 6px; line-height: 1.6;">
                        @if($customerInvoice->notes)
                            {!! nl2br(e($customerInvoice->notes)) !!}
                            @if($customerInvoice->terms)<div style="margin-top: 5px;">{{ $customerInvoice->terms }}</div>@endif
                        @else
                            &bull; Payment is due within 30 days from invoice date.<br>
                        &bull; Please retain this document for tax and accounting records.<br>
                        &bull; This is an electronically generated document.
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Sign-off Layout (Pushed to bottom using flexbox parent) --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 25px; border-top: 1px solid #e5e7eb; padding-top: 15px;">
        <tr>
            <td width="55%" valign="bottom" class="muted" style="font-size: 6.5pt; line-height: 1.4;">
                THANK YOU FOR YOUR BUSINESS! | <span class="ar" style="direction: ltr; display: inline-block;"> شكراً لتعاملكم معنا!</span>
            </td>
            <td width="45%" valign="bottom" align="right" style="text-align: right;font-size: 7.5pt;">
                @if($company->email)Email: {{ $company->email }} &nbsp;|&nbsp; @endif
                @if($company->phone)Tel: {{ $company->phone }}@endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
