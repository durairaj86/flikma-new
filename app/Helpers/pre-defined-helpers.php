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
    if (empty($services)) {
        return $nameOnly ? '' : [];
    }

    $serviceArray = array_filter(array_intersect_key(services(), array_flip((array) $services)));
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
    return $requiredData ? ($size[$requiredData] ?? $requiredData) : $size;
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
    // the column is text ("1", "2"): a strict match on numbers sent every real role to "Unknown"
    if ($role === null || $role === '') {
        return '—';
    }

    return __(match ((int) $role) {
        1 => 'Super User',
        2 => 'User',
        3 => 'Admin',
        4 => 'Employee',
        default => 'Unknown', // optional fallback
    });
}

/**
 * The Super User is the person who registered the company (role === 1).
 * They always have full access to every module — no department rights
 * checks apply to them.
 */
function isSuperUser($user = null): bool
{
    $user = $user ?? auth()->user();
    return $user && (int) $user->role === 1;
}

function appModules(): array
{
    return config('modules', []);
}

/**
 * Resolve a request path (no leading slash, e.g. "masters/users") to a
 * module key from config/modules.php. Returns null when the path doesn't
 * belong to any registered module (e.g. dashboard, profile, settings account).
 */
function resolveModuleFromPath(string $path): ?string
{
    $path = ltrim($path, '/');

    foreach (appModules() as $moduleKey => $module) {
        foreach ($module['prefixes'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $moduleKey;
            }
        }
    }

    return null;
}

/**
 * Whether the given (or currently authenticated) user may perform $action
 * ('view', 'create', 'edit', 'delete') on $module. The Super User always
 * passes. Users with no department, or whose department has no explicit
 * permission row for the module, are denied by default.
 */
function userCan(string $module, string $action = 'view', $user = null): bool
{
    $user = $user ?? auth()->user();

    if (!$user) {
        return false;
    }

    if (isSuperUser($user)) {
        return true;
    }

    if (!$user->department_id) {
        return false;
    }

    $column = 'can_' . $action;
    // the company's own row for this department and module, else the shared default row (see DepartmentModulePermission::effectiveFor)
    $permission = \App\Models\Master\DepartmentModulePermission::effectiveFor((int) $user->department_id, $user->company_id ? (int) $user->company_id : null)->get($module);

    return $permission ? (bool) $permission->{$column} : false;
}

/**
 * The VAT entries, read from the saudi_vat_framework table (the same table and the same dropdown the fatoora invoice app uses):
 * Standard Rated / Zero Rated / Exempt / Out of Scope, each zero rated / exempt / out of scope one with its VATEX reason code.
 * A tax code is BASE or BASE:VATEX-CODE ('STANDARD', 'ZERO:VATEX-SA-32'). Codes saved before this table was used (the plain
 * 'ZERO' / 'EXEMPT' / 'OUTOFSCOPE', VATEX-SA-34-3 ...) stay known as legacy entries, so old lines keep resolving but are not offered.
 */
