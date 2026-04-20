<?php

use App\Models\Master\TransportDirectory\Airport;
use App\Models\Master\TransportDirectory\CarrierLines;
use App\Models\Master\TransportDirectory\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function services($selected = null, $direct = null): array|string
{
    $services = [
        1 => 'Freight Forwarding',
        2 => 'Customs Clearance',
        3 => 'Transportation',
        4 => 'Warehousing',
        5 => 'Moving & Relocation',
        6 => 'Import/Export Trading',
        7 => 'Courier & Express Delivery',
    ];


    if ($selected && !$direct) {
        $names = array_intersect_key($services, array_flip($selected));
        return implode(', ', $names);
    } elseif ($selected && $direct) {
        return $services[$selected];
    }
    return $services;
}

function getSelectedServices($services, $nameOnly = null)
{
    $serviceArray = array_filter(array_intersect_key(services(), array_flip($services)));
    if ($nameOnly) {
        return implode(', ', $serviceArray);
    }
    return $serviceArray;
}

function containerTypes(): array
{
    return [
        'dry' => ['Dry' => 'Standard non-refrigerated container'],
        'reefer' => ['Reefer' => 'Temperature-controlled refrigerated container'],
        'open_top' => ['Open Top' => 'Container with removable top, for tall cargo'],
        'flat_rack' => ['Flat Rack' => 'Container with no sides, for oversized cargo'],
        'tank' => ['Tank' => 'Container for liquids or bulk materials'],
    ];
}

function containerTypesData(): array
{
    return [
        'dry' => 'Dry',
        'reefer' => 'Reefer',
        'open_top' => 'Open Top',
        'flat_rack' => 'Flat Rack',
        'tank' => 'Tank',
    ];
}


function containerSize($requiredData = null): array|string
{
    $size = [
        '20GP' => '20\' Standard (20GP)',
        '40GP' => '40\' Standard (40GP)',
        '40HC' => '40\' High Cube (40HC)',
        '45HC' => '45\' High Cube (45HC)',
        '20RF' => '20\' Reefer (20RF)',
        '40RF' => '40\' Reefer (40RF)',
        '40RH' => '40\' High Cube Reefer (40RH)',
        '20OT' => '20\' Open Top (20OT)',
        '40OT' => '40\' Open Top (40OT)',
        '20FR' => '20\' Flat Rack (20FR)',
        '40FR' => '40\' Flat Rack (40FR)',
        '20TK' => '20\' Tank (20TK)',
    ];
    return $requiredData ? $size[$requiredData] : $size;
}

function shipmentMode(): array
{
    return [
        'sea' => 'Sea',
        'air' => 'Air',
        /*'road' => 'Road',
        'courier' => 'Courier',*/
    ];
}

function shipmentCategory(): array
{
    return [
        'fcl' => 'FCL',
        'lcl' => 'LCL',
        'break-bulk' => 'Break bulk',
        'consolidation' => 'Consolidation'
    ];
}

function cargoRequirements(): array
{
    return [
        'DG' => 'DG',
        'SABER' => 'SABER',
        'CITC' => 'CITC',
        'MOI' => 'MOI',
        'SFDA' => 'SFDA',
        'MOE' => 'MOE',
        'PME' => 'PME',
        'EXCEMPTION' => 'EXCEMPTION',
    ];
}

function commodityType(): array
{
    return [
        'general' => 'General',
        'hazardous' => 'Hazardous',
        'perishable' => 'Perishable',
        'reefer' => 'Reefer',
        'oversized' => 'Oversized',
        'valuable' => 'Valuable',
    ];
}

function cargoTypes(): array
{
    return [
        'general' => ['General' => 'normal goods, non-special handling'],
        'hazardous' => ['Hazardous' => 'dangerous goods, requires DG declaration'],
        'perishable' => ['Perishable' => 'food, flowers, pharma – sensitive to time/temp'],
        'reefer' => ['Reefer' => 'temperature controlled containers'],
        'oversized' => ['Oversized' => 'Oversized / OOG (too big for standard containers)'],
        'valuable' => ['Valuable' => 'high value shipments – gold, cash, electronics']
    ];
}

function packageType(): array
{
    return [
        'box'      => 'Box',
        'carton'   => 'Carton',
        'crate'    => 'Crate',
        'pallet'   => 'Pallet',
        'pieces'   => 'Pieces',
        'bag'      => 'Bag',
        'sack'     => 'Sack',
        'drum'     => 'Drum',
        'barrel'   => 'Barrel',
        'bale'     => 'Bale',
        'roll'     => 'Roll',
        'bundle'   => 'Bundle',
        'case'     => 'Case',
        'tube'     => 'Tube',
        'package'  => 'Package',
    ];
}


