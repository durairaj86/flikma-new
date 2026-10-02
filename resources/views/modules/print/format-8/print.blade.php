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
            font-family: 'NotoArabic';
            src: url('file://{{ public_path('fonts/NotoSansArabic-Regular.ttf') }}');
            font-weight: normal;
        }
        @font-face {
            font-family: 'NotoArabic';
            src: url('file://{{ public_path('fonts/NotoSansArabic-Bold.ttf') }}');
            font-weight: bold;
        }
        .ar, [class*="-ar"] { font-family: 'NotoArabic', 'Body', Arial, sans-serif !important; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Body', 'NotoArabic', Arial, sans-serif; color: #1f2937; font-size: 9.5pt; line-height: 1.2; background: #ffffff; }
        @page { margin: 0; }
        .paper { width: 100%; min-height: 100vh; padding: 10px 24px; background: #fff; position: relative; display: flex; flex-direction: column; justify-content: space-between; }
        .ar { direction: rtl; text-align: right; unicode-bidi: embed; }
        .muted { color: #6b7280; }
        .navy { color: var(--accent); }
        .gold { color: #b8860b; }
        table { border-collapse: collapse; width: 100%; }

        .watermark {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 90pt; font-weight: 900; text-transform: uppercase; color: rgba(150, 150, 150, 0.12);
            z-index: 999; white-space: nowrap; pointer-events: none;
        }

        table.items th { font-size: 8pt; font-weight: bold; padding: 6px 10px; background: var(--accent); color: #ffffff; }
        table.items td { font-size: 8.5pt; padding: 5px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        table.items tr:nth-child(even) td { background: #f7f9fc; }
    </style>
    @php $__colorCssTpl = ':root { --accent: __COLOR__; }'; @endphp
    <style id="dynamic-color-style">{!! str_replace('__COLOR__', $accentColor, $__colorCssTpl) !!}</style>
    <script id="color-css-template" type="application/json">{!! json_encode($__colorCssTpl) !!}</script>
</head>
<body>
<div class="paper">
    <div>
        @if($customerInvoice->status == 1)
            <div class="watermark">DRAFT</div>
        @elseif($customerInvoice->status == 4)
            <div class="watermark" style="color: rgba(200,30,30,0.12);">CANCELLED</div>
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

        {{-- Header: logo | title | invoice no. box --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="34%" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="42" valign="middle">
                                @if($company->logo)
                                    <img src="{{ $company->logo }}" style="max-width: 38px; max-height: 38px; border-radius: 8px;">
                                @else
                                    <div style="width: 38px; height: 38px; background: var(--accent); border-radius: 8px; color: #fff; font-size: 18pt; font-weight: bold; text-align: center; line-height: 38px;">
                                        {{ strtoupper(substr($company->name ?? 'F', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td valign="top" style="padding-left: 6px;">
                                <div class="navy" style="font-size: 12.5pt; font-weight: bold; line-height: 1.25;">{{ $company->name }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="33%" valign="top" align="center" style="padding-top: 6px;">
                    <div class="ar navy" style="font-size: 16pt; font-weight: bold; line-height: 1.2;">{{ $docTitleAr }}</div>
                    <div class="navy" style="font-size: 15pt; font-weight: bold; letter-spacing: 1px;">{{ $docTitle }}</div>
                </td>
                <td width="33%" valign="top" align="right">
                    <div style="background: #f4b400; color: var(--accent); border-radius: 6px; padding: 8px 14px; display: inline-block; text-align: center; min-width: 190px;">
                        <div style="font-size: 7pt; font-weight: bold;"><span class="ar">رقم الفاتورة</span> | INVOICE NO.</div>
                        <div style="font-size: 12pt; font-weight: bold; margin-top: 3px;">{{ $customerInvoice->row_no ?: '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Contact info row --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px; font-size: 8pt;">
            <tr>
                <td width="40%" valign="top">
                    <div class="ar" style="font-size: 8pt;">{{ $company->country ?: 'Kingdom of Saudi Arabia' }}</div>
                    <div class="muted" style="font-size: 8pt;">
                        @if($company->address){{ $company->address }}, @endif{{ $company->city }}
                    </div>
                </td>
                <td width="20%" valign="top" data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">@if($company->phone)Tel: {{ $company->phone }}@endif</td>
                <td width="25%" valign="top">@if($company->email)Email: {{ $company->email }}@endif</td>
                <td width="15%" valign="top"></td>
            </tr>
        </table>

        <div style="border-top: 1px solid #e5e7eb; margin-top: 10px;"></div>

        {{-- Bill To / Meta --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px;">
            <tr>
                <td width="48%" valign="top">
                    <div style="background: var(--accent); color: #fff; padding: 8px 14px; border-radius: 6px 6px 0 0; font-size: 9pt; font-weight: bold;">
                        BILL TO <span class="ar">| الفاتورة إلى</span>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 6px 6px; padding: 14px;">
                        <div style="font-weight: bold; font-size: 9.5pt;">{{ $customerInvoice->customer?->name_en }}</div>
                        @if($customerInvoice->customer?->name_ar)<div class="ar" style="font-size: 9pt; margin-top: 2px;">{{ $customerInvoice->customer->name_ar }}</div>@endif
                        <div class="muted" style="font-size: 8.5pt; margin-top: 6px;">
                            {{ $customerInvoice->customer?->address1_en }}@if($customerInvoice->customer?->city_en), {{ $customerInvoice->customer->city_en }}@endif
                        </div>
                        <table cellpadding="3" cellspacing="0" border="0" style="font-size: 8pt; margin-top: 6px;">
                            @if($customerInvoice->customer?->vat_number)
                                <tr><td class="muted" style="width: 35%;">VAT Number</td><td>:</td><td>{{ $customerInvoice->customer->vat_number }}</td></tr>
                            @endif
                            <tr data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">
                                @if($customerInvoice->customer?->phone)
                                    <td class="muted">Mobile No.</td><td>:</td><td>{{ $customerInvoice->customer->phone }}</td>
                                @endif
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="4%"></td>
                <td width="48%" valign="top">
                    <table cellpadding="4" cellspacing="0" border="0" width="100%" style="font-size: 8pt; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <tr>
                            <td class="muted" style="width: 58%; white-space: nowrap;">INVOICE DATE <span class="ar" style="font-size: 7pt;">| تاريخ الفاتورة</span></td>
                            <td style="width: 4%;">:</td>
                            <td style="font-weight: bold;">{{ $customerInvoice->invoice_date ? \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d/m/Y') : '-' }}<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('H:i') }}</span></td>
                        </tr>
                        <tr>
                            <td class="muted" style="white-space: nowrap;">DUE DATE <span class="ar" style="font-size: 7pt;">| تاريخ الاستحقاق</span></td>
                            <td>:</td>
                            <td style="font-weight: bold;">{{ $customerInvoice->due_date ? \Carbon\Carbon::parse($customerInvoice->due_date)->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="white-space: nowrap;">PLACE OF SUPPLY <span class="ar" style="font-size: 7pt;">| مكان التوريد</span></td>
                            <td>:</td>
                            <td style="font-weight: bold;">{{ $company->city ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="white-space: nowrap;">CURRENCY <span class="ar" style="font-size: 7pt;">| العملة</span></td>
                            <td>:</td>
                            <td style="font-weight: bold;">{{ $customerInvoice->currency }}</td>
                        </tr>
                        <tr>
                            <td class="muted" style="white-space: nowrap;">PAYMENT STATUS <span class="ar" style="font-size: 7pt;">| حالة السداد</span></td>
                            <td>:</td>
                            <td>
                                @if((float) $customerBalance <= 0)
                                    <strong style="color: #15803d;">PAID</strong>
                                @else
                                    <strong style="color: #b91c1c;">UNPAID</strong>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Line Items --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="items" style="margin-top: 10px;">
            <thead>
            <tr>
                <th align="left" style="width: 5%;">#</th>
                <th align="left" style="width: 33%;">ITEM DESCRIPTION<br><span class="ar" style="font-weight: normal;">وصف الصنف</span></th>
                <th align="right" style="width: 12%;">QTY<br><span class="ar" style="font-weight: normal;">الكمية</span></th>
                <th align="left" style="width: 12%;{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}"
                    data-toggle="hsn_sac">HSN/SAC<br><span class="ar" style="font-weight: normal;">الرمز</span></th>
                <th align="left" style="width: 12%;{{ ($settings->unit ?? true) ? '' : 'display:none' }}"
                    data-toggle="unit">UNIT<br><span class="ar" style="font-weight: normal;">الوحدة</span></th>
                <th align="right" style="width: 18%;{{ ($settings->rate ?? true) ? '' : 'display:none' }}"
                    data-toggle="rate">UNIT PRICE ({{ $customerInvoice->currency }})<br><span class="ar" style="font-weight: normal;">سعر الوحدة</span></th>
                <th align="right" style="width: 9%;{{ ($settings->discount ?? false) ? '' : 'display:none' }}"
                    data-toggle="discount">DISC<br><span class="ar" style="font-weight: normal;">الخصم</span></th>
                <th align="right" style="width: 20%;">AMOUNT BEFORE TAX ({{ $customerInvoice->currency }})<br><span class="ar" style="font-weight: normal;">الإجمالي قبل الضريبة</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($customerInvoice->customerInvoiceSubs as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $item->description }}</div>
                        @if($item->comment)<div class="muted" data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? 'font-size: 8pt; margin-top: 2px;' : 'display:none' }}">{{ $item->comment }}</div>@endif
                    </td>
                    <td align="right">{{ amountFormat($item->quantity) }}</td>
                    <td data-toggle="hsn_sac" style="{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}">-</td>
                    <td data-toggle="unit" style="{{ ($settings->unit ?? true) ? '' : 'display:none' }}">{{ $item->unit ?: '-' }}</td>
                    <td align="right" data-toggle="rate" style="{{ ($settings->rate ?? true) ? '' : 'display:none' }}">{{ amountFormat($item->unit_price) }}</td>
                    <td align="right" data-toggle="discount" style="{{ ($settings->discount ?? false) ? '' : 'display:none' }}">{{ amountFormat($customerInvoice->discount_total ?? 0) }}</td>
                    <td align="right" style="font-weight: bold;">{{ amountFormat($item->total) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- Total quantity pill --}}
        <table cellpadding="0" cellspacing="0" border="0" style="margin-top: 10px;">
            <tr>
                <td style="background: #f7f9fc; border: 1px solid #e5e7eb; border-radius: 20px; padding: 6px 16px; font-size: 8.5pt;">
                    <span class="ar muted">إجمالي الكمية</span> : <strong>{{ $customerInvoice->customerInvoiceSubs->sum('quantity') }}</strong> &nbsp;
                    <span class="muted">Total Quantity : <strong>{{ $customerInvoice->customerInvoiceSubs->sum('quantity') }}</strong></span>
                </td>
            </tr>
        </table>

        {{-- Scan to pay / totals --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px;">
            <tr>
                <td width="24%" valign="top">
                    <div style="border: 2px solid #f4b400; border-radius: 8px; padding: 8px; text-align: center;">
                        <div class="gold" style="font-size: 7.5pt; font-weight: bold; margin-bottom: 4px;">SCAN TO PAY</div>
                        @include('modules.print.partials.zatca-qr')
                    </div>
                </td>
                <td width="38%" valign="top" style="padding-left: 10px;">
                    <table width="100%" cellpadding="8" cellspacing="0" border="0" style="border: 1px solid #e5e7eb; border-radius: 6px;">
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="font-size: 8pt;">TOTAL BEFORE TAX ({{ $customerInvoice->currency }})<br><span class="ar muted" style="font-size: 7.5pt;">الإجمالي قبل الضريبة</span></td>
                            <td align="right" style="font-weight: bold; font-size: 10pt;">{{ amountFormat($customerInvoice->sub_total) }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 8pt;">VAT ({{ rtrim(rtrim(number_format((float) ($customerInvoice->sub_total > 0 ? $customerInvoice->tax_total / $customerInvoice->sub_total * 100 : 0), 2), '0'), '.') }}%)<br><span class="ar muted" style="font-size: 7.5pt;">ضريبة القيمة المضافة</span></td>
                            <td align="right" style="font-weight: bold; font-size: 10pt;">{{ amountFormat($customerInvoice->tax_total) }}</td>
                        </tr>
                        <tr data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? 'border-top: 1px solid #e5e7eb;' : 'display:none' }}">
                            <td style="font-size: 8pt;">OUTSTANDING<br><span class="ar muted" style="font-size: 7.5pt;">الرصيد المستحق</span></td>
                            <td align="right" style="font-weight: bold; font-size: 10pt;">{{ amountFormat($customerBalance) }}</td>
                        </tr>
                    </table>
                </td>
                <td width="38%" valign="top" style="padding-left: 10px;">
                    <div style="background: var(--accent); color: #fff; border-radius: 6px; padding: 14px;">
                        <div style="font-size: 8pt;">GRAND TOTAL ({{ $customerInvoice->currency }})<br><span class="ar" style="font-size: 7.5pt;">المجموع الكلي</span></div>
                        <div style="font-size: 18pt; font-weight: bold; margin-top: 6px;">{{ amountFormat($customerInvoice->grand_total) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Amount in words --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px;">
            <tr>
                <td style="background: #f7f9fc; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px;">
                    <div style="font-weight: bold; font-size: 8.5pt;">AMOUNT IN WORDS <span class="ar" style="font-size: 8pt;">| المبلغ كتابة</span></div>
                    <div class="ar" style="font-size: 8.5pt; margin-top: 6px;">{{ convert(round((float) $customerInvoice->grand_total, 2), $customerInvoice->currency) }}</div>
                    <div style="font-size: 8.5pt; margin-top: 4px;">{{ amountInWords(round((float) $customerInvoice->grand_total, 2)) }}</div>
                </td>
            </tr>
        </table>

        {{-- Terms & Conditions --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px;">
            <tr>
                <td style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px;">
                    <div style="font-weight: bold; font-size: 8.5pt; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px;">TERMS &amp; CONDITIONS <span class="ar" style="font-size: 8pt;">| الشروط والأحكام</span></div>
                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px; font-size: 8pt;">
                        <tr>
                            <td width="50%" valign="top" style="padding: 2px 8px 2px 0;">&bull; Payment is due within 14 days from the invoice date.</td>
                            <td width="50%" valign="top" style="padding: 2px 0 2px 8px;">&bull; A late fee of 1% per month will be applied on overdue amounts.</td>
                        </tr>
                        <tr>
                            <td width="50%" valign="top" style="padding: 2px 8px 2px 0;">&bull; Please ensure the details before making the payment.</td>
                            <td width="50%" valign="top" style="padding: 2px 0 2px 8px;">&bull; Please retain a copy of this invoice for your tax and accounting records.</td>
                        </tr>
                    </table>
                    @if($customerInvoice->terms)
                        <div style="font-size: 8pt; margin-top: 6px; line-height: 1.4;">{{ $customerInvoice->terms }}</div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Bank / Signature --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 6px;">
            <tr>
                <td width="58%" valign="top">
                    <div style="font-weight: bold; font-size: 9pt;">BANK DETAILS <span class="ar" style="font-size: 8pt;">| بيانات الحساب البنكي</span></div>
                    @if($bank)
                        <table cellpadding="2" cellspacing="0" border="0" style="font-size: 8pt; margin-top: 4px;">
                            <tr><td class="muted" style="width: 35%;">BANK NAME</td><td>{{ $bank->name }}</td></tr>
                            <tr><td class="muted">ACCOUNT NAME</td><td>{{ $bank->account_name ?: $company->name }}</td></tr>
                            <tr><td class="muted">ACCOUNT NO.</</td><td>{{ $bank->account_no }}</td></tr>
                            <tr><td class="muted">IBAN</td><td>{{ $bank->iban }}</td></tr>
                            @if($bank->swift)<tr><td class="muted">SWIFT CODE</td><td>{{ $bank->swift }}</td></tr>@endif
                        </table>
                    @else
                        <div class="muted" style="font-size: 8pt; margin-top: 6px;">No bank account on file.</div>
                    @endif
                </td>
                <td width="4%"></td>
                <td width="38%" valign="top">
                    <div style="font-weight: bold; font-size: 9pt;">AUTHORISED SIGNATURE <span class="ar" style="font-size: 8pt;">| توقيع معتمد</span></div>
                    <div style="font-size: 17pt; font-style: italic; font-weight: bold; color: var(--accent); margin-top: 6px;">
                        {{ $company->name }}
                    </div>
                    <div style="border-top: 1px solid #9ca3af; margin-top: 6px; padding-top: 4px; font-size: 8.5pt;">
                        {{ $company->name }}<br>
                        <span class="muted" style="font-size: 7.5pt;">Finance Manager</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer bar pinned to bottom --}}
    <div style="background: var(--accent); color: #fff; margin-top: 10px; padding: 8px 16px; border-radius: 6px; font-size: 8pt;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td valign="middle">
                    @if($company->phone)Tel: {{ $company->phone }} &nbsp;|&nbsp; @endif
                    @if($company->email)Email: {{ $company->email }}@endif
                </td>
                <td valign="middle" align="right">
                    THANK YOU FOR YOUR BUSINESS! <span class="ar">| شكراً لتعاملكم معنا!</span>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