function vat(): array
{
    static $cache = [];
    $locale = app()->getLocale();
    if (isset($cache[$locale])) return $cache[$locale];

    $base = ['S' => 'STANDARD', 'Z' => 'ZERO', 'E' => 'EXEMPT', 'O' => 'OUTOFSCOPE'];
    $groups = ['S' => 'Standard Rated', 'Z' => 'Zero Rated', 'E' => 'Exempt', 'O' => 'Out of Scope'];
    $entries = [];
    try {
        $rows = \Illuminate\Support\Facades\DB::table('saudi_vat_framework')->orderBy('id')->get();
    } catch (\Throwable $e) {
        $rows = collect();
    }
    $order = ['S' => 0, 'Z' => 1, 'E' => 2, 'O' => 3];
    foreach ($rows->sortBy(fn ($r) => [$order[$r->category_code] ?? 9, $r->id]) as $r) {
        $title = ucwords(str_replace('_', ' ', $r->slug));
        $reason = $r->exemption_reason_code ?: null;
        $desc = ($locale === 'ar' && $r->description_ar) ? $r->description_ar : (string) $r->description;
        $entries[] = [
            'code' => ($base[$r->category_code] ?? 'ZERO') . ($reason ? ':' . $reason : ''),
            'name' => (string) $r->description,                       // the reason text (English), saved on the line as exemption_code_text
            'group' => $groups[$r->category_code] ?? 'Other',
            'label' => $title,
            'percent' => (float) $r->rate,
            'description' => $desc,
            'reason' => $reason,
            'slug' => $r->slug,
            // the dropdown, as in fatoora: "15.00% - Standard Rate" (Out of Scope has no rate), and "VATEX-SA-32 — description" under it
            'option' => $r->slug === 'out_of_scope' ? $title : number_format((float) $r->rate, 2) . '% - ' . $title,
            'subtext' => $reason ? $reason . ' — ' . $desc : $desc,
        ];
    }

    // old codes the table does not carry: still known, never offered
    $known = array_column($entries, 'code');
    foreach (vatLegacy() as $v) {
        if (in_array($v['code'], $known, true)) continue;
        $entries[] = $v + ['legacy' => true, 'option' => number_format((float) $v['percent'], 2) . '% - ' . $v['label'], 'subtext' => $v['description'] ?? ''];
    }

    return $cache[$locale] = $entries;
}

/** The hard coded list flikma used before the saudi_vat_framework table: only for lines saved with one of these codes. */
function vatLegacy(): array
{
    return array_merge([
        [
            'code' => 'STANDARD',
            'name' => 'Standard',
            'group' => 'Standard Rated',
            'label' => 'Standard Rate',
            'percent' => 15,
            'description' => 'Standard VAT rate 15% applied to taxable supplies'
        ],
        [
            'code' => 'ZERO',
            'name' => 'Zero-rated',
            'group' => 'Zero Rated',
            'label' => 'Zero Rate',
            'percent' => 0,
            'description' => 'VAT rate 0% — exports, basic foods, education, health services'
        ],
        [
            'code' => 'EXEMPT',
            'name' => 'Exempt',
            'group' => 'Exempt',
            'label' => 'Exempt',
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
            'group' => 'Out of Scope',
            'label' => 'Out of Scope',
            'percent' => 0,
            'description' => 'Transactions not subject to VAT under KSA law'
        ],
    ], vatExemptionReasons());
}

/**
 * ZATCA exemption / zero-rate reasons (VATEX codes). Each one is a tax code of the form BASE:VATEX-CODE, e.g.
 * ZERO:VATEX-SA-32. vatBase() gives the category the rest of the app and ZATCA already understand (ZERO / EXEMPT /
 * OUTOFSCOPE); vatReason() gives the VATEX code that goes on the ZATCA invoice line.
 */
function vatExemptionReasons(): array
{
    $rows = [
        ['ZERO', 'VATEX-SA-32', 'Export of goods', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-33', 'Export of services', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-34-1', 'International transport of goods', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-34-2', 'International transport of passengers', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-34-3', 'Services directly connected to international passenger transport', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-34-4', 'Supply of a qualifying means of transport', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-34-5', 'Services relating to goods or passenger transportation (Article 25)', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-35', 'Medicines and medical equipment', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-36', 'Qualifying metals', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-EDU', 'Private education to citizens', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-HEA', 'Private healthcare to citizens', 'Zero Rated'],
        ['ZERO', 'VATEX-SA-MLTRY', 'Supply of qualified military goods', 'Zero Rated'],
        ['EXEMPT', 'VATEX-SA-29', 'Financial services (Article 29)', 'Exempt'],
        ['EXEMPT', 'VATEX-SA-29-7', 'Life insurance services (Article 29)', 'Exempt'],
        ['EXEMPT', 'VATEX-SA-30', 'Real estate transactions (Article 30)', 'Exempt'],
        ['OUTOFSCOPE', 'VATEX-SA-OOS', 'Not subject to VAT', 'Out of Scope'],
    ];
    return array_map(fn($r) => [
        'code' => $r[0] . ':' . $r[1],
        'name' => $r[2],
        'group' => $r[3],
        'label' => $r[2],
        'percent' => 0,
        'description' => $r[1],
        'reason' => $r[1],
    ], $rows);
}

