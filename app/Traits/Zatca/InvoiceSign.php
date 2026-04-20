<?php

namespace App\Traits\Zatca;

//use Carbon\Carbon;
use DOMDocument;
use Illuminate\Support\Facades\Storage;

trait InvoiceSign
{
    use SimplifiedInvoice;

    public function signInvoice(array $invoice, array $egs_unit, string $certificate, string $private_key)
    {
        $invoice_xml = $this->simplifiedTaxInvoice($invoice, $egs_unit);
        $invoice_hash = $this->getInvoiceHash($invoice_xml);
        [$hash, $issuer, $serialNumber, $public_key, $signature]
            = $this->getCertificateInfo($certificate);
        $digital_signature = $this->createInvoiceDigitalSignature($invoice_hash, $private_key);
        $qr = $this->generateQR(
            $invoice_xml,
            $digital_signature,
            $public_key,
            $signature,
            $invoice_hash
        );

        $serialNumberInteger = 0;
        $len = strlen($serialNumber);
        for ($i = 1; $i <= $len; $i++) {
            $serialNumberInteger = bcadd($serialNumberInteger, bcmul(strval(hexdec($serialNumber[$i - 1])), bcpow('16', strval($len - $i))));
        }

        //date_default_timezone_set("Asia/Riyadh");
        $signed_properties_props = [
            'sign_timestamp' => date('Y-m-d\TH:i:s\Z'),
            'certificate_hash' => $hash, // SignedSignatureProperties/SigningCertificate/CertDigest/<ds:DigestValue>SET_CERTIFICATE_HASH</ds:DigestValue>
            'certificate_issuer' => $issuer,
            'certificate_serial_number' => $serialNumberInteger
        ];

        $ubl_signature_signed_properties_xml_string_for_signing = $this->defaultUBLExtensionsSignedPropertiesForSigning($signed_properties_props);

        //$ubl_signature_signed_properties_xml_string = $this->defaultUBLExtensionsSignedProperties($signed_properties_props);

        $signed_properties_hash = base64_encode(openssl_digest($ubl_signature_signed_properties_xml_string_for_signing, 'sha256'));
        //$signed_properties_hash = base64_encode(hash('sha256', trim($ubl_signature_signed_properties_xml_string_for_signing)));
        //$signed_properties_hash = base64_encode(Hash::make($ubl_signature_signed_properties_xml_string_for_signing));

        // UBL Extensions
        $ubl_signature_xml_string = $this->defaultUBLExtensions(
            $invoice_hash, // <ds:DigestValue>SET_INVOICE_HASH</ds:DigestValue>
            $signed_properties_hash, // SignatureInformation/Signature/SignedInfo/Reference/<ds:DigestValue>SET_SIGNED_PROPERTIES_HASH</ds:DigestValue>
            $digital_signature,
            $certificate,
            $ubl_signature_signed_properties_xml_string_for_signing
        );

        // Set signing elements
        $unsigned_invoice_str = $invoice_xml->saveXML();
        $unsigned_invoice_str = str_replace('SET_UBL_EXTENSIONS_STRING', $ubl_signature_xml_string, $unsigned_invoice_str);
        $unsigned_invoice_str = str_replace('SET_QR_CODE_DATA', $qr, $unsigned_invoice_str);

        /////////////////////////

        $xml = new \DOMDocument("1.0", "utf-8");
        $xml->loadXML($unsigned_invoice_str);// invoice file after populate the properties;

        //use domPath to register this namespace
        $xpath = new \DOMXPath($xml);

        // register namespace
        $xpath->registerNamespace('default-ns', "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2");
        $xpath->registerNamespace('sig', "urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2");
        $xpath->registerNamespace('sac', "urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2");
        $xpath->registerNamespace('sbc', "urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2");
        $xpath->registerNamespace('ds', "http://www.w3.org/2000/09/xmldsig#");
        $xpath->registerNamespace('xades', "http://uri.etsi.org/01903/v1.3.2#");

        // path of SignedProperties
        $SignedProperties = "//default-ns:Invoice/ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sig:UBLDocumentSignatures/sac:SignatureInformation/ds:Signature/ds:Object/xades:QualifyingProperties/xades:SignedProperties";
        // get SignedProperties by path query
        $SignedPropertiesValue = $xpath->query($SignedProperties);
        // convert SignedProperties node to c14n standerd.
        //dd($SignedPropertiesValue[0]->C14N(\true));
        $canonicalizationInvoiceXML = $SignedPropertiesValue[0]->C14N(\true);
        // replace tag to rquired in zatca.
        $canonicalizationInvoiceXML = str_replace('/>', '></ds:DigestMethod>', $canonicalizationInvoiceXML);

        $signed_properties_hash = base64_encode(hash('sha256', $canonicalizationInvoiceXML));
        $signed_invoice_string = $xml->saveXML();
        $signed_invoice_string = str_replace('SET_SIGNED_PROPERTIES_HASH', $signed_properties_hash, $signed_invoice_string);

        //$signed_invoice_string = $signed_invoice->saveXML();
        //$signed_invoice_string = $this->signedPropertiesIndentationFix($signed_invoice_string);  //for test

        return [$signed_invoice_string, $invoice_hash, $qr];
    }

