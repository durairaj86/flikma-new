<?php

$tax_total = <<<XML
<cac:TaxTotal>
        <cbc:TaxAmount currencyID="SAR">__158.67</cbc:TaxAmount>__TaxSubtotal
    </cac:TaxTotal>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="SAR">___tax_amount</cbc:TaxAmount>
    </cac:TaxTotal>
XML;

$tax_sub_total = <<<XML

        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="SAR">_46_TAXABLE</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="SAR">_6_TAX_AMOUNT</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5305">__S</cbc:ID>
                <cbc:Percent>_15_PERCENT</cbc:Percent>__TaxExemption
                <cac:TaxScheme>
                    <cbc:ID schemeAgencyID="6" schemeID="UN/ECE 5153">__VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
XML;

$tax_exemption = <<<XML

                <cbc:TaxExemptionReasonCode>_25_TAX_ERC</cbc:TaxExemptionReasonCode>
                <cbc:TaxExemptionReason>_35_TAX_ER</cbc:TaxExemptionReason>
XML;



return [
    'tax_total' => $tax_total,
    'tax_sub_total' => $tax_sub_total,
    'tax_exemption' =>$tax_exemption
];