function clearanceStatus(): array
{
    return [
        'pending' => 'Pending',
        'under-process' => 'Under Process',
        'cleared' => 'Cleared',
        'on-hold' => 'On Hold',
    ];
}

function countries(): array
{
    return [
        'SA' => 'Saudi Arabia',
        'US' => 'United States',
        'AF' => 'Afghanistan',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AS' => 'American Samoa',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antarctica',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BQ' => 'Bonaire, Sint Eustatius and Saba',
        'BA' => 'Bosnia and Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet Island',
        'BR' => 'Brazil',
        'IO' => 'British Indian Ocean Territory',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'CV' => 'Cabo Verde',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CX' => 'Christmas Island',
        'CC' => 'Cocos (Keeling) Islands',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo',
        'CD' => 'Congo (Democratic Republic)',
        'CK' => 'Cook Islands',
        'CR' => 'Costa Rica',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CW' => 'Curaçao',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'SZ' => 'Eswatini',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands (Malvinas)',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FI' => 'Finland',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'TF' => 'French Southern Territories',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HM' => 'Heard Island and McDonald Islands',
        'VA' => 'Holy See',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea (North)',
        'KR' => 'Korea (South)',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Lao People’s Democratic Republic',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macao',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'YT' => 'Mayotte',
        'MX' => 'Mexico',
        'FM' => 'Micronesia (Federated States of)',
        'MD' => 'Moldova',
        'MC' => 'Monaco',
        'MN' => 'Mongolia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'MM' => 'Myanmar',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'NC' => 'New Caledonia',
        'NZ' => 'New Zealand',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'NU' => 'Niue',
        'NF' => 'Norfolk Island',
        'MK' => 'North Macedonia',
        'MP' => 'Northern Mariana Islands',
        'NO' => 'Norway',
        'OM' => 'Oman',
        'PK' => 'Pakistan',
        'PW' => 'Palau',
        'PS' => 'Palestine, State of',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico',
        'QA' => 'Qatar',
        'RE' => 'Réunion',
        'RO' => 'Romania',
        'RU' => 'Russian Federation',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthélemy',
        'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
        'KN' => 'Saint Kitts and Nevis',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin (French part)',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SN' => 'Senegal',
        'RS' => 'Serbia',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SX' => 'Sint Maarten (Dutch part)',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'SB' => 'Solomon Islands',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'GS' => 'South Georgia and the South Sandwich Islands',
        'SS' => 'South Sudan',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard and Jan Mayen',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Tokelau',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Türkiye',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'GB' => 'United Kingdom',
        'UM' => 'United States Minor Outlying Islands',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VE' => 'Venezuela',
        'VN' => 'Viet Nam',
        'VG' => 'Virgin Islands (British)',
        'VI' => 'Virgin Islands (U.S.)',
        'WF' => 'Wallis and Futuna',
        'EH' => 'Western Sahara',
        'YE' => 'Yemen',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
    ];
}

function incoterms(): \Illuminate\Support\Collection
{
    return DB::table('incoterms')->select('code', 'name', 'description')->where('is_active', 1)->orderBy('name')->get();
}

function currencies(): \Illuminate\Support\Collection
{
    return DB::table('currencies')
        /*->select('code', 'name', 'country')*/
        ->orderByRaw("FIELD(code, 'USD', 'SAR') DESC") // put USD and SAR first
        ->orderBy('name') // then rest alphabetically
        ->get()->pluck('name', 'code');
}

function accountTypes(): array
{
    return [
        'Asset' => 'Asset',
        'Liability' => 'Liability',
        'Income' => 'Income',
        'Expense' => 'Expense',
        'Equity' => 'Equity',
    ];
}

function roles(): array
{
    return [
        1 => ['Super User' => 'normal goods, non-special handling'],
        2 => ['User' => 'dangerous goods, requires DG declaration'],
        3 => ['Admin' => 'food, flowers, pharma – sensitive to time/temp'],
        4 => ['Employee' => 'temperature controlled containers']
    ];
}

function roleDisplay($role)
{
    return match ($role) {
        1 => 'Super User',
        2 => 'User',
        3 => 'Admin',
        4 => 'Employee',
        default => 'Unknown', // optional fallback
    };
}

function vat(): array
{
    return [
        [
            'code' => 'STANDARD',
            'name' => 'Standard',
            'percent' => 15,
            'description' => 'Standard VAT rate 15% applied to taxable supplies'
        ],
        [
            'code' => 'ZERO',
            'name' => 'Zero-rated',
            'percent' => 0,
            'description' => 'VAT rate 0% — exports, basic foods, education, health services'
        ],
        [
            'code' => 'EXEMPT',
            'name' => 'Exempt',
            'percent' => 0,
            'description' => 'No VAT charged, cannot reclaim input VAT — e.g., financial services'
        ],
        /*[
            'code' => 'OTHERSALES',
            'name' => 'Other Sales',
            'percent' => 0,
            'description' => 'Special cases / adjustments'
        ],
        [
            'code' => 'REVERSE',
            'name' => 'Reverse Charge',
            'percent' => 15,
            'description' => 'Purchases from outside KSA where VAT is accounted by recipient'
        ],*/
        [
            'code' => 'OUTOFSCOPE',
            'name' => 'Out-of-scope',
            'percent' => 0,
            'description' => 'Transactions not subject to VAT under KSA law'
        ],
    ];
}

