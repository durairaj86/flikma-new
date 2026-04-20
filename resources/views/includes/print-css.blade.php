<link rel="stylesheet" href="{{ asset('css/adminlte.css') }}">
<link rel="stylesheet" href="{{ asset('css/manual.css') }}">
<style>

    .invoice-wrapper {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        font-size: 1em;
        color: #000;
    }

    .table-invoice {
        width: 100%;
        border-collapse: collapse;
    }

    .table-invoice th, .table-invoice td {
        padding: 6px 8px;
        text-align: left;
        vertical-align: middle;
    }

    .table-invoice th {
        background: #f1f1f1;
        font-weight: 600;
    }

    .table-invoice td.text-end {
        text-align: right;
    }

    .total-section {
        margin-top: 20px;
    }

    .total-section table {
        width: 320px;
    }

    .total-section td {
        padding: 4px 8px;
    }

    .total-section tr:last-child td {
        border-top: 2px solid #000;
        font-weight: 600;
        font-size: 1rem;
    }

    .currency-note {
        font-size: 13px;
        margin-top: 5px;
        color: #444;
    }

    .terms-box {
        margin-top: 40px;
        border-top: 1px solid #ccc;
        padding-top: 10px;
    }

    .currency-note {
        font-size: 10px;
        color: #555;
        margin-top: 2px;
        font-weight: lighter;
    }

    .company-info h5 {
        margin-bottom: 4px;
        font-weight: 700;
    }

    .company-info small {
        line-height: 1.4;
        color: #555;
    }

    .invoice-title {
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        margin: 20px 0;
        padding: 6px 0;
        letter-spacing: 1px;
    }


    .action-buttons {
        text-align: right;
        margin-bottom: 20px;
    }

    .qr-section {
        margin-top: 40px;
        text-align: right;
    }

    .amount-words {
        margin-top: 8px;
        font-style: italic;
        font-size: 12px;
    }

    .draft-watermark {
        position: absolute;
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
        opacity: 0.5;
        display: none;
    }

    .invoice-wrapper > *:not(.draft-watermark) {
        position: relative;
        z-index: 1;
    }

    @media print {
        body {
            background: none !important;
            -webkit-print-color-adjust: exact;
        }

        .invoice-wrapper {
            border: none;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .btn {
            display: none !important;
        }

        .draft-watermark {
            display: block;
        }
    }

    #html-pdf .btn {
        display: none !important;
    }

    #html-pdf .draft-watermark {
        top: 50%;
        display: block;
    }
</style>
