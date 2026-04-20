<?php

namespace App\Traits\Zatca;

use App\Enums\TaxSubmit;
use App\Enums\Zatca;
use DOMDocument;

trait SimplifiedInvoice
{
    private $ZATCAPaymentMethods = [
        'CASH' => '10',
        'CREDIT' => '30',
        'BANK_ACCOUNT' => '42',
        'BANK_CARD' => '48'
    ];

    private $ZATCAInvoiceTypes = [
        'INVOICE' => 388,
        'DEBIT_NOTE' => 383,
        'CREDIT_NOTE' => 381,
    ];

    public function simplifiedTaxInvoice(array $invoice, array $egs_unit)
    {
        $populated_template = require __DIR__ . '/templates/simplified_tax_invoice_template.php';
        $populated_template = str_replace('SET_NAME_OF_INVOICE_TYPE', Zatca::INVOICE_TYPE_NAMES[$invoice['category']], trim($populated_template));
        $populated_template = str_replace('SET_INVOICE_TYPE', $this->ZATCAInvoiceTypes[$egs_unit['cancelation']['cancelation_type']], trim($populated_template));

        // if canceled (BR-KSA-56) set reference number to canceled invoice
        if (isset($egs_unit['cancelation']['canceled_invoice_number']) && $egs_unit['cancelation']['canceled_invoice_number']) {
            $populated_template = str_replace('SET_BILLING_REFERENCE', $this->defaultBillingReference($egs_unit['cancelation']['canceled_invoice_number']), $populated_template);
        } else {
            $populated_template = str_replace('SET_BILLING_REFERENCE', '', $populated_template);
        }

        $buyer_template = ($invoice['category'] == TaxSubmit::TAX_INVOICE_TEXT && isset($invoice['customer'])) ? $this->setBuyerAddress($invoice['customer']) : '';

        $populated_template = str_replace('SET_INVOICE_SERIAL_NUMBER', $invoice['invoice_serial_number'], $populated_template);
        $populated_template = str_replace('SET_TERMINAL_UUID', $egs_unit['uuid'], $populated_template);
        $populated_template = str_replace('SET_ISSUE_DATE', $invoice['issue_date'], $populated_template);
        $populated_template = str_replace('SET_ISSUE_TIME', $invoice['issue_time'], $populated_template);
        $populated_template = str_replace('SET_PREVIOUS_INVOICE_HASH', $invoice['previous_invoice_hash'], $populated_template);
        $populated_template = str_replace('SET_INVOICE_COUNTER_NUMBER', $invoice['invoice_counter_number'], $populated_template);
        $populated_template = str_replace('SET_COMMERCIAL_REGISTRATION_NUMBER', $egs_unit['CRN_number'], $populated_template);

        $populated_template = str_replace('SET_STREET_NAME', $egs_unit['location']['street'], $populated_template);
        $populated_template = str_replace('SET_BUILDING_NUMBER', $egs_unit['location']['building'], $populated_template);
        $populated_template = str_replace('SET_PLOT_IDENTIFICATION', $egs_unit['location']['plot_identification'], $populated_template);
        $populated_template = str_replace('SET_CITY_SUBDIVISION', $egs_unit['location']['city_subdivision'], $populated_template);
        $populated_template = str_replace('SET_CITY', $egs_unit['location']['city'], $populated_template);
        $populated_template = str_replace('SET_POSTAL_NUMBER', $egs_unit['location']['postal_zone'], $populated_template);

        $populated_template = str_replace('SET_VAT_NUMBER', $egs_unit['VAT_number'], $populated_template);
        $populated_template = str_replace('SET_VAT_NAME', $egs_unit['VAT_name'], $populated_template);
        $parseLineItems = $this->parseLineItems($invoice['line_items'], $egs_unit['cancelation']);

        $populated_template = str_replace('PARSE_LINE_ITEMS', $parseLineItems, $populated_template);
        $populated_template = str_replace('SET_CUSTOMER_INFO', $buyer_template, $populated_template);

        $document = new DOMDocument();
        $document->loadXML($populated_template);
        return $document;
    }

    private function defaultBillingReference(string $invoice_number): string
    {
        $populated_template = require __DIR__ . '/templates/invoice_billing_reference_template.php';
        return str_replace('SET_INVOICE_NUMBER', $invoice_number, $populated_template);
    }

