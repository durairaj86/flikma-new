<?php

$buyerInfo = <<<XML

        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="CRN">SET_CR_NUMBER</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>SET_BUYER_NAME</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>SET_STREET_NAME</cbc:StreetName>
                <cbc:BuildingNumber>SET_BUILDING_NUMBER</cbc:BuildingNumber>
                <cbc:PlotIdentification>SET_PLOT_IDENTIFICATION</cbc:PlotIdentification>
                <cbc:CitySubdivisionName>SET_CITY_SUBDIVISION</cbc:CitySubdivisionName>
                <cbc:CityName>SET_CITY</cbc:CityName>
                <cbc:PostalZone>SET_POSTAL_NUMBER</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>SET_COUNTRY_CODE</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>__SET_BUYER_TAX_SCHEME
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>SET_BUYER_NAME</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
XML;

$buyerTaxScheme = <<<XML

            <cac:PartyTaxScheme>
                <cbc:RegistrationName>SET_BUYER_NAME</cbc:RegistrationName>
                <cbc:CompanyID>SET_BUYER_VAT_NUMBER</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
XML;

return [
    'buyerInfo' => $buyerInfo,
    'buyerTaxScheme' => $buyerTaxScheme,
];
