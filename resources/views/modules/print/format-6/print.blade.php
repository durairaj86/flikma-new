<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Invoice — {{ $customerInvoice->row_no }}</title>
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

        .ar, [class*="-ar"] {
            font-family: 'NotoArabic', 'Body', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Body', 'NotoArabic', sans-serif;
            color: #111827;
            font-size: 9pt;
            line-height: 1.3;
            background: #ffffff;
        }

        @page {
            margin: 16px;
        }

        .paper {
            width: 740px;
            margin: 0 auto;
            padding: 6px 9px;
            background: #fff;
            position: relative;
            min-height: 1040px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .ar {
            direction: rtl;
            text-align: right;
            unicode-bidi: embed;
        }

        .muted {
            color: #6b7280;
        }

        .green {
            color: var(--accent);
        }

        .bg-green {
            background: var(--accent);
            color: #ffffff;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            border-spacing: 0;
        }

        table.items th {
            font-size: 7.5pt;
            font-weight: bold;
            background: var(--accent);
            color: #ffffff;
            padding: 8px 6px;
        }

        table.items td {
            font-size: 8pt;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
            padding: 6px;
        }

        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 90pt;
            font-weight: 900;
            text-transform: uppercase;
            color: rgba(150, 150, 150, 0.12);
            z-index: 999;
            white-space: nowrap;
            pointer-events: none;
        }
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

        {{-- Top Header Table: Logo (Left), Tax Invoice Title (Center), Invoice Number (Right) --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="30%" valign="top">
                    @if($company->logo)
                        <img src="{{ $company->logo }}"
                             style="max-height: 50px; max-width: 200px; margin-bottom: 4px;">
                    @else
                        <div
                            style="font-size: 11pt; font-weight: bold; color: var(--accent); line-height: 1.1;">{{ $company->name }}</div>
                    @endif

                </td>
                <td width="40%" valign="top" align="center" style="padding-top: 2px;">
                    <div
                        style="font-size: 18pt; font-weight: bold; color: var(--accent); line-height: 1.1; letter-spacing: 0.5px;">
                        {{ $docTitle }}
                    </div>
                    <div
                        style="font-size: 9pt; color: #555; margin-top: 2px; font-weight: bold;">{{ $docTitleAr }}</div>
                </td>
                <td width="30%" valign="top" align="right">
                    <div
                        style="display: inline-block; text-align: right; border: 1px solid var(--accent); padding: 6px 12px; border-radius: 4px; background: transparent;">
                        <div
                            style="font-size: 6.5pt; text-transform: uppercase; color: #6b7280; font-weight: bold;">
                            Invoice No. / رقم الفاتورة
                        </div>
                        <div
                            style="font-size: 11pt; font-weight: bold; color: #222; margin-top: 1px;">{{ $customerInvoice->row_no ?: '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Dates & Summary Bar --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%"
               style="margin-top: 12px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
            <tr>
                <td width="50%" valign="top" style="padding: 8px 0;">
                    <div class="muted" style="font-size: 7pt; text-transform: uppercase;">
                        Total Amount / إجمالي المبلغ
                    </div>
                    <div style="font-size: 12pt; font-weight: bold; margin-top: 2px;">
                        {{ amountFormat($customerInvoice->grand_total) }} <span class="muted"
                                                                             style="font-size: 7.5pt;">{{ $customerInvoice->currency }}</span>
                    </div>
                </td>
                <td width="25%" valign="top" align="right" style="padding: 8px 0;">
                    <div class="muted" style="font-size: 7pt;">Invoice Date / <span
                            class="ar muted"
                            style="font-size: 7pt;">تاريخ الفاتورة</span></div>

                    <div style="font-size: 8pt; font-weight: bold; margin-top: 2px;">
                        {{ $customerInvoice->invoice_date ? \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d F Y') : '-' }}<span data-toggle="show_time" style="{{ ($settings->show_time ?? false) ? '' : 'display:none' }}"> {{ \Carbon\Carbon::parse($customerInvoice->created_at)->format('H:i') }}</span>
                    </div>
                </td>
                <td width="25%" valign="top" align="right" style="padding: 8px 0 8px 10px;">
                    @if($customerInvoice->due_date)
                        <div class="muted" style="font-size: 7pt;">Due Date / <span
                                class="ar muted"
                                style="font-size: 7pt;">تاريخ الاستحقاق</span></div>

                        <div style="font-size: 8pt; font-weight: bold; margin-top: 2px;">
                            {{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d F Y') }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- Billed From / Billed To --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 12px;">
            <tr>
                <td width="48%" valign="top" style="padding-right: 10px;">
                    <div
                        style="font-weight: bold; font-size: 8.5pt; border-bottom: 2px solid var(--accent); padding-bottom: 3px; margin-bottom: 6px;">
                        Billed From / من:
                    </div>
                    <div style="font-size: 8pt; line-height: 1.5; ">
                        {{ $company->name }}<br>
                        @if($company->address)
                            {{ $company->address }},
                        @endif
                        {{ $company->city }},
                        @if($company->postal_code)
                            {{ $company->postal_code }}<br>
                        @endif
                        <div style="font-size: 8pt; margin-top: 6px;">
                            <span style="">VAT Number:</span> {{ $company->vat_number }}<br>
                            <span style="">CR Number:</span> {{ $company->cr_number }}
                        </div>
                    </div>
                </td>
                <td width="4%" valign="top"></td>
                <td width="48%" valign="top" style="padding-left: 10px;">
                    <div
                        style="font-weight: bold; font-size: 8.5pt; border-bottom: 2px solid var(--accent); padding-bottom: 3px; margin-bottom: 6px;">
                        Billed To / إلى:
                    </div>
                    <div style="font-size: 8pt; line-height: 1.5; ">
                        {{ $customerInvoice->customer?->name_en }}<br>
                        {{ $customerInvoice->customer?->address1_en }}@if($customerInvoice->customer?->city_en)
                            , {{ $customerInvoice->customer->city_en }}
                        @endif
                        @if($customerInvoice->customer?->vat_number)
                            <br>
                            <div style="font-size: 8pt; margin-top: 6px;">
                                <span>VAT Number : </span>{{ $customerInvoice->customer->vat_number }}
                            </div>
                        @endif
                        @if($customerInvoice->customer?->cr_number)
                            <div style="font-size: 8pt;">
                                <span>CR Number : </span>{{ $customerInvoice->customer->cr_number }}
                            </div>
                        @endif
                    </div>
                    @if($customerInvoice->customer?->name_ar)
                        <div class="ar" style="font-size: 8pt; margin-top: 6px; line-height: 1.6; ">
                            {{ $customerInvoice->customer->name_ar }}<br>
                            {{ $customerInvoice->customer?->address1_ar }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        @include('modules.print.partials.extra-details')

        {{-- Line Items Table --}}
        <table width="100%" cellpadding="0" cellspacing="0" border="0" class="items" style="margin-top: 12px;">
            <thead>
            <tr>
                <th align="left" style="width: 5%;">SL<br><span style="font-size: 6pt; font-weight: normal;" class="ar">م</span>
                </th>
                <th align="left" style="width: 37%;">Description<br><span
                        style="font-size: 6pt; font-weight: normal;" class="ar">بيان الصنف</span></th>
                <th align="left" style="width: 8%;{{ ($settings->hsn_sac ?? false) ? '' : 'display:none' }}"
                    data-toggle="hsn_sac">HSN/SAC<br><span style="font-size: 6pt; font-weight: normal;" class="ar">الرمز</span></th>
                <th align="left" style="width: 8%;{{ ($settings->unit ?? true) ? '' : 'display:none' }}"
                    data-toggle="unit">Unit<br><span style="font-size: 6pt; font-weight: normal;"
                                                        class="ar">الوحدة</span></th>
                <th align="right" style="width: 10%;">Qty<br><span style="font-size: 6pt; font-weight: normal;"
                                                                 class="ar">الكمية</span></th>
                <th align="right" style="width: 13%;{{ ($settings->rate ?? true) ? '' : 'display:none' }}"
                    data-toggle="rate">Unit Price<br><span style="font-size: 6pt; font-weight: normal;"
                                                            class="ar">السعر</span></th>
                <th align="right" style="width: 10%;{{ ($settings->discount ?? false) ? '' : 'display:none' }}"
                    data-toggle="discount">Disc<br><span style="font-size: 6pt; font-weight: normal;"
                                                      class="ar">الخصم</span></th>
                <th align="right" style="width: 10%;">Total Price<br>
                    <span style="font-size: 6pt; font-weight: normal;" class="ar">
        السعر الإجمالي
    </span>
                </th>
                <th align="right" style="width: 8%;">VAT<br><span style="font-size: 6pt; font-weight: normal;"
                                                                  class="ar">النسبة</span></th>
                <th align="right" style="width: 9%;">VAT Amt<br><span style="font-size: 6pt; font-weight: normal;"
                                                                      class="ar">قيمة الضريبة</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach($customerInvoice->customerInvoiceSubs as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>{{ $item->description }}</div>
                        @if($item->comment)
                            <div data-toggle="item_description" style="{{ ($settings->item_description ?? true) ? 'font-size: 7.5pt; margin-top: 1px;' : 'display:none' }}">{{ $item->comment }}</div>
                        @endif
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

        {{-- Totals Table --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 14px;">
            <tr>
                <td width="52%" valign="middle" align="center">
                    @include('modules.print.partials.zatca-qr')
                </td>
                <td width="48%" valign="top">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #e5e7eb;">
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 6px 8px; font-size: 7.5pt;">
                                Total before VAT<br><span class="ar muted"
                                                          style="font-size: 6.5pt;">الإجمالي قبل القيمة المضافة</span>
                            </td>
                            <td align="right" style="padding: 6px 8px; font-weight: bold;">{{ amountFormat($customerInvoice->sub_total) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 6px 8px; font-size: 7.5pt;">
                                VAT ({{ rtrim(rtrim(number_format((float) ($customerInvoice->sub_total > 0 ? $customerInvoice->tax_total / $customerInvoice->sub_total * 100 : 0), 2), '0'), '.') }}%)<br><span class="ar muted"
                                                                                                                                                                       style="font-size: 6.5pt;">ضريبة القيمة المضافة</span>
                            </td>
                            <td align="right" style="padding: 6px 8px; font-weight: bold;">{{ amountFormat($customerInvoice->tax_total) }}</td>
                        </tr>
                        <tr data-toggle="party_balance" style="{{ ($settings->party_balance ?? false) ? 'border-bottom: 1px solid #e5e7eb;' : 'display:none' }}">
                            <td style="padding: 6px 8px; font-size: 7.5pt;">
                                Outstanding<br><span class="ar muted" style="font-size: 6.5pt;">الرصيد المستحق</span>
                            </td>
                            <td align="right" style="padding: 6px 8px; font-weight: bold;">{{ amountFormat($customerBalance) }}</td>
                        </tr>
                        <tr class="bg-green">
                            <td style="padding: 8px; font-size: 8.5pt; font-weight: bold;">
                                Net Amount<br><span
                                    class="ar" style="font-size: 7.5pt;">المبلغ الإجمالي</span>
                            </td>
                            <td align="right"
                                style="padding: 8px; font-size: 10pt; font-weight: bold;">{{ amountFormat($customerInvoice->grand_total) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Amount in words block --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
            <tr>
                <td style="padding: 7px 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
                    <div style="font-weight: bold; font-size: 7.5pt;">Amount in Words / إجمالي المبلغ كتابة:</div>
                    <div style="font-size: 7.5pt;  margin-top: 2px;">{{ amountInWords(round((float) $customerInvoice->grand_total, 2)) }}</div>
                    <div class="ar"
                         style="font-size: 7.5pt; margin-top: 2px;">{{ convert(round((float) $customerInvoice->grand_total, 2), $customerInvoice->currency) }}</div>
                </td>
            </tr>
        </table>

        {{-- Payment Method (left) & Terms and Conditions (right) --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px;">
            <tr>
                <td width="48%" valign="top" style="padding: 7px 10px; border: 1px solid #e5e7eb;">
                    @if($bank)
                        <div
                            style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px;">
                            Payment Method / طرق الدفع
                        </div>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="font-size: 7.5pt; line-height: 1.3;">
                            <tr>
                                <td class="muted" style="padding: 2px 6px 2px 0; width: 35%;">
                                    Account Name:
                                </td>
                                <td style="padding: 2px 0; width: 65%;">
                                    <strong>{{ $bank->account_name ?: $company->name }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="muted" style="padding: 2px 6px 2px 0;">Bank Name /
                                    <span
                                        class="ar">البنك</span>:
                                </td>
                                <td style="padding: 2px 0;">{{ $bank->name }}</td>
                            </tr>
                            @if($bank->account_no)
                                <tr>
                                    <td class="muted" style="padding: 2px 6px 2px 0;">Account
                                        No.:
                                    </td>
                                    <td style="padding: 2px 0;">{{ $bank->account_no }}</td>
                                </tr>
                            @endif
                            @if($bank->iban)
                                <tr>
                                    <td class="muted" style="padding: 2px 6px 2px 0;">IBAN / <span
                                            class="ar">الايبان</span>:
                                    </td>
                                    <td style="padding: 2px 0;"><strong>{{ $bank->iban }}</strong></td>
                                </tr>
                            @endif
                        </table>
                    @endif
                </td>
                <td width="4%"></td>
                <td width="48%" valign="top"
                    @if($customerInvoice->terms) style="padding: 7px 10px; background: #f9fafb; border: 1px solid #e5e7eb;" @endif>
                    <div
                        style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px;">
                        Terms &amp; Conditions / الشروط والأحكام
                    </div>
                    <div style="font-size: 6.5pt; margin-top: 2px; line-height: 1.4;">
                        @if($customerInvoice->terms)
                            {{ $customerInvoice->terms }}
                        @elseif($customerInvoice->notes)
                            {{ $customerInvoice->notes }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Email pinned to bottom --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%"
           style="margin-top: 25px; border-top: 1px solid #e5e7eb; padding-top: 15px;">
        <tr>
            <td width="55%" valign="bottom" class="muted" style="font-size: 6.5pt; line-height: 1.4;">
                THANK YOU FOR YOUR BUSINESS! | <span class="ar" style="direction: ltr; display: inline-block;"> شكراً لتعاملكم معنا!</span>
            </td>
            <td width="45%" valign="bottom" align="right" style="text-align: right;font-size: 7.5pt;">
                @if($company->email)
                    Email: {{ $company->email }} &nbsp;|&nbsp;
                @endif
                @if($company->phone)
                    Tel: {{ $company->phone }}
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