/** The VAT category of a tax code: 'ZERO:VATEX-SA-32' -> 'ZERO'. */
function vatBase($code): string
{
    return strtok((string)$code, ':') ?: '';
}

/** The VATEX reason of a tax code ('' when it has none). */
function vatReason($code): string
{
    $parts = explode(':', (string)$code, 2);
    return $parts[1] ?? '';
}

/** Readable name of a tax code for lists and prints. */
function vatLabel($code): string
{
    if ($code === null || $code === '') return '-';
    foreach (vat() as $v) {
        if ($v['code'] === $code) return number_format($v['percent'], 2) . '% - ' . __($v['label']);
    }
    return (string)$code;
}

/**
 * The VAT exemption of a tax code, for a line that is not standard rated: ['category' => 'Zero Rate' | 'Exempt' | 'Out of Scope',
 * 'code' => 'VATEX-SA-32' (null when the line was saved with the plain category), 'text' => the reason, e.g. "Export of goods"].
 * Null for a standard rated line.
 */
function vatExemption($code): ?array
{
    if ($code === null || $code === '') return null;
    $entry = collect(vat())->firstWhere('code', $code);
    if (!$entry || (float) $entry['percent'] > 0) return null;

    // the category the code belongs to (ZERO:VATEX-SA-32 -> the "Zero Rate" entry), shown with the code
    $category = collect(vat())->firstWhere('code', explode(':', $code)[0]) ?? $entry;
    $hasCode = !empty($entry['reason']);

    return [
        'category' => __($category['label']),
        'code' => $hasCode ? $entry['reason'] : null,
        'text' => $hasCode ? (!empty($entry['legacy']) ? $entry['name'] : ($entry['description'] ?? $entry['name'])) : __($category['description'] ?? $category['label']),
        'text_en' => $hasCode ? ($entry['name'] ?? $entry['label']) : null,   // untranslated reason text, as saved on the line
        'group' => $entry['group'] ?? null,
    ];
}

/**
 * The VAT entries an invoice line can be given: the saudi_vat_framework ones (each zero rated / exempt / out of scope one with its
 * VATEX reason code). An old code that is no longer offered (plain Zero Rate ...) is added back only for the line that holds it ($keep).
 */
function vatSelectable($keep = null): array
{
    return array_values(array_filter(vat(), fn ($v) => empty($v['legacy']) || ($keep !== null && $keep !== '' && $v['code'] === $keep)));
}

/** Whether a tax code is a zero rated / exempt / out of scope one that has no VAT exemption reason code (the plain category). */
function vatNeedsExemption($code): bool
{
    if ($code === null || $code === '') return false;
    $entry = collect(vat())->firstWhere('code', $code);

    return $entry && (float) $entry['percent'] <= 0 && empty($entry['reason']);
}

function vatPercent($vat): int
{
    return match (vatBase($vat)) {
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
    if ($port == 'sea' || $port == 'air') {
        // the ports this company used most recently (at least 20, filled from the master list)
        $result = \App\Services\Common\RecentPorts::get($port);
    }/* elseif ($port == 'carrier-sea') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Sea')->orderBy('name')->limit(50)->get();
    } elseif ($port == 'carrier-air') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Air')->orderBy('name')->limit(50)->get();
    }*/
    return $result ?? collect();

}

function defaultCarriers($carrier = null): \Illuminate\Support\Collection
{
    $carrier = $carrier ?? 'sea';
    if ($carrier == 'sea') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Sea')->orderBy('name')->limit(50)->get();
    } elseif ($carrier == 'air') {
        $result = CarrierLines::select('id', 'name', 'mode')->where('mode', 'Air')->orderBy('name')->limit(50)->get();
    }
    return $result ?? collect();

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