function vatPercent($vat): int
{
    return match ($vat) {
        'STANDARD' => 15,
        'ZERO' => 0,
        'EXEMPT' => 0,
        'OTHERSALES' => 0,
        'REVERSE' => 15,
        'OUTOFSCOPE' => 0,
        default => 0,
    };
}

function decimals(): int
{
    return 2;
}

function paymentModes(): array
{
    return [
        'cash' => 'cash',
        'cheque' => 'cheque',
        'bank' => 'bank',
    ];
}

function preloadPOLAndPOD($port = null): \Illuminate\Support\Collection
{
    $port = $port ?? 'sea';
    if ($port == 'sea') {
        $result = Port::select('id', 'name', 'code')->orderBy('name')->limit(50)->get();
    } elseif ($port == 'air') {
        $result = Airport::select('id', 'name', 'code')->orderBy('name')->limit(50)->get();
    }/* elseif ($port == 'carrier-sea') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Sea')->orderBy('name')->limit(50)->get();
    } elseif ($port == 'carrier-air') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Air')->orderBy('name')->limit(50)->get();
    }*/
    if ($result->count() == 0) {
        return ['No Data Found'];
    }
    return $result;

}

function defaultCarriers($carrier = null): \Illuminate\Support\Collection
{
    $carrier = $carrier ?? 'sea';
    if ($carrier == 'sea') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Sea')->orderBy('name')->limit(50)->get();
    } elseif ($carrier == 'air') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Air')->orderBy('name')->limit(50)->get();
    }
    if ($result->count() == 0) {
        return ['No Data Found'];
    }
    return $result;

}

function successResponse($message = 'Saved successfully', $data = [], $code = 200)
{
    $title = '';
    if (is_array($message)) {
        $title = $message[1];
        $message = $message[0];
    }
    $response = [
        'status' => 'success',
        'title' => __($title),
        'message' => __($message),
        'data' => $data,
    ];
    return response()->json($response, options: JSON_INVALID_UTF8_IGNORE);
}

function errorResponse($message = 'Error while processing data', $data = [], $code = 400)
{
    $title = '';
    if (is_array($message)) {
        $title = $message[1];
        $message = $message[0];
    }
    $response = [
        'status' => 'error',
        'title' => __($title),
        'message' => __($message),
        'data' => $data,
    ];

    return response()->json($response, $code);
}

function deleteResponse($message = 'Deleted Successfully', $data = [])
{
    $response = [
        'status' => 'delete',
        'message' => __($message),
        'data' => $data,
    ];

    return response()->json($response);
}

function salesTypes()
{
    return [
        'STANDARD' => 'Standard VAT rate 15% applied to taxable supplies',
        'ZERO' => 'VAT rate 0% — exports, basic foods, education, health services',
        'EXEMPT' => 'No VAT charged, cannot reclaim input VAT — e.g., financial services',
        'OUTOFSCOPE' => 'Transactions not subject to VAT under KSA law',
        'OTHERSALES' => 'Special cases / adjustments',
        'REVERSE' => 'Purchases from outside KSA where VAT is accounted by recipient',
    ];
}

function countryCode()
{
    return 'SA';
}

function industrialTypes(): array
{
    return [
        "Manufacturing" => "Manufacturing",
        "Construction" => "Construction",
        "Automotive" => "Automotive",
        "Textile" => "Textile",
        "Logistics" => "Logistics",
        "Warehousing" => "Warehousing",
        "Electronics" => "Electronics",
        "Chemical" => "Chemical",
        "Pharmaceutical" => "Pharmaceutical",
        "Food & Beverage" => "Food & Beverage",
        "Retail" => "Retail",
        "Wholesale" => "Wholesale",
        "Engineering" => "Engineering",
        "Oil & Gas" => "Oil & Gas",
        "Mining" => "Mining",
        "Packaging" => "Packaging",
        "Metal Fabrication" => "Metal Fabrication",
        "Plastic Manufacturing" => "Plastic Manufacturing",
        "Furniture" => "Furniture",
        "Agriculture" => "Agriculture",
        "Printing" => "Printing",
        "Paper Products" => "Paper Products",
        "Hardware" => "Hardware",
        "Heavy Machinery" => "Heavy Machinery",
        "Software" => "Software",
        "Other" => "Other",
    ];
}


