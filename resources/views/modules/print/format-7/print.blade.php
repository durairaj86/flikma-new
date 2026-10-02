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
        .ar, [class*="-ar"] { font-family: 'NotoArabic', 'Body', Arial, sans-serif !important; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Body', 'NotoArabic', Arial, sans-serif; color: #1f2937; font-size: 9pt; line-height: 1.35; background: #ffffff; }
        @page { margin: 15px; }
        .paper { width: 740px; margin: 0 auto; padding: 7px 7px; background: #fff; position: relative; min-height: 1040px; display: flex; flex-direction: column; justify-content: space-between; }
        .ar { direction: rtl; text-align: right; unicode-bidi: embed; }
        .muted { color: #6b7280; }
        .navy { color: var(--accent); }
        table { border-collapse: collapse; width: 100%; }

        .watermark {
            position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 90pt; font-weight: 900; text-transform: uppercase; color: rgba(150, 150, 150, 0.12);
            z-index: 999; white-space: nowrap; pointer-events: none;
        }

        table.items th { font-size: 7.5pt; font-weight: bold; padding: 6px 8px; background: var(--accent); color: #ffffff; }
        table.items td { font-size: 8pt; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
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

        {{-- Header: wordmark logo | title | invoice no. box --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td  valign="top">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="38" valign="top">
                                @if($company->logo)
                                    <img src="{{ $company->logo }}" style="max-width: 200px; max-height: 60px; border-radius: 6px;">
                                @else
                                    <div style="width: 34px; height: 34px; background: var(--accent); border-radius: 6px; color: #fff; font-size: 16pt; font-weight: bold; text-align: center; line-height: 34px;">
                                        {{ strtoupper(substr($company->name ?? 'F', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="34%" valign="top" align="center" style="padding-top: 4px;">
                    <div class="navy" style="font-size: 14pt; font-weight: bold; line-height: 1.1;">{{ $docTitleAr }}</div>
                    <div class="navy" style="font-size: 13pt; font-weight: bold; letter-spacing: 0.5px;">{{ $docTitle }}</div>
                </td>
                <td width="33%" valign="top" align="right">
                    <div style="background: var(--accent); color: #fff; border-radius: 6px; padding: 6px 12px; display: inline-block; text-align: center; min-width: 170px;">
                        <div style="font-size: 6.5pt;"><span class="ar">رقم الفاتورة</span> | INVOICE NO.</div>
                        <div style="font-size: 11pt; font-weight: bold; margin-top: 2px;">{{ $customerInvoice->row_no ?: '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="border-top: 2px solid var(--accent); margin-top: 8px;"></div>

        {{-- Meta Dates Row --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
            <tr>
                <td width="50%" valign="top">
                    <div class="muted" style="font-size: 8pt; line-height: 1.5;">
                        <span style="font-weight: bold; color: #1f2937;">E-Invoicing Compliant Invoice</span><br>
                        Generated in accordance with ZATCA requirements.
                    </div>
                </td>
                <td width="50%" valign="top" align="right">
                    <table cellpadding="3" cellspacing="0" border="0" style="font-size: 8pt;" align="right">
                        <tr>
                            <td class="muted" style="text-align: right;">INVOICE DATE | <span class="ar"> تاريخ الفاتورة</span>&nbsp;:</td>
                            <td style="font-weight: bold; padding-left: 6px;">{{ $customerInvoice->invoice_date ? \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d/m/Y') : '-' }}<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('H:i') }}</span></td>
                        </tr>
                        <tr>
                            <td class="muted" style="text-align: right;padding-top:5px">DUE DATE | <span class="ar"> تاريخ الاستحقاق</span>&nbsp;:</td>
                            <td style="font-weight: bold; padding-left: 6px;">{{ $customerInvoice->due_date ? \Carbon\Carbon::parse($customerInvoice->due_date)->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Company Address (Left) & Bill To (Right) Side-by-Side Layout --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 10px;">
            <tr>
                {{-- Left Side: Company Address --}}
                <td width="48%" valign="top">
                    <div style="background: var(--accent); color: #fff; padding: 8px 12px; border-radius: 6px 6px 0 0; font-size: 8.5pt; font-weight: bold;">
                        SUPPLIER <span class="ar">| المورد</span>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 6px 6px; padding: 12px; font-size: 8.5pt; min-height: 135px;">
                        <div style="font-weight: bold; font-size: 9pt;">{{ $company->name }}</div>

                        <div class="muted" style="font-size: 8pt; margin-top: 6px; line-height: 1.5;">
                            @if($company->address){{ $company->address }}<br>@endif
                            {{ $company->city }}<br>
                            {{ $company->country ?: 'Kingdom of Saudi Arabia' }}
                        </div>

                        <div data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">
                            @if($company->phone)<div class="muted" style="font-size: 8pt; margin-top: 6px;">Tel: {{ $company->phone }}</div>@endif
                        </div>
                        @if($company->email)<div class="muted" style="font-size: 8pt; margin-top: 2px;">Email: {{ $company->email }}</div>@endif
                        @if($company->vat_number)
                            <div style="font-size: 8pt; margin-top: 4px;"><span class="ar">الرقم الضريبي</span> / VAT NO: <strong>{{ $company->vat_number }}</strong></div>
                        @endif
                        @if($company->cr_number)
                            <div style="font-size: 8pt; margin-top: 4px;"><span class="ar">رقم السجل التجاري</span> / CR NO: <strong>{{ $company->cr_number }}</strong></div>
                        @endif
                    </div>
                </td>

                <td width="4%"></td>

                {{-- Right Side: Bill To --}}
                <td width="48%" valign="top">
                    <div style="background: var(--accent); color: #fff; padding: 8px 12px; border-radius: 6px 6px 0 0; font-size: 8.5pt; font-weight: bold;">
                        BILL TO <span class="ar">| الفاتورة إلى</span>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 6px 6px; padding: 12px; font-size: 8.5pt; min-height: 135px;">
                        <div style="font-weight: bold; font-size: 9pt;">{{ $customerInvoice->customer?->name_en }}</div>
                        @if($customerInvoice->customer?->name_ar)<div class="ar" style="font-size: 8.5pt; margin-top: 2px;">{{ $customerInvoice->customer->name_ar }}</div>@endif

                        <div class="muted" style="font-size: 8pt; margin-top: 6px; line-height: 1.5;">
                            @if($customerInvoice->customer?->address1_en){{ $customerInvoice->customer->address1_en }}<br>@endif
                            @if($customerInvoice->customer?->city_en){{ $customerInvoice->customer->city_en }}<br>@endif
                            {{ $customerInvoice->customer?->country ?: 'Kingdom of Saudi Arabia' }}
                        </div>

                        <div data-toggle="show_phone" style="{{ ($settings->show_phone ?? true) ? '' : 'display:none' }}">
                            @if($customerInvoice->customer?->phone)<div class="muted" style="font-size: 8pt; margin-top: 6px;">Tel: {{ $customerInvoice->customer->phone }}</div>@endif
                        </div>
                        @if($customerInvoice->customer?->email)<div class="muted" style="font-size: 8pt; margin-top: 2px;">Email: {{ $customerInvoice->customer->email }}</div>@endif
                        @if($customerInvoice->customer?->vat_number)
                            <div style="font-size: 8pt; margin-top: 4px;"><span class="ar">الرقم الضريبي</span> / VAT NO: <strong>{{ $customerInvoice->customer->vat_number }}</strong></div>
                        @endif
                        @if($customerInvoice->customer?->cr_number)
                            <div style="font-size: 8pt; margin-top: 4px;"><span class="ar">رقم السجل التجاري</span> / CR NO: <strong>{{ $customerInvoice->customer->cr_number }}</strong></div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Line Items --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="items" style="margin-top: 10px;">
            <thead>
            <tr>
                <th align="left" style="width: 4%;">#</th>
                <th align="left" style="width: 24%;">DESCRIPTION<br><span class="ar" style="font-weight: normal;">وصف المنتج/الخدمة</span></th>
                <th align="left" style="width: 8%;{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}" data-toggle="hsn_sac">HSN/SAC<br><span class="ar" style="font-weight: normal;">الرمز</span></th>
                <th align="left" style="width: 8%;{{ ($settings->unit ?? true) ? '' : 'display:none' }}" data-toggle="unit">UNIT<br><span class="ar" style="font-weight: normal;">الوحدة</span></th>
                <th align="right" style="width: 7%;">QTY<br><span class="ar" style="font-weight: normal;">الكمية</span></th>
                <th align="right" style="width: 12%;{{ ($settings->rate ?? true) ? '' : 'display:none' }}" data-toggle="rate">UNIT PRICE<br><span class="ar" style="font-weight: normal;">سعر الوحدة</span></th>
                <th align="right" style="width: 9%;{{ ($settings->discount ?? false) ? '' : 'display:none' }}" data-toggle="discount">DISC<br><span class="ar" style="font-weight: normal;">الخصم</span></th>
                <th align="right" style="width: 13%;">BEFORE VAT<br><span class="ar" style="font-weight: normal;">الإجمالي قبل الضريبة</span></th>
                <th align="right" style="width: 8%;">VAT %<br><span class="ar" style="font-weight: normal;">نسبة الضريبة</span></th>
                <th align="right" style="width: 12%;">VAT AMOUNT<br><span class="ar" style="font-weight: normal;">قيمة الضريبة</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($customerInvoice->customerInvoiceSubs as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $item->description }}</div>
                        @if($item->comment)<div class="muted" data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? 'font-size: 7.5pt; margin-top: 1px;' : 'display:none' }}">{{ $item->comment }}</div>@endif
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

        {{-- Amount in words + Totals --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
            <tr>
                <td width="48%" valign="top">
                    <div style="background: #f7f9fc; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px;">
                        <div style="font-weight: bold; font-size: 8pt;">AMOUNT IN WORDS</div>
                        <div class="ar" style="font-size: 8pt; font-weight: bold; margin-top: 1px;">المبلغ كتابة</div>
                        <div style="font-size: 8pt; margin-top: 6px;">{{ amountInWords(round((float) $customerInvoice->grand_total, 2)) }}</div>
                        <div class="ar" style="font-size: 8pt; margin-top: 2px;">{{ convert(round((float) $customerInvoice->grand_total, 2), $customerInvoice->currency) }}</div>
                    </div>
                </td>
                <td width="4%"></td>
                <td width="48%" valign="top">
                    <table width="100%" cellpadding="8" cellspacing="0" border="0" style="border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden;">
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="font-size: 8pt;padding-bottom: 3px;padding-top: 3px">TOTAL BEFORE VAT | <span class="ar muted" style="font-size: 7pt;">المحموع قبل لضريبة</span></td>
                            <td align="right" style="font-weight: bold;">{{ amountFormat($customerInvoice->sub_total) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="font-size: 8pt;padding-bottom: 3px;padding-top: 3px">TOTAL VAT ({{ rtrim(rtrim(number_format((float) ($customerInvoice->sub_total > 0 ? $customerInvoice->tax_total / $customerInvoice->sub_total * 100 : 0), 2), '0'), '.') }}%) | <span class="ar muted" style="font-size: 7pt;">إجمالي لضريبة</span></td>
                            <td align="right" style="font-weight: bold;">{{ amountFormat($customerInvoice->tax_total) }}</td>
                        </tr>
                        <tr data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? 'border-bottom: 1px solid #e5e7eb;' : 'display:none' }}">
                            <td style="font-size: 8pt;padding-bottom: 3px;padding-top: 3px">OUTSTANDING | <span class="ar muted" style="font-size: 7pt;">الرصيد المستحق</span></td>
                            <td align="right" style="font-weight: bold;">{{ amountFormat($customerBalance) }}</td>
                        </tr>
                        <tr style="background: var(--accent); color: #fff;">
                            <td style="font-size: 9pt; font-weight: bold; border-radius: 0 0 0 6px;padding:5px;">GRAND TOTAL ({{ $customerInvoice->currency }}) <span class="ar" style="font-size: 8pt;">الإجمالي الكلي</span></td>
                            <td align="right" style="font-size: 11pt; font-weight: bold; border-radius: 0 0 6px 0;padding:5px;">{{ amountFormat($customerInvoice->grand_total) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Notes Section (Full Width Left to Right) --}}
        <div style="margin-top: 10px; background: #fdfdfd; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px;">
            <div style="font-weight: bold; font-size: 8.5pt;">NOTES | <span class="ar" style="font-size: 7.5pt;"> ملاحظات</span></div>
            <div style="font-size: 7.5pt; margin-top: 4px; line-height: 1.5;">
                @if($customerInvoice->notes)
                    {!! nl2br(e($customerInvoice->notes)) !!}
                @else
                    Please make payment within 30 days. Late payment may incur additional charges. This is an electronically generated invoice.
                @endif
                @if($customerInvoice->terms)<div style="margin-top: 4px;">{{ $customerInvoice->terms }}</div>@endif
            </div>
        </div>

        <div style="border-top: 2px solid var(--accent); margin-top: 10px;"></div>

        {{-- Bank Details & QR Code Section --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
            <tr>
                <td width="55%" valign="top">
                    <div style="font-weight: bold; font-size: 8.5pt;">BANK DETAILS | <span class="ar" style="font-size: 7.5pt;"> بيانات التحويل</span></div>
                    @if($bank)
                        <table cellpadding="2" cellspacing="0" border="0" style="font-size: 7.5pt; margin-top: 4px;">
                            <tr><td class="muted" style="width: 35%;">BANK NAME</td><td>{{ $bank->name }}</td></tr>
                            <tr><td class="muted">ACCOUNT NAME</td><td>{{ $bank->account_name ?: $company->name }}</td></tr>
                            <tr><td class="muted">ACCOUNT NO.</</td><td>{{ $bank->account_no }}</td></tr>
                            <tr><td class="muted">IBAN NO.</td><td>{{ $bank->iban }}</td></tr>
                            @if($bank->swift)<tr><td class="muted">SWIFT CODE</td><td>{{ $bank->swift }}</td></tr>@endif
                        </table>
                    @else
                        <div class="muted" style="font-size: 7.5pt; margin-top: 4px;">No bank account on file.</div>
                    @endif
                </td>
                <td width="45%" valign="top" align="right">
                    @include('modules.print.partials.zatca-qr')
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Bar Pinned Strictly to Bottom --}}
    <div style="background: var(--accent); color: #fff; margin-top: 10px; padding: 10px 14px; border-radius: 6px; font-size: 7.5pt;">
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