    public function getInvoiceHash(DOMDocument $invoice_xml): string
    {
        $pure_invoice_string = $this->getPureInvoiceString($invoice_xml);

        $pure_invoice_string = str_replace('<?xml version="1.0" encoding="UTF-8"?>' . "\n", '', $pure_invoice_string);
        $pure_invoice_string = str_replace('<cac:AccountingCustomerParty/>', '<cac:AccountingCustomerParty></cac:AccountingCustomerParty>', $pure_invoice_string);
        $pure_invoice_string = str_replace('<cbc:TaxExemptionReasonCode/>', '<cbc:TaxExemptionReasonCode></cbc:TaxExemptionReasonCode>', $pure_invoice_string);
        $pure_invoice_string = str_replace('<cbc:TaxExemptionReason/>', '<cbc:TaxExemptionReason></cbc:TaxExemptionReason>', $pure_invoice_string);
        $hash = hash('sha256', trim($pure_invoice_string));
//        dd(trim($pure_invoice_string),$pure_invoice_string,$hash);
        $hash = pack('H*', $hash);
        return base64_encode($hash);
    }

    private function getPureInvoiceString(DOMDocument $invoice_xml)
    {
        $document = new DOMDocument();
        $document->loadXML($invoice_xml->saveXML());

        while ($element = $document->getElementsByTagName('UBLExtensions')->item(0)) {
            $element->parentNode->removeChild($element);
        }

        while ($element = $document->getElementsByTagName('Signature')->item(0)) {
            $element->parentNode->removeChild($element);
        }

        $qrIndex = $document->getElementsByTagName('AdditionalDocumentReference')->length - 1;
        while ($element = $document->getElementsByTagName('AdditionalDocumentReference')->item($qrIndex)) { // qr code tag remove
            $element->parentNode->removeChild($element);
        }

        return $document->saveXML();
    }

    public function getCertificateInfo(string $certificate_string): array
    {
        $cleaned_certificate_string = $this->cleanUpCertificateString($certificate_string);
        $wrapped_certificate_string = "-----BEGIN CERTIFICATE-----\n{$cleaned_certificate_string}\n-----END CERTIFICATE-----";
        $hash = $this->getCertificateHash($cleaned_certificate_string);
        $x509 = openssl_x509_parse($wrapped_certificate_string);

        $res = openssl_get_publickey($wrapped_certificate_string);
        $cert = openssl_pkey_get_details($res);
        $public_key = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----'], '', $cert['key']);

        /*return [
            $hash,
            'CN=' . implode(', ', array_reverse($x509['issuer'])),
            $x509['serialNumber'],
            //$cert['key'],
            base64_decode($public_key),
            $this->getCertificateSignature($wrapped_certificate_string),
        ];*/
        if (isset($x509['issuer']['DC'])) {
            $cnTxt = 'CN=' . $x509['issuer']['CN'] . ', DC=' . implode(', DC=', array_reverse($x509['issuer']['DC']));
        } else {
            $cnTxt = 'CN=' . implode(', ', array_reverse($x509['issuer']));
        }
        return [
            $hash,
            $cnTxt,
            $x509['serialNumber'],
            //$cert['key'],
            base64_decode($public_key),
            $this->getCertificateSignature($wrapped_certificate_string),
        ];
    }

    public function getCertificateSignature(string $cer): string
    {
        /*$res = openssl_x509_read($cer);
        openssl_x509_export($res, $out, FALSE);

        $out = explode('Signature Algorithm:', $out);
        $out = explode('-----BEGIN CERTIFICATE-----', $out[2]);
        $out = explode("\n", $out[0]);
        $out = $out[1] . $out[2] . $out[3] . $out[4];
        $out = str_replace([':', ' '], '', $out);
        dd($out);

        return pack('H*', $out);*/
        $res = openssl_x509_read($cer);
        openssl_x509_export($res, $out, false);
        $out = explode('Signature Algorithm:', $out);
        $out = explode('ecdsa-with-SHA256', $out[2]);
        if (str_contains($out[1], 'Signature Value:')) {
            $out = explode('Signature Value:', $out[1]);
        }
        $out = explode('-----BEGIN CERTIFICATE-----', $out[1]);
        $out = explode("\n", $out[0]);
        $out = $out[1] . $out[2] . $out[3] . $out[4];
        $out = str_replace([':', ' '], '', $out);

        return pack('H*', $out);
    }

    public function createInvoiceDigitalSignature(string $invoice_hash, string $private_key)
    {
        $invoice_hash_bytes = base64_encode($invoice_hash);
        $cleanedup_private_key_string = $this->cleanUpPrivateKeyString($private_key);
        $wrapped_private_key_string = "-----BEGIN EC PRIVATE KEY-----\n{$cleanedup_private_key_string}\n-----END EC PRIVATE KEY-----";

        base64_encode(openssl_sign($invoice_hash_bytes, $binary_signature, $wrapped_private_key_string, 'sha256'));

        return base64_encode($binary_signature);
    }