    private function setBuyerAddress($customer)
    {
        $buyerTemplate = require __DIR__ . '/templates/customer_account_template.php';
        $countryCode = countryCode($customer['country']);
        $buyerTaxTemplate = $countryCode == 'SA' ? $buyerTemplate['buyerTaxScheme'] : null;

        $buyerTemplate = str_replace('__SET_BUYER_TAX_SCHEME', $buyerTaxTemplate, $buyerTemplate['buyerInfo']);
        $buyerTemplate = str_replace('SET_BUYER_NAME', str_replace('&', '&amp;', $customer['name_en']), $buyerTemplate);
        $buyerTemplate = str_replace('SET_STREET_NAME', str_replace('&', '&amp;', $customer['address1_en']), $buyerTemplate);
        $buyerTemplate = str_replace('SET_BUILDING_NUMBER', $customer['building_number'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_PLOT_IDENTIFICATION', $customer['plot_no'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_CITY_SUBDIVISION', $customer['city_sub_division'] ?? $customer['city_en'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_CITY', $customer['city_en'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_POSTAL_NUMBER', $customer['postal_code'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_COUNTRY_CODE', $countryCode, $buyerTemplate);
        $buyerTemplate = str_replace('SET_BUYER_VAT_NUMBER', $customer['vat_number'], $buyerTemplate);
        $buyerTemplate = str_replace('SET_CR_NUMBER', $customer['cr_number'], $buyerTemplate);

        return $buyerTemplate;
    }

    private function parseLineItems(array $line_items, array $cancelation)
    {
        // BT-110
        $total_taxes = 0;
        $total_subtotal = 0;
        $total_out_of_scope = 0;
        $invoice_line_items = [];
        array_map(function ($line_item) use (&$total_taxes, &$total_subtotal, &$invoice_line_items, &$total_out_of_scope) {
            [$line_item_xml, $line_item_totals] = $this->constructLineItem($line_item);
            $invoice_line_items[] = $line_item_xml;
            if (in_array($line_item['service_type'], ['STANDARD', 'ZERO'])) {
                $total_taxes += round($line_item_totals['taxes_total'], 2);
                $total_subtotal += round((float)$line_item_totals['subtotal'], 2);
            } else {
                $total_out_of_scope += ($line_item['tax_exclusive_price'] * $line_item['quantity']);
            }
        }, $line_items);
        $payment_means_template = '';
        //dd($cancelation);
        if ($cancelation['cancelation_type'] == 'CREDIT_NOTE' || $cancelation['cancelation_type'] == 'DEBIT_NOTE') {
            // Invoice canceled. Tunred into credit/debit note. Must have PaymentMeans
            // BR-KSA-17
            $payment_means_template = require __DIR__ . '/templates/creditnote_template.php';
        }

        /*if(props.cancelation) {
            // Invoice canceled. Tunred into credit/debit note. Must have PaymentMeans
            // BR-KSA-17
            $this->invoice_xml.set('Invoice/cac:PaymentMeans', false, {
                'cbc:PaymentMeansCode': props.cancelation.payment_method,
                'cbc:InstructionNote': props.cancelation.reason ?? 'No note Specified'
            });
        }*/

        /*
         * <cac:TaxTotal>
         *      </cac:TaxSubtotal> ...
         * set invoice lines
         */
        $tax_total_template = require __DIR__ . '/templates/tax_total_template.php';

        $item_lines = $this->constructTaxTotal($line_items);
        $groupOutOfScope = [];
        foreach ($item_lines[0]['cac:TaxSubtotal'] as $line_item) {
            if ($line_item['cac:TaxCategory']['cbc:TaxExemptionReasonCode'] == 'VATEX-SA-OOS') {
                if (isset($groupOutOfScope['VATEX-SA-OOS'])) {
                    $groupOutOfScope['VATEX-SA-OOS']['cbc:TaxableAmount']['#text'] += $line_item['cbc:TaxableAmount']['#text'];
                } else {
                    $groupOutOfScope['VATEX-SA-OOS'] = $line_item;
                }
            } elseif ($line_item['cac:TaxCategory']['cbc:TaxExemptionReasonCode'] == 'VATEX-SA-33') {
                if (isset($groupOutOfScope['VATEX-SA-33'])) {
                    $groupOutOfScope['VATEX-SA-33']['cbc:TaxableAmount']['#text'] += $line_item['cbc:TaxableAmount']['#text'];
                } else {
                    $groupOutOfScope['VATEX-SA-33'] = $line_item;
                }
            } elseif ($line_item['cac:TaxCategory']['cbc:TaxExemptionReasonCode'] == 'VATEX-SA-29') {
                if (isset($groupOutOfScope['VATEX-SA-29'])) {
                    $groupOutOfScope['VATEX-SA-29']['cbc:TaxableAmount']['#text'] += $line_item['cbc:TaxableAmount']['#text'];
                } else {
                    $groupOutOfScope['VATEX-SA-29'] = $line_item;
                }
            } else {
                $groupOutOfScope[] = $line_item;
            }
        }
//dd($groupOutOfScope,$item_lines[0]['cac:TaxSubtotal']);
        $lines = '';
        foreach ($groupOutOfScope as $key => $line) {
            $l = $tax_total_template['tax_sub_total'];
            $s = $line['cac:TaxCategory']['cbc:ID']['#text'];
            $l = str_replace('_46_TAXABLE', $line['cbc:TaxableAmount']['#text'], $l);
            $l = str_replace('_6_TAX_AMOUNT', $line['cbc:TaxAmount']['#text'], $l);
            $l = str_replace('__S', $s, $l);
            $l = str_replace('_15_PERCENT', $line['cac:TaxCategory']['cbc:Percent'], $l);
            $exemption = $s == 'S' ? '' : $tax_total_template['tax_exemption'];
            $l = str_replace('__TaxExemption', $exemption, $l);
            $l = str_replace('_25_TAX_ERC', $line['cac:TaxCategory']['cbc:TaxExemptionReasonCode'], $l);
            $l = str_replace('_35_TAX_ER', $line['cac:TaxCategory']['cbc:TaxExemptionReason'], $l);
            $l = str_replace('__VAT', $line['cac:TaxCategory']['cac:TaxScheme']['cbc:ID']['#text'], $l);

            $lines .= $l;
        }
        $tax_total_template['tax_total'] = str_replace('__158.67', $item_lines[0]['cbc:TaxAmount']['#text'], $tax_total_template['tax_total']);
        $tax_total_template['tax_total'] = str_replace('___tax_amount', $item_lines[1]['cbc:TaxAmount']['#text'], $tax_total_template['tax_total']);
        $tax_total_template = str_replace('__TaxSubtotal', $lines, $tax_total_template['tax_total']);

        /*
         * <cac:LegalMonetaryTotal>
         * $legal_monetary_total_template tags set
         */
        $legal_monetary_total_template = require __DIR__ . '/templates/legal_monetary_total_template.php';

        $constructLegalMonetaryTotal = $this->constructLegalMonetaryTotal($total_subtotal, $total_taxes);

        $legal_monetary_total_template = str_replace('_LineExtensionAmount', $constructLegalMonetaryTotal['cbc:LineExtensionAmount']['#text'] + $total_out_of_scope, $legal_monetary_total_template);
        $legal_monetary_total_template = str_replace('_TaxExclusiveAmount', $constructLegalMonetaryTotal['cbc:TaxExclusiveAmount']['#text'] + $total_out_of_scope, $legal_monetary_total_template);
        $legal_monetary_total_template = str_replace('_TaxInclusiveAmount', $constructLegalMonetaryTotal['cbc:TaxInclusiveAmount']['#text'] + $total_out_of_scope, $legal_monetary_total_template);
        $legal_monetary_total_template = str_replace('_AllowanceTotalAmount', $constructLegalMonetaryTotal['cbc:AllowanceTotalAmount']['#text'], $legal_monetary_total_template);
        $legal_monetary_total_template = str_replace('_PrepaidAmount', $constructLegalMonetaryTotal['cbc:PrepaidAmount']['#text'], $legal_monetary_total_template);
        $legal_monetary_total_template = str_replace('_PayableAmount', $constructLegalMonetaryTotal['cbc:PayableAmount']['#text'] + $total_out_of_scope, $legal_monetary_total_template);

        /*
         * <cac:InvoiceLine> ...
         * set invoice lines
         */
        $invoice_line_template = require __DIR__ . '/templates/invoice_line_template.php';

        $delivery = "<cac:Delivery>
            <cbc:ActualDeliveryDate>" . date('Y-m-d') . "</cbc:ActualDeliveryDate>
        </cac:Delivery>";

        $invoice_line = '';
        foreach ($invoice_line_items as $item) {
            $invoice_line_template_copy = $invoice_line_template['invoice_line'];
            $invoice_line_template_copy = str_replace('__ID', $item['cbc:ID'], $invoice_line_template_copy);
            $invoice_line_template_copy = str_replace('__InvoicedQuantity', $item['cbc:InvoicedQuantity']['#text'], $invoice_line_template_copy);
            $invoice_line_template_copy = str_replace('__LineExtensionAmount', $item['cbc:LineExtensionAmount']['#text'], $invoice_line_template_copy);
            $invoice_line_template_copy = str_replace('__TaxAmount', $item['cac:TaxTotal']['cbc:TaxAmount']['#text'], $invoice_line_template_copy);
            $invoice_line_template_copy = str_replace('__RoundingAmount', $item['cac:TaxTotal']['cbc:RoundingAmount']['#text'], $invoice_line_template_copy);
            $invoice_line_template_copy = str_replace('__PriceAmount', $item['cac:Price']['cbc:PriceAmount']['#text'], $invoice_line_template_copy);

            $invoice_line_template_copy = str_replace('__Name', $item['cac:Item']['cbc:Name'], $invoice_line_template_copy);

            $iit = '';
            foreach ($item['cac:Item']['cac:ClassifiedTaxCategory'] as $ClassifiedTaxCategory) {
                $invoice_item_template = $invoice_line_template['invoice_item'];
                $invoice_item_template = str_replace('___S', $ClassifiedTaxCategory['cbc:ID'], $invoice_item_template);
                $invoice_item_template = str_replace('___Percent', $ClassifiedTaxCategory['cbc:Percent'], $invoice_item_template);

                $iit .= $invoice_item_template;
            }
            $invoice_line_template_copy = str_replace('ClassifiedTaxCategory', $iit, $invoice_line_template_copy);

            $ipt = '';
            foreach ($item['cac:Price']['cac:AllowanceCharge'] as $AllowanceCharge) {
                $invoice_price_template = $invoice_line_template['invoice_price'];
                $invoice_price_template = str_replace('___AllowanceChargeReason', $AllowanceCharge['cbc:AllowanceChargeReason'], $invoice_price_template);
                $invoice_price_template = str_replace('___Amount', $AllowanceCharge['cbc:Amount']['#text'], $invoice_price_template);

                $ipt .= $invoice_price_template;
            }
            $invoice_line_template_copy = str_replace('AllowanceCharge', $ipt, $invoice_line_template_copy);

            $invoice_line .= $invoice_line_template_copy;
        }

        return $delivery . $payment_means_template . $tax_total_template . $legal_monetary_total_template . $invoice_line;
    }

    private function constructLineItem($line_item): array
    {
        [
            $cacAllowanceCharges,
            $cacClassifiedTaxCategories, $cacTaxTotal,
            $line_item_total_tax_exclusive,
            $line_item_total_taxes,
            $line_item_total_discounts
        ] = $this->constructLineItemTotals($line_item);

        return [
            /*'line_item_xml' => */ [
                'cbc:ID' => $line_item['id'],
                'cbc:InvoicedQuantity' => [
                    '@_unitCode' => 'PCE',
                    '#text' => $line_item['quantity']
                ],
                // BR-DEC-23
                'cbc:LineExtensionAmount' => [
                    '@_currencyID' => 'SAR',
                    '#text' => number_format($line_item_total_tax_exclusive, 2, '.', '')
                ],
                'cac:TaxTotal' => $cacTaxTotal,
                'cac:Item' => [
                    'cbc:Name' => $line_item['name'],
                    'cac:ClassifiedTaxCategory' => $cacClassifiedTaxCategories
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => [
                        '@_currencyID' => 'SAR',
                        '#text' => $line_item['tax_exclusive_price']
                    ],
                    'cac:AllowanceCharge' => $cacAllowanceCharges
                ]
            ],
            /*'line_item_totals' => */ [
                'taxes_total' => $line_item_total_taxes,
                'discounts_total' => $line_item_total_discounts,
                'subtotal' => $line_item_total_tax_exclusive
            ]
        ];
    }

    private function constructLineItemTotals($line_item): array
    {
        $line_item_total_discounts = 0;
        $line_item_total_taxes = 0;

        $cacAllowanceCharges = [];

        $text = 'S';
        if ($line_item['VAT_percent'] == 0) {
            if ($line_item['service_type'] == 'EXEMPT') {
                $text = 'E';
            } elseif ($line_item['service_type'] == 'OUTOFSCOPE') {
                $text = 'O';
            } else {
                $text = 'Z';
            }
        }

        // VAT
        // BR-KSA-DEC-02
        $VAT = [
            //'cbc:ID' => $line_item['VAT_percent'] ? 'S' : 'O',
            'cbc:ID' => $text,
            // BT-120, KSA-121
            'cbc:Percent' => $line_item['VAT_percent'] ? (number_format($line_item['VAT_percent'] * 100, 2, '.', '')) : 0,
            //'cbc:Percent' => '',
            'cac:TaxScheme' => [
                'cbc:ID' => 'VAT'
            ],
        ];
        $cacClassifiedTaxCategories[] = $VAT;

        // Calc total discounts
        array_map(function ($discount) use (&$line_item_total_discounts, &$cacAllowanceCharges) {
            $line_item_total_discounts += $discount['amount'];
            $cacAllowanceCharges[] = [
                'cbc:ChargeIndicator' => 'false',
                'cbc:AllowanceChargeReason' => $discount['reason'],
                'cbc:Amount' => [
                    '@_currencyID' => 'SAR',
                    // BR-DEC-01
                    '#text' => number_format($discount['amount'], 2, '.', '')
                ]
            ];
        }, $line_item['discounts'] ?? []);


        // Calc item subtotal
        $line_item_subtotal = ($line_item['tax_exclusive_price'] * $line_item['quantity']) - $line_item_total_discounts;

        // Calc total taxes
        // BR-KSA-DEC-02
        $line_item_total_taxes = $line_item_total_taxes + ($line_item['VAT_percent'] >= 0 ? ($line_item_subtotal * $line_item['VAT_percent']) : 0);

            array_map(function ($tax) use (&$line_item_total_taxes, $line_item_subtotal, &$cacClassifiedTaxCategories) {
                $line_item_total_taxes = $line_item_total_taxes + (floatval($tax['percent_amount']) * $line_item_subtotal);
                $cacClassifiedTaxCategories[] = [
                    'cbc:ID' => 'S',
                    'cbc:Percent' => number_format($tax['percent_amount'] * 100, 2, '.', ''),
                    'cac:TaxScheme' => [
                        'cbc:ID' => 'VAT'
                    ]
                ];

            }, $line_item['other_taxes'] ?? [])[0] ?? [0, 0];

        $roundingAmount = round($line_item_subtotal, 2) + round($line_item_total_taxes, 2);

        // BR-KSA-DEC-03, BR-KSA-51
        $cacTaxTotal = [
            'cbc:TaxAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($line_item_total_taxes, 2, '.', '')
            ],
            'cbc:RoundingAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($roundingAmount, 2, '.', ''),
            ],
        ];

        return [
            $cacAllowanceCharges,
            $cacClassifiedTaxCategories, $cacTaxTotal,
            $line_item_subtotal,
            $line_item_total_taxes,
            $line_item_total_discounts
        ];
    }

    private function constructLegalMonetaryTotal(float $total_subtotal, float $total_taxes)
    {
        return [
            // BR-DEC-09
            'cbc:LineExtensionAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($total_subtotal, 2, '.', '')
            ],
            // BR-DEC-12
            'cbc:TaxExclusiveAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($total_subtotal, 2, '.', '')
            ],
            // BR-DEC-14, BT-112
            'cbc:TaxInclusiveAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($total_subtotal + $total_taxes, 2, '.', '')
            ],
            'cbc:AllowanceTotalAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => 0
            ],
            'cbc:PrepaidAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => 0
            ],
            // BR-DEC-18, BT-112
            'cbc:PayableAmount' => [
                '@_currencyID' => 'SAR',
                '#text' => number_format($total_subtotal + $total_taxes, 2, '.', '')
            ]
        ];
    }

    private function constructTaxTotal(array $line_items)
    {
        $totalTaxableAmount = 0;
        /*foreach (collect($line_items)->where('service_type', 5) as $line_item) {
            $totalTaxableAmount += $line_item['quantity'] * $line_item['tax_exclusive_price'];
        }*/
        $cacTaxSubtotal = [];
        // BR-DEC-13, MESSAGE : [BR-DEC-13]-The allowed maximum number of decimals for the Invoice total VAT amount (BT-110) is 2.
        $addTaxSubtotal = function ($taxable_amount, $tax_amount, $tax_percent, $service_type) use (&$cacTaxSubtotal, $totalTaxableAmount) {
            //dd($tax_percent);
            $text = 'S';
            $reasonCode = '';
            if ($tax_percent == 0) {
                if ($service_type == 'EXEMPT') {
                    $reasonCode = 'VATEX-SA-29';
                    $text = 'E';
                } /*elseif ($service_type == 5) {
                    $reasonCode = 'VATEX-SA-OOS';
                    $text = 'O';
                    //$taxable_amount = $totalTaxableAmount;
                }*/ elseif ($service_type == 'OUTOFSCOPE') {
                    $reasonCode = 'VATEX-SA-OOS';
                    $text = 'O';
                    //$taxable_amount = $totalTaxableAmount;//old code
                    //$taxable_amount = $totalTaxableAmount;
                } elseif ($service_type == 'STANDARD') {
                    $reasonCode = '';
                    $text = 'Z';
                } else {
                    $reasonCode = 'VATEX-SA-33';
                    $text = 'Z';
                }
            }
            $cacTaxSubtotal[] = [
                // BR-DEC-19
                'cbc:TaxableAmount' => [
                    '@_currencyID' => 'SAR',
                    '#text' => number_format((float)($taxable_amount), 2, '.', '')
                ],
                'cbc:TaxAmount' => [
                    '@_currencyID' => 'SAR',
                    '#text' => number_format((float)($tax_amount), 2, '.', '')
                ],
                'cac:TaxCategory' => [
                    'cbc:ID' => [
                        '@_schemeAgencyID' => 6,
                        '@_schemeID' => 'UN/ECE 5305',
                        '#text' => $text
                        //'#text' => 'Z'
                    ],
                    'cbc:Percent' => $tax_percent ? number_format((float)$tax_percent * 100.00, 2, '.', '') : 0,
                    //'cbc:Percent' => '',
                    // BR-O-10
                    'cbc:TaxExemptionReason' => $tax_percent ? '' : salesTypes()[$service_type],
                    'cbc:TaxExemptionReasonCode' => $reasonCode,
                    'cac:TaxScheme' => [
                        'cbc:ID' => [
                            '@_schemeAgencyID' => 6,
                            '@_schemeID' => 'UN/ECE 5153',
                            '#text' => 'VAT'
                            //'#text' => $tax_percent ? 'VAT' : 'VATEX-SA-OOS'
                        ]
                    ],
                ]
            ];
        };

        $taxes_total = 0;

        array_map(function ($line_item) use (&$addTaxSubtotal, &$taxes_total) {
            $total_line_item_discount = array_reduce($line_item['discounts'], function ($p, $c) {
                return $p + $c['amount'];
            }, 0);
            $taxable_amount = ($line_item['tax_exclusive_price'] * $line_item['quantity']) - ($total_line_item_discount ?? 0);

            $tax_amount = ((float)$line_item['VAT_percent']) * ((float)$taxable_amount);
            $addTaxSubtotal($taxable_amount, $tax_amount, $line_item['VAT_percent'], $line_item['service_type']);
            $taxes_total += round($tax_amount, 2);
            array_map(function ($tax) use (&$taxable_amount, &$addTaxSubtotal, &$taxes_total, &$service_type) {
                $tax_amount = $tax['percent_amount'] * $taxable_amount;
                $addTaxSubtotal($taxable_amount, $tax_amount, $tax['percent_amount'], $service_type);
                $taxes_total += $tax_amount;
            }, $line_item['other_taxes']);
        }, $line_items);

        // BT-110
        $taxes_total = number_format($taxes_total, 2, '.', '');

        // BR-DEC-13, MESSAGE : [BR-DEC-13]-The allowed maximum number of decimals for the Invoice total VAT amount (BT-110) is 2.
        return [
            [
                // Total tax amount for the full invoice
                'cbc:TaxAmount' => [
                    '@_currencyID' => 'SAR',
                    '#text' => $taxes_total
                ],
                'cac:TaxSubtotal' => $cacTaxSubtotal,
            ],
            [
                // KSA Rule for VAT tax
                'cbc:TaxAmount' => [
                    '@_currencyID' => 'SAR',
                    '#text' => $taxes_total
                ]
            ]
        ];
    }
}
