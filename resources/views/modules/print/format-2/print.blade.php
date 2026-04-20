<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .invoice-wrapper {
            /*width: 794px;*/ /* A4 width approximation */
            /* MODIFIED: Removed padding to allow the bordered child to fill 100vh */
            padding: 0;
            /*margin: 0 auto;*/
            background-color: #fff;
            box-sizing: border-box;
            color: #333;
            font-size: 9pt;
            line-height: 1.3;
        }

        /* NEW: Make the bordered container the positioning context */
        .border-h-100-relative {
            position: relative;
        }

        /* --- Header Top Section (Logo/Brand on Left, Address on Right) --- */
        .header-top {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            margin-bottom: 5px;
        }

        .logo-box {
            font-size: 13pt;
            font-weight: 700;
            color: #1f375f;
        }

        .address-box {
            text-align: right;
            font-size: 8pt;
            line-height: 1.1;
        }

        .address-box p {
            margin: 0;
            padding: 1px 0;
        }

        /* --- TAX INVOICE Row (50%/50% Split) --- */
        .invoice-title-row {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 50%/50% split for col-6 */
            align-items: center;
            padding-bottom: 5px;
        }

        .invoice-title-row .english-title {
            text-align: left;
            font-size: 14pt;
            font-weight: 700;
            margin: 0;
        }

        .invoice-title-row .arabic-title {
            text-align: right;
            font-size: 14pt;
            font-weight: 700;
            direction: rtl;
            margin: 0;
        }

        /* --- Customer & Details Section (Outer Bordered Box) --- */
        /*.details-section {
            margin-bottom: 5px;
        }*/

        /* --- Customer Address Split (English Left, Arabic Right - Row 1) --- */
        .customer-address-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* OVERRIDE: Remove vertical padding from the parent to allow children to touch top/bottom borders */
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        /* NEW FLEX CONTAINER FOR CUSTOMER DETAILS */
        .customer-split-flex {
            display: flex;
            line-height: 1.2;
            font-size: 8.5pt;
        }

        /* MODIFIED: Add vertical padding back to the English child for content spacing, and handle border */
        .customer-address-english {
            padding-top: 8px; /* Re-add vertical spacing (approx p-2) */
            padding-bottom: 8px; /* Re-add vertical spacing (approx p-2) */
            padding-right: 5px;
            border-right: 1px solid #000; /* Added vertical divider */
        }

        .customer-label-col {
            width: 20%; /* Approx col-4 */
            flex-shrink: 0;
            font-weight: 700;
            font-size: 9.5pt;
            padding-right: 5px;
        }

        .customer-details-col {
            width: 80%; /* Approx col-8 */
        }

        .customer-details-col p {
            margin: 0 0 3px 0;
        }

        .customer-details-col .customer-name-bold {
            font-weight: 700;
            font-size: 9.5pt;
            margin-bottom: 3px;
            margin-top: 0;
        }

        /* MODIFIED: Add vertical padding back to the Arabic child for content spacing */
        .customer-address-arabic {
            padding-top: 8px; /* Re-add vertical spacing (approx p-2) */
            padding-bottom: 8px; /* Re-add vertical spacing (approx p-2) */
            text-align: right;
            padding-left: 5px; /* Added left padding for spacing from the divider */
        }

        .customer-address-arabic .customer-label-col {
            text-align: right;
            direction: rtl;
        }

        .customer-address-arabic .customer-details-col {
            text-align: right;
        }

        /* END NEW FLEX CONTAINER STYLES */

        /* --- Metadata 3-Column Grid --- */
        .metadata-grid-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; /* col-4, col-4, col-4 structure */
            font-size: 8.5pt;
            padding: 3px 0;
            line-height: 1.2;
        }

        .metadata-grid-row:last-of-type {
            border-bottom: none;
        }

        .metadata-grid-row .label-english {
            font-weight: 700;
            text-align: left;
            padding-left: 5px;
        }

        .metadata-grid-row .data-value {
            text-align: center;
            font-weight: 400;
        }

        .metadata-grid-row .label-arabic {
            font-weight: 700;
            text-align: right;
            direction: rtl;
            padding-right: 5px;
        }

        /* --- Shipping/Job Details Section --- */
        .shipping-job-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
            font-size: 8.5pt;
            border-bottom: 1px solid #000;
        }

        .shipping-job-section table {
            width: 49%;
            margin: 2px 0;
            border-collapse: collapse;
        }

        .shipping-job-section td {
            padding: 2px 5px;
            line-height: 1.2;
            vertical-align: baseline;
        }

        /* NEW STYLES FOR ALIGNMENT */
        .shipping-job-section .label-col {
            font-weight: 700;
            width: 25%; /* Fixed width for labels */
            padding-right: 0;
        }

        .shipping-job-section .data-col {
            text-align: left;
            padding-left: 0;
        }

        .shipping-job-section .separator-col {
            width: 10px; /* Width for the colon column */
            text-align: center;
            padding: 2px 0;
            font-weight: 700; /* Ensure colon is visible */
        }

        /* END NEW STYLES */

        /* --- Charges Table (Line Items Only) --- */
        .charges-table {

            border-collapse: collapse;
            font-size: 8.5pt;
            /* UPDATED: Only top and bottom borders, no left/right */
            /*border-top: 1px solid #000;*/
            border-bottom: 1px solid #000;
            border-left: none;
            border-right: none;
            /* Adjust position relative to the p-4 padding of the main container */

            width: calc(100% - 1px); /* Stretch to full width including padding adjustment (p-4 is 16px left/right) */
        }

        .charges-table thead th {
            background-color: #e0e0e0;
            font-weight: 700;
            padding: 3px 8px;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            line-height: 1.1;
            vertical-align: baseline;
        }

        .charges-table thead th:last-child {
            border-right: none;
        }

        .charges-table tbody td {
            padding: 6px 8px;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            text-align: right;
        }

        .charges-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* UPDATED: Remove right border of the description column */
        .charges-table .charge-desc {
            text-align: left;
            width: 30%;
        }

        /* NEW: Remove right border of the last data column (Total SAR) */
        .charges-table tbody td:last-child {
            border-right: none;
        }

        /* --- Total Summary Section (New Grid Structure) --- */
        .total-summary-section {
            margin-top: -1px;
            /* UPDATED: Remove left/right border, keep top/bottom */
            border-left: none;
            border-right: none;

            padding: 5px;
            /* Adjust position to align with the rest of the content within the p-4 padding */
            width: calc(100% - 1px);
        }

        .total-summary-row {
            display: grid;
            grid-template-columns: calc(100% - 37%) 10% 5% 10% 12%;
            font-weight: 700;
            font-size: 8.5pt;
            line-height: 1.2;
        }

        /* Cell containing all textual content */
        .total-summary-row .text-and-label-col {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-left: 3px;
            padding-right: 5px;
        }

        .total-summary-row .words-group {
            flex: 1;
            text-align: left;
        }

        .total-summary-row .label-group {
            flex-shrink: 0;
            text-align: right;
            white-space: nowrap;
            padding-left: 10px;
        }

        .total-summary-row .arabic-text {
            direction: rtl;
            font-weight: 400;
            line-height: 1.2;
            display: block;
        }

        .total-summary-row .english-text {
            display: block;
            padding: 2px 0;
        }

        /* Numerical cells - align with the last four columns of the table */
        .total-summary-row .col-total-excl-vat,
        .total-summary-row .col-vat-amount,
        .total-summary-row .col-final-total {
            text-align: right;
            padding-right: 5px;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .total-summary-row .col-vat-percent {
            text-align: center;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        /* --- Footer Section --- */
        .footer-section {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            /* Temporarily push content down if needed, but not necessary with absolute footer */
            /* margin-bottom: 40px; */
        }

        .bank-details {
            /*width: 55%;*/
            font-size: 9pt;
        }

        .bank-details table {
            width: 100%;
        }

        .bank-details td {
            padding: 1px 0;
            line-height: 1.2;
        }

        .bank-details td:first-child {
            font-weight: 700;
            width: 30%;
        }

        .notes-and-total {
            width: 40%;
            text-align: right;
            line-height: 1.2;
        }

        .total-amount-words {
            font-weight: 700;
            margin-bottom: 5px;
            padding-bottom: 5px;
            direction: rtl;
        }

        .total-amount-box {
            font-size: 14pt;
            font-weight: 700;
            border: 3px solid #333;
            padding: 3px 10px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .footer-notes {
            font-size: 8pt;
            line-height: 1.1;
            padding-top: 5px;
            color: #666;
            text-align: left;
        }

        .footer-notes p {
            margin: 0 0 2px 0;
        }

        .arabic {
            direction: rtl;
        }

        /* --- MODIFIED: Position at the absolute bottom --- */
        .center-footer-text {
            /* Positioning */
            position: absolute;
            /*bottom: 0;
            left: 0;*/
            /* Layout and Appearance */
            width: 100%; /* Spans the full width of the bordered container */
            text-align: center;
            font-size: 8pt;
            line-height: 1.1;
            /* Adjust padding to align with the main content's p-4 padding */
            /*padding: 5px 16px;*/ /* 16px is the horizontal padding from p-4 */
            box-sizing: border-box; /* Include padding in the width calculation */
        }

        .center-footer-text p {
            margin: 2px 0;
        }

        .arabic-name {
            color: #FF8C00;
        }

        .draft-watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px;
            color: rgba(0, 0, 0, 0.1);
            font-weight: 800;
            text-transform: uppercase;
            z-index: 2;
            pointer-events: none;
            white-space: nowrap;
            opacity: 0.6;
            display: block;
        }

        .invoice-wrapper > *:not(.draft-watermark) {
            /*position: relative;
            z-index: 1;*/
        }

        /*.page {
            page-break-after: auto;
        }*/

        /* --- Charges Table (Line Items Only) --- */
        /* ... (existing charges-table styles) ... */

        /* NEW: Job Container Section Styles */
        .job-container-section {
            /* Aligns with the width of the charges-table and total-summary-section */
            width: calc(100% - 1px);
            margin-bottom: 15px; /* Spacing before the footer */
            /* Adjust position relative to the main content's padding (p-4 is 16px left/right) */
            padding: 0 8px; /* Horizontal padding to match content */
            box-sizing: border-box;
        }

        .job-container-section .job-container-title {
            font-size: 9pt; /* Slightly larger than table data for a title */
            font-weight: 700;
            margin-bottom: 5px;
        }

        .job-container-section table {
            width: 50%; /* Make it span half the width, similar to the shipping/job tables */
            border-collapse: collapse;
            font-size: 8.5pt;
            /* Optional: Add a light border for structure */
            border: 1px solid #ccc;
        }

        .job-container-section thead th {
            background-color: #e0e0e0;
            font-weight: 700;
            padding: 3px 8px;
            border-bottom: 1px solid #ccc;
            text-align: left;
            line-height: 1.1;
            vertical-align: middle;
        }

        .job-container-section tbody td {
            padding: 4px 8px;
            text-align: left;
            border-right: 1px solid #eee; /* Light column separation */
        }

        .job-container-section tbody td:last-child {
            border-right: none;
        }

        /*.content-position {
            left: 40px;
            right: 40px;
        }*/

        /* ======================
           PRINT STYLES (IMPORTANT)
        ====================== */
        @media print {
            @page {
                margin: 0;
            }

            .print-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 138px;
                background-color: white;
                z-index: 1000;
                padding: 20px 30px 0 30px;
            }

            .print-footer {
                position: fixed;
                bottom: 10px; /* Position it inside your body::before border */
                left: 30px;
                right: 30px;
                height: 60px;
                z-index: 1000;
                text-align: center;
                font-size: 8pt;
            }

            .print-footer p {
                margin: 0;
            }

            /* Force the footer gap to repeat on every page */
            tfoot {
                display: table-footer-group;
            }

            .footer-content p {
                margin: 2px 0;
            }

            .print-body {
                position: static !important;
                width: 100%;
                /* 1. Remove the 150px margin you had.
                   2. Use padding to ensure the Customer Details start under the header on Page 1. */
                margin: 0 !important;
                padding: 5px 30px 60px 30px !important;
            }

            .charges-table {
                width: 100%;
                display: table;
                border-collapse: collapse;
                /* THE KEY: Negative margin equal to the spacer height.
                   This pulls the table UP on Page 1, overlapping the spacer.
                   On Page 2, the table doesn't "restart," so the spacer works. */
                margin-top: -138px !important;
            }

            thead {
                display: table-header-group;
            }

            .print-spacer {
                display: table-row !important;
                height: 138px !important;
            }

            /* Standard row styling */
            .charges-table tbody tr {
                page-break-inside: avoid;
            }

            body::before {
                content: "";
                position: fixed;
                top: 30px;
                bottom: 30px;
                left: 30px;
                right: 30px;
                border: 1px solid #000;
                z-index: 9999;
                pointer-events: none;
            }
        }


        /* Hide spacer on regular screen view */
        @media screen {
            .header-spacer {
                display: none;
            }
        }
    </style>
</head>
<body>
<table>
    <thead>
    <tr>
        <th style="height: 138px">
            <div class="print-header">
                <div class="header-top border-bottom border-dark p-2 pt-3">
                    <div class="logo-box"><img src="{{ asset($company->logo_path) }}"></div>
                    <div class="address-box">
                        <h6 class="mb-0 pb-0 fw-bold">{{ $company->name }}</h6>
                        <h6 class="mb-0 arabic-name">{{ $company->name_ar }}</h6>
                        <p class="fw-bold">{{ $company->address_1 }}</p>
                        <p class="fw-bold">{{ $company->city }} {{ $company->postal_code }}
                            , {{ $company->city_sub_division }}</p>
                        <p class="fw-bold">SAUDI ARABIA</p>
                        @if($company->tax_number)
                            <p class="fw-bold">CR NO: {{ $company->cr_number }}, VAT NO: {{ $company->tax_number }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <div class="print-body">
                @if($customerInvoice->status == 1)
                    <div class="draft-watermark">DRAFT</div>
                @endif
                <div class="invoice-title-row border-bottom border-dark">
                    <h2 class="english-title text-center">TAX INVOICE</h2>
                    <h2 class="arabic-title text-center">فاتورة ضريبية</h2>
                </div>

                <div class="details-section">

                    <div class="customer-address-split border-bottom border-dark">
                        <div class="customer-address-english customer-split-flex">
                            <div class="customer-label-col">
                                <p class="customer-name-bold mb-0 px-1">Customer:</p>
                            </div>
                            <div class="customer-details-col">
                                <p class="customer-name-bold text-uppercase">{{ $customerInvoice->customer->name_en }}</p>
                                <p class="text-uppercase">{{ $customerInvoice->customer->address1_en }}</p>
                                <p class="text-uppercase">{{ $customerInvoice->customer->city_en }}
                                    , {{ $customerInvoice->customer->country }}
                                    , {{ $customerInvoice->customer->postal_code }}</p>
                                <p>Phone: {{ $customerInvoice->customer->phone }}</p>
                                <p style="font-weight: 700;">VAT No.: {{ $customerInvoice->customer->vat_number }}
                                    /{{ $customerInvoice->customer->cr_number }}</p>
                                <p>Credit Term: CASH</p>
                            </div>
                        </div>

                        <div class="customer-address-arabic customer-split-flex">
                            <div class="customer-details-col">
                                <p class="customer-name-bold">{{ $customerInvoice->customer->name_ar }}</p>
                                <p>{{ $customerInvoice->customer->address1_ar }}</p>
                                {{--<p style="font-weight: 700;">شركة محمد هادي الرشيد</p>
                                <p>2865, طريق فرع الدمام, الرياض, الرياض,</p>
                                <p>المملكة العربية السعودية, 13242</p>--}}
                                <p>{{ $customerInvoice->customer->city_ar }}, العربية
                                    السعودية, {{ $customerInvoice->customer->postal_code }}</p>
                                <p>{{ $customerInvoice->customer->phone }} هاتف: </p>
                                <p>رقم ضريبة القيمة المضافة للعميل: {{ $customerInvoice->customer->vat_number }}
                                    /{{ $customerInvoice->customer->cr_number }}</p>
                            </div>
                            <div class="customer-label-col">
                                <p class="mb-0">العميل:</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-bottom border-dark">
                        <div class="metadata-grid-row">
                            <div class="label-english">Customer VAT No.:</div>
                            <div class="data-value">{{ $customerInvoice->customer->vat_number }}
                                /{{ $customerInvoice->customer->cr_number }}</div>
                            <div class="label-arabic">رقم ضريبة القيمة المضافة للعميل:</div>
                        </div>
                        <div class="metadata-grid-row">
                            <div class="label-english">Invoice No.:</div>
                            <div class="data-value">{{ $customerInvoice->row_no }}</div>
                            <div class="label-arabic">رقم الفاتورة:</div>
                        </div>
                        <div class="metadata-grid-row">
                            <div class="label-english">Invoice Date:</div>
                            <div
                                class="data-value text-uppercase">{{ \Carbon\Carbon::parse($customerInvoice->invoice_date)->format('d-M-y') }}
                            </div>
                            <div class="label-arabic">تاريخ:</div>
                        </div>
                        <div class="metadata-grid-row">
                            <div class="label-english">Payment Due Date:</div>
                            <div
                                class="data-value text-uppercase">{{ \Carbon\Carbon::parse($customerInvoice->due_date)->format('d-M-y') }}</div>
                            <div class="label-arabic">تاريخ الاستحقاق للدفع:</div>
                        </div>
                    </div>
                </div>

                @php
                    $job = $customerInvoice->job;
                    $rightTD = 0;
                @endphp

                <div class="shipping-job-section">
                    <table>
                        <tbody>
                        <tr>
                            <td class="label-col">Shipper</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->shipper }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">الشاحن</td>
                        </tr>
                        <tr>
                            <td class="label-col">Consignee</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->consignee }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">المرسل إليه</td>
                        </tr>
                        <tr>
                            <td class="label-col">HBL No</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->hbl_no }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">رقم بوليصة الشحن الفرعية</td>
                        </tr>
                        <tr>
                            <td class="label-col">Place of Origin</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->pol }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">بلد المنشأ</td>
                        </tr>
                        <tr>
                            <td class="label-col">Final destination</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->pod }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">الوجهة النهائية</td>
                        </tr>
                        <tr>
                            <td class="label-col">Vessel / Flight</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->carrier }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">الناقل / الرحلة</td>
                        </tr>
                        <tr>
                            <td class="label-col">Voyage/Flight No.</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->voyage_flight_no }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">رقم الرحلة</td>
                        </tr>
                        <tr>
                            <td class="label-col">Shipper Ref No.</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->shipping_ref }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">رقم مرجع الشاحن</td>
                        </tr>
                        <tr>
                            <td class="label-col">Customer P/O No.</td>
                            <td class="separator-col">:</td>
                            <td class="data-col"></td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">رقم طلب الشراء</td>
                        </tr>
                        <tr>
                            <td class="label-col">Remarks</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->remarks }}</td>
                            <td class="text-end text-muted small" lang="ar" dir="rtl">ملاحظات</td>
                        </tr>
                        </tbody>
                    </table>
                    <div style="border-right: 1px solid #000"></div>
                    <table>
                        <tbody>
                        <tr>
                            <td class="label-col">Job Number</td>
                            <td class="separator-col">:</td>
                            <td class="data-col">{{ $job->row_no }}
                                / {{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                            <td class="text-end" lang="ar" dir="rtl">رقم الوظيفة</td>
                        </tr>
                        <tr>
                            <td class="label-col">Job Date</td>
                            <td class="separator-col">:</td>
                            <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                            <td class="text-end" lang="ar" dir="rtl">تاريخ الوظيفة</td>
                        </tr>
                        <tr>
                            <td class="label-col">Master Number</td>
                            <td class="separator-col">:</td>
                            <td class="data-col text-uppercase">{{ $job->awb_no }}
                                / {{ \Carbon\Carbon::parse($job->posted_at)->format('d-M-y') }}</td>
                            <td class="text-end" lang="ar" dir="rtl">رقم البوليصة الرئيسي</td>
                        </tr>
                        <tr>
                            <td class="label-col">House No.</td>
                            <td class="separator-col">:</td>
                            <td class="data-col"></td>
                            <td class="text-end" lang="ar" dir="rtl">رقم بوليصة الشحن الفرعية</td>
                        </tr>

                        @if($job->no_of_pieces)
                            <tr>
                                <td class="label-col">Number of Packs</td>
                                <td class="separator-col">:</td>
                                <td class="data-col">{{ $job->no_of_pieces }}</td>
                                <td class="text-end" lang="ar" dir="rtl">عدد الطرود</td>
                            </tr>
                        @else
                            @php
                                $rightTD++
                            @endphp
                        @endif

                        @if($job->weight)
                            <tr>
                                <td class="label-col">Weight in Kgs</td>
                                <td class="separator-col">:</td>
                                <td class="data-col">{{ $job->weight }}</td>
                                <td class="text-end" lang="ar" dir="rtl">الوزن بالكيلو جرام</td>
                            </tr>
                        @else
                            @php
                                $rightTD++
                            @endphp
                        @endif

                        @if($job->volume)
                            <tr>
                                <td class="label-col">Volume in CBM</td>
                                <td class="separator-col">:</td>
                                <td class="data-col">{{ $job->volume }}</td>
                                <td class="text-end" lang="ar" dir="rtl">الحجم بالمتر المكعب</td>
                            </tr>
                        @else
                            @php
                                $rightTD++
                            @endphp
                        @endif

                        <tr>
                            <td class="label-col">ETD</td>
                            <td class="separator-col">:</td>
                            <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->etd)->format('d-M-y') }}</td>
                            <td class="text-end" lang="ar" dir="rtl">التاريخ المتوقع للمغادرة</td>
                        </tr>
                        <tr>
                            <td class="label-col">ETA</td>
                            <td class="separator-col">:</td>
                            <td class="data-col text-uppercase">{{ \Carbon\Carbon::parse($job->eta)->format('d-M-y') }}</td>
                            <td class="text-end" lang="ar" dir="rtl">التاريخ المتوقع للوصول</td>
                        </tr>
                        <tr>
                            <td class="label-col">Narration</td>
                            <td class="separator-col">:</td>
                            <td class="data-col"></td>
                            <td class="text-end" lang="ar" dir="rtl">سرد / بيان</td>
                        </tr>
                        @for($j=0;$j<=$rightTD;$j++)
                            <tr>
                                <td colspan="4"></td>
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
                <table class="charges-table">
                    <thead>
                    <tr class="header-spacer">
                        <th colspan="10"
                            style="height: 138px; border: none !important; padding: 0 !important; background: transparent !important;"></th>
                    </tr>
                    <tr>
                        <th class="charge-desc">Charge Description <br> بيان الرسوم</th>
                        <th style="width: 5%;">Curr. <br> العملة</th>
                        <th style="width: 8%;">Rate Per Unit <br> السعر لكل وحدة</th>
                        <th style="width: 5%;">Unit <br> وحدة</th>
                        <th style="width: 10%;">Curr. Amount <br> مبلغ العملة</th>
                        <th style="width: 5%;">/ROE <br> سعر الصرف</th>
                        <th style="width: 10%;">Total Price excl. VAT <br> السعر الجمالي بدون الضريبة</th>
                        <th style="width: 5%;">VAT% <br> الضربة %</th>
                        <th style="width: 10%;">VAT Amount <br> قيمة الضريبة</th>
                        <th style="width: 12%;">Total <br> المجموع {{ $customerInvoice->currency }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customerInvoice->customerInvoiceSubs as $k => $items)
                        <tr>
                            <td class="charge-desc text-uppercase">{{ $items->description }} @if($items->comment)
                                    - <span>{{ $items->comment }}</span>
                                @endif</td>
                            <td>{{ $customerInvoice->currency }}</td>
                            <td>{{ amountFormat($items->unit_price) }}</td>
                            <td>{{ $items->quantity }}</td>
                            <td>{{ amountFormat($items->total) }}</td>
                            <td>1.000000</td>
                            <td>{{ amountFormat($items->total) }}</td>
                            <td>{{ $items->tax_percent }}</td>
                            <td>{{ amountFormat($items->tax_amount) }}</td>
                            <td>{{ amountFormat($items->total_with_tax) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="page">
                    <div class="total-summary-section">
                        <div class="total-summary-row">

                            <div class="text-and-label-col">
                                <div class="words-group">
                                    <div
                                        class="english-text">{{ amountInWords(round($customerInvoice->grand_total, 2)) }}</div>
                                    <div
                                        class="arabic-text">{{ convert(round($customerInvoice->grand_total, 2)) }}</div>
                                </div>
                                <div class="label-group">
                                    <div class="english-text">Total in: {{ $customerInvoice->currency }}</div>
                                    @if(authUserCompany()->currency != $customerInvoice->currency)
                                        <div class="english-text">Total in: {{ authUserCompany()->currency }}
                                            / {{ $customerInvoice->currency_rate }}</div>
                                    @endif
                                    <div class="arabic-text">الإجمالي بـ: {{ $customerInvoice->currency }}</div>
                                </div>
                            </div>
                            @if(authUserCompany()->currency != $customerInvoice->currency)
                                <div class="label-group col-total-excl-vat">
                                    <div class="english-text">{{ amountFormat($customerInvoice->sub_total) }}</div>
                                    <div class="english-text">{{ amountFormat($customerInvoice->base_sub_total) }}</div>
                                    <div
                                        class="arabic-text">{{ toArabicNumber($customerInvoice->base_sub_total) }}</div>
                                </div>
                                <div class="col-vat-percent"></div>
                                <div class="label-group col-vat-amount">
                                    <div class="english-text">{{ amountFormat($customerInvoice->tax_total) }}</div>
                                    <div class="english-text">{{ amountFormat($customerInvoice->base_tax_total) }}</div>
                                    <div
                                        class="arabic-text">{{ toArabicNumber($customerInvoice->base_tax_total ?? '0.00') }}</div>
                                </div>
                                <div class="label-group col-final-total">
                                    <div class="english-text">{{ amountFormat($customerInvoice->grand_total) }}</div>
                                    <div
                                        class="english-text">{{ amountFormat($customerInvoice->base_grand_total) }}</div>
                                    <div
                                        class="arabic-text">{{ toArabicNumber($customerInvoice->base_grand_total) }}</div>
                                </div>
                            @else
                                <div class="label-group col-total-excl-vat">
                                    <div class="english-text">{{ amountFormat($customerInvoice->sub_total) }}</div>
                                    <div class="arabic-text">{{ toArabicNumber($customerInvoice->sub_total) }}</div>
                                </div>
                                <div class="col-vat-percent"></div>
                                <div class="label-group col-vat-amount">
                                    <div class="english-text">{{ amountFormat($customerInvoice->tax_total) }}</div>
                                    <div
                                        class="arabic-text">{{ toArabicNumber($customerInvoice->tax_total ?? '0.00') }}</div>
                                </div>
                                <div class="label-group col-final-total">
                                    <div class="english-text">{{ amountFormat($customerInvoice->grand_total) }}</div>
                                    <div class="arabic-text">{{ toArabicNumber($customerInvoice->grand_total) }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(isset($jobContainers) && filled($jobContainers))
                    <div class="job-container-section">
                        <div class="job-container-title">Container Details</div>
                        <table>
                            <thead>
                            <tr>
                                <th>Container Number</th>
                                <th>No. of Containers</th>
                                <th>TYPE</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($jobContainers as $containerSize => $container)
                                <tr>
                                    <td>{{ $container['container_number'] }}</td>
                                    <td>{{ $container['qty'] ?? '' }}</td>
                                    <td>{{ containerSize($container['container_size']) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @if(isset($jobPackages) && filled($jobPackages))
                    <div class="mt-4 mx-1">
                        <table class="table table-bordered align-middle" style="font-size: 10px">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">Commodity</th>
                                <th scope="col" class="text-end">No. of Pcs</th>
                                <th scope="col">Pack Type</th>
                                <th scope="col" class="text-end">G.Weight</th>
                                <th scope="col">Unit</th>
                                <th scope="col">Volume (m3)</th>
                                <th scope="col">V.Weight</th>
                                <th scope="col" class="text-end">C.Weight</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($jobPackages as $package)
                                <tr>
                                    <td>{{ $package->description_goods }}</td>
                                    <td class="text-end">{{ $package->quantity }}</td>
                                    <td>{{ ucfirst($package->package_type) }}</td>
                                    <td class="text-end">{{ $package->package_weight }}</td>
                                    <td>KGS</td>
                                    <td>{{ $package->volume }}</td>
                                    <td>{{ round(($package->quantity * $package->length * $package->width * $package->height)/6000,3) }}</td>
                                    <td class="text-end">{{ $package->chargeable_weight }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <table style="font-size: 10px;width: 100%;border-collapse: collapse;">
                    <tbody>
                    <tr>
                        <td>
                            <div class="footer-section px-2">
                                <div class="bank-details">
                                    <p style="font-weight: 700; margin-bottom: 5px;">Bank
                                        Details</p>
                                    <table style="font-size: 10px">
                                        <tbody>
                                        <tr>
                                            <td>Account Name:</td>
                                            <td>{{ $bank->account_holder }}</td>
                                        </tr>
                                        <tr>
                                            <td>Account Name (Arabic):</td>
                                            <td>{{ $bank->account_holder_arabic }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bank Name:</td>
                                            <td>{{ $bank->bank_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Account No:</td>
                                            <td>{{ $bank->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>IBAN No:</td>
                                            <td>{{ $bank->iban_code }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bank Address:</td>
                                            <td>{{ $bank->bank_address }}</td>
                                        </tr>
                                        <tr>
                                            <td>SWIFT Code:</td>
                                            <td>{{ $bank->swift_code }}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                                @if ($company->zatca_registered && $customerInvoice->qr && false)
                                    <div width="200"
                                         height="auto">{{ SimpleSoftwareIO\QrCode\Facades\QrCode::size(135)->generate($customerInvoice->qr) }}</div>
                                @elseif ($company->currency == 'SAR')
                                    @php
                                        $currencyRate = 1;
                                            $generatedString = Salla\ZATCA\GenerateQrCode::fromArray([
                                            new Salla\ZATCA\Tags\Seller($company->name), // seller name
                                            new Salla\ZATCA\Tags\TaxNumber( $company->tax_number), // seller tax number
                                            new Salla\ZATCA\Tags\InvoiceDate(formDate($customerInvoice->invoice_date)."T".\Illuminate\Support\Carbon::parse($customerInvoice->created_at,'UTC')->format('H:i:s')).'Z',
                                            new Salla\ZATCA\Tags\InvoiceTotalAmount(amountFormat(($customerInvoice->total_amount + $customerInvoice->total_tax_amount)/$currencyRate)),
                                            new Salla\ZATCA\Tags\InvoiceTaxAmount(amountFormat($customerInvoice->total_tax_amount/$currencyRate))
                                            // TODO :: Support others tags
                                            ])->render();
                                    @endphp
                                    <img src="{{$generatedString}}" width="200" height="auto" alt="QR Code"/>

                                @endif
                                {{--@if ($company->zatca_registered && $customerInvoice->qr)
                                    <div width="200"
                                         height="auto">{{ SimpleSoftwareIO\QrCode\Facades\QrCode::size(135)->generate($customerInvoice->qr) }}</div>
                                @elseif ($company->currency == 'SAR')
                                    @php
                                        $currencyRate = 1;
                                            $generatedString = Salla\ZATCA\GenerateQrCode::fromArray([
                                            new Salla\ZATCA\Tags\Seller($company->name), // seller name
                                            new Salla\ZATCA\Tags\TaxNumber( $company->tax_number), // seller tax number
                                            new Salla\ZATCA\Tags\InvoiceDate(formDate($customerInvoice->invoice_date)."T".\Illuminate\Support\Carbon::parse($customerInvoice->created_at,$company->timezone)->format('H:i:s')), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
                                            new Salla\ZATCA\Tags\InvoiceTotalAmount(amountFormat(($customerInvoice->total_amount + $customerInvoice->total_tax_amount)/$currencyRate)), // invoice total amount
                                            new Salla\ZATCA\Tags\InvoiceTaxAmount(amountFormat($customerInvoice->total_tax_amount/$currencyRate)) // invoice tax amount
                                            // TODO :: Support others tags
                                            ])->render();
                                    @endphp
                                    <img src="{{$generatedString}}" width="200" height="auto" alt="QR Code"/>
                                @endif--}}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </td>
    </tr>
    </tbody>

    {{--<div class="invoice-wrapper">
        <div class="content-position">--}}

    <tfoot>
    <tr>
        <td style="height: 60px; border: none !important;">
            <div class="print-footer">
                <div>
                    <p>This is a computer generated document and does not require a signature</p>
                    <p class="arabic">هذا المستند تم انتاجه بواسطة الكمبيوتر ولا يحتاج الى توقيع .</p>
                </div>
            </div>
        </td>
    </tr>
    </tfoot>
</table>


{{--</div>
</div>--}}
</body>
</html>