    public static function cleanUpPrivateKeyString(string $private_key)
    {
        $private_key = str_replace('-----BEGIN EC PRIVATE KEY-----', '', $private_key);
        $private_key = str_replace('-----END EC PRIVATE KEY-----', '', $private_key);

        return trim($private_key);
    }

    public function generateQR(DOMDocument $invoice_xml, string $digital_signature, $public_key, $signature, string $invoice_hash)
    {
        //dd($digital_signature,$signature);
        // Extract required tags
        $seller_name = $invoice_xml->getElementsByTagName('AccountingSupplierParty')[0]
            ->getElementsByTagName('RegistrationName')[0]->textContent;

        $VAT_number = $invoice_xml->getElementsByTagName('CompanyID')[0]->textContent;

        $invoice_total = $invoice_xml->getElementsByTagName('TaxInclusiveAmount')[0]->textContent;

        $VAT_total = 0;
        if ($tax_amount = $invoice_xml->getElementsByTagName('TaxTotal')[0]) {
            $VAT_total = $tax_amount->getElementsByTagName('TaxAmount')[0]->textContent;
        }

        $issue_date = $invoice_xml->getElementsByTagName('IssueDate')[0]->textContent;
        $issue_time = $invoice_xml->getElementsByTagName('IssueTime')[0]->textContent;

        // Detect if simplified invoice or not (not used currently assuming all simplified tax invoice)
        //$invoice_type = $invoice_xml->getElementsByTagName('Invoice/cbc:InvoiceTypeCode')[0]['@_name'];

        $formatted_datetime = date('Y-m-d\TH:i:s\Z', strtotime("{$issue_date} {$issue_time}"));

        $qr_tlv = $this->TLV([
            $seller_name,
            $VAT_number,
            $formatted_datetime,
            $invoice_total,
            $VAT_total,
            $invoice_hash,
            $digital_signature,
            $public_key,
            $signature
        ]);

        return base64_encode($qr_tlv);
    }

    private function TLV(array $tags): string
    {
        $__toHex = function ($value) {
            return pack('H*', sprintf('%02X', $value));
        };

        $__toString = function ($__tag, $__value, $__length) use ($__toHex) {
            $value = (string)$__value;
            return $__toHex($__tag) . $__toHex($__length) . $value;
        };

        foreach ($tags as $i => $tag) {
            $__TLVS[] = $__toString($i + 1, $tag, strlen($tag));
        }

        return implode('', $__TLVS) ?? '';
    }

    public function defaultUBLExtensionsSignedPropertiesForSigning(array $signed_properties_props): string
    {
        $populated_template = require __DIR__ . '/templates/ubl_signature_signed_properties_for_signing_template.php';

        $populated_template = str_replace('SET_SIGN_TIMESTAMP', $signed_properties_props['sign_timestamp'], $populated_template);
        $populated_template = str_replace('SET_CERTIFICATE_HASH', $signed_properties_props['certificate_hash'], $populated_template);
        $populated_template = str_replace('SET_CERTIFICATE_ISSUER', $signed_properties_props['certificate_issuer'], $populated_template);
        $populated_template = str_replace('SET_CERTIFICATE_SERIAL_NUMBER', $signed_properties_props['certificate_serial_number'], $populated_template);

        return $populated_template;
    }

    public function defaultUBLExtensions(string $invoice_hash, string $signed_properties_hash, string $digital_signature, string $cleanUpCertificateString, string $ubl_signature_signed_properties_xml_string): string
    {
        $cleanUpCertificateString = $this->cleanUpCertificateString($cleanUpCertificateString);

        $populated_template = require __DIR__ . '/templates/ubl_signature.php';
        $populated_template = str_replace('SET_INVOICE_HASH', $invoice_hash, $populated_template);
        //$populated_template = str_replace('SET_SIGNED_PROPERTIES_HASH', $signed_properties_hash, $populated_template);
        $populated_template = str_replace('SET_DIGITAL_SIGNATURE', $digital_signature, $populated_template);
        $populated_template = str_replace('SET_CERTIFICATE', $cleanUpCertificateString, $populated_template);
        return str_replace('SET_SIGNED_PROPERTIES_XML', $ubl_signature_signed_properties_xml_string, $populated_template);
    }

    private function getCertificateHash($cleanup_certificate_string): string
    {
        //dd($cleanup_certificate_string);
        $hash = openssl_digest($cleanup_certificate_string, 'sha256');
        //$hash2 = hash('sha256', $cleanup_certificate_string); //temp
        //dd($hash,$hash2);
        //$hash = pack('H*', $hash);
        return base64_encode($hash);
    }

    public static function cleanUpCertificateString(string $certificate_string): string
    {
        $certificate_string = str_replace('-----BEGIN CERTIFICATE-----', '', $certificate_string);
        $certificate_string = str_replace('-----END CERTIFICATE-----', '', $certificate_string);

        return trim($certificate_string);
    }
}
