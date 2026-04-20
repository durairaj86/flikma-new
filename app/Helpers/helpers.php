<?php


use App\Models\Zatca\ZatcaConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

include('menus.php');
include('pre-defined-helpers.php');
function cacheName()
{
    return companyId();
}

function authUserCompany()
{
    if (session('company_id')) {
        return \App\Models\Master\Company::companies();
        //return \App\Models\Master\Company::findOrFail(session('company_id'));
        //return session('company_id');
    }
    Auth::logout();
    return false;
}

function companyId()
{
    if (session('company_id')) {
        return session('company_id');
    }
    return false;
}

function encodeId($id)
{
    if (is_string($id) || is_numeric($id)) {
        return Hashids::encode($id);
    }

    throw new \InvalidArgumentException("Invalid ID: must be a string or numeric value.");
}

function decodeId($hash)
{
    if ($hash) {
        if (!is_string($hash)) {
            throw new \InvalidArgumentException("Invalid hash: must be a string.");
        }

        $decoded = Hashids::decode($hash);

        if (empty($decoded)) {
            throw new \InvalidArgumentException("Invalid hash: cannot be decoded.");
        }

        return $decoded[0]; // return original numeric ID
    }
    return $hash;
}

function decodeIds($hashes)
{
    // If empty, return empty
    if (!$hashes) {
        return [];
    }

    // If single string, convert into array
    if (is_string($hashes)) {
        $hashes = [$hashes];
    }

    // Ensure it's an array
    if (!is_array($hashes)) {
        throw new \InvalidArgumentException("Invalid input: must be a string or array.");
    }

    $decodedIds = [];

    foreach ($hashes as $hash) {

        if (!is_string($hash)) {
            throw new \InvalidArgumentException("Invalid hash: must be a string.");
        }

        $decoded = Hashids::decode($hash);

        if (empty($decoded)) {
            throw new \InvalidArgumentException("Invalid hash: cannot be decoded. Given: {$hash}");
        }

        $decodedIds[] = $decoded[0];
    }

    return $decodedIds;
}

function amountToFloat($value): float
{
    return (float)str_replace(',', '', $value);
}

function amountFormat($amount, $decimals = 2): string
{
    return number_format($amount, $decimals, '.', ',');
}

function amountInWords($number)
{
    $no = floor($number);
    $point = round(($number - $no) * 100);

    $digits_1 = strlen($no);
    $i = 0;
    $str = [];

    $words = [
        0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
        5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
        14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen',
        17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        20 => 'twenty', 30 => 'thirty', 40 => 'forty',
        50 => 'fifty', 60 => 'sixty', 70 => 'seventy',
        80 => 'eighty', 90 => 'ninety'
    ];

    $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];

    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;

        $counter = count($str);

        if ($number) {
            $str[] = ($number < 21)
                ? $words[$number] . ' ' . ($digits[$counter] ?? '')
                : $words[intdiv($number, 10) * 10] . ' ' . $words[$number % 10] . ' ' . ($digits[$counter] ?? '');
        }
    }

    $result = trim(implode(' ', array_reverse($str)));

    // ✅ FIX: convert paise to words
    $paise = '';
    if ($point > 0) {
        $paise = ($point < 21)
            ? $words[$point]
            : $words[intdiv($point, 10) * 10] . ' ' . $words[$point % 10];
    }

    return ucwords($result . ' ' . ($paise ? 'and ' . $paise . ' only' : 'only'));
}

function convert($amount, $currency = 'ريال سعودي')
{
    // Basic Arabic number translation
    $formatter = new \NumberFormatter('ar', \NumberFormatter::SPELLOUT);
    $arabicWords = $formatter->format($amount);
    return ucfirst($arabicWords);
}

function appVersion(): string
{
    return "1.0.53";
}

function toArabicNumber($number)
{
    // Ensure the input is treated as a string, handling null/numeric types
    $number = (string)$number;

    $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    return str_replace($western, $arabic, $number);
}

/**
 * Convert normal date (d-m-Y or d/m/Y) to database format (Y-m-d)
 */
function formDate($date): ?string
{
    if (empty($date)) {
        return null;
    }

    try {
        // Automatically detect separator and format
        return Carbon::createFromFormat(str_contains($date, '/') ? 'd/m/Y' : 'd-m-Y', $date)
            ->format('Y-m-d');
    } catch (\Exception $e) {
        return null;
    }
}

function showDate($date): ?string
{
    if (empty($date)) {
        return null;
    }

    try {
        return Carbon::parse($date)->format('d-m-Y');
    } catch (\Exception $e) {
        return null;
    }
}

function isFilled($data): bool
{
    // Not an array → treat as empty
    if (!is_array($data)) {
        return false;
    }

    // If array has no elements
    if (empty($data)) {
        return false;
    }

    // Check each value for actual content (not empty string, not null, not spaces)
    foreach ($data as $value) {
        if (!empty(trim((string)$value))) {
            return true;
        }
    }

    return false;
}

function commonZatcaErrors($code)
{
    $message = [
        'BR-KSA-84' => 'For Standard Rate VAT Category the rate can either be 5% or 15%',
        'BR-KSA-66' => 'Seller postal code (BT-38) must be 5 digits',
        'BR-O-08' => 'Not subject to VAT - Category Total Mismatch',
        'invalid-invoice-hash' => 'Check your customer details like VAT Number, CR ID, Address...',
    ];
    return $message[$code] ?? null;
}

function zatcaDateCheck($model): array
{
    $register = false;
    $message = null;
    $subDomain = subDomain();
    $zatcaCheck = isCompanyEligibleForZatca($subDomain);
    if ($zatcaCheck) {
        $company = authUserCompany();
        if (($company->currency == 'SAR' && !$company->zatca_registered)) {
            if ($company->wave && (Carbon::parse($model->invoice_date) >= Carbon::parse((zatcaWave($company->wave))))) {
                $register = true;
                $message = isSimulationMode($subDomain, $company);
            }
        } elseif ($company->zatca_registered && $company->wave && (Carbon::parse($model->invoice_date) >= Carbon::parse((zatcaWave($company->wave))))) {
            $register = true;
        }
    }
    return [
        'zatcaRegister' => $register,
        'message' => $message
    ];
}

//simulation working like production for testing domains like apex, demo, pilot
function isSimulationMode($subDomain, $company): string
{
    if (!isTestingDomain($subDomain)) {
        $zatcaConfig = ZatcaConfig::where('company_id', $company->id)->select('status')->first();
        if (filled($zatcaConfig) && $zatcaConfig->status == \App\Enums\Zatca::SIMULATION_MODE) {
            return 'You are in simulation mode. kindly activate production mode';
        }
    }
    return 'You must onboard with ZATCA. your integration date ' . zatcaWave($company->wave);
}

function isCompanyEligibleForZatca($subDomain): bool
{
    $sandboxDomain = isTestingDomain($subDomain);
    if ($sandboxDomain && \App\Enums\Zatca::TEST) {
        return true;
    } else {
        if ($sandboxDomain) {
            return false;
        }
    }
    return true;
}

function isTestingDomain($subDomain = null): bool
{
    return in_array($subDomain ?? subDomain(), ['flikma', 'dev', 'demo']);
}

function zatcaMode()
{
    return isTestingDomain() ? \App\Enums\Zatca::SIMULATION_TEXT : \App\Enums\Zatca::CORE_TEXT;
}

function zatcaWave($wave)
{
    $data = [
        "1" => "01-07-2023",
        "2" => "01-01-2024",
        "3" => "01-02-2024",
        "4" => "01-03-2024",
        "5" => "01-04-2024",
        "6" => "01-05-2024",
        "7" => "01-06-2024",
        "8" => "01-07-2024",
        "9" => "01-10-2024",
        "10" => "01-01-2025",
        "11" => "01-02-2025",
        "12" => "01-03-2025",
        "13" => "01-04-2025",
        "14" => "01-05-2025",
        "15" => "01-06-2025",
        "16" => "01-07-2025",
        "17" => "01-08-2025",
        "18" => "01-09-2025",
        "19" => "01-10-2025",
        "20" => "01-11-2025",
        "21" => "01-12-2025",
        "22" => "01-01-2026",

    ];
    return isset($data[$wave]) ? $data[$wave] : false;
}

function subDomain()
{
    $domain = 'dev';

    $host = request()->getHttpHost();

    if (!empty($host)) {
        $domain = str_replace('www.', '', $host);
        $domain = strstr($domain, '.', true);
        if ($domain === false) {
            $domain = $host;
        }
    }

    return $domain;
}

function getInitials(string $name): string
{
    // Remove extra whitespace, convert to array of words
    $words = explode(' ', trim($name));
    $initials = '';

    // Get the first word's initial
    $initials .= strtoupper(substr($words[0], 0, 1));

    // If there is a second word, get its initial
    if (isset($words[1])) {
        // Find the first non-empty word after the first word
        $secondWordInitial = '';
        foreach (array_slice($words, 1) as $word) {
            if (!empty($word)) {
                $secondWordInitial = strtoupper(substr($word, 0, 1));
                break;
            }
        }
        $initials .= $secondWordInitial;
    }

    // Ensure we don't return an empty string
    return !empty($initials) ? $initials : 'NA';
}

function truncateName(string $name, int $limit = 25): string
{
    if (Str::length($name) <= $limit) {
        return $name;
    }

    $totalLength = Str::length($name);
    $remaining = $totalLength - $limit;

    $truncatedName = Str::substr($name, 0, $limit);
    return $truncatedName . '...';
}

function actionJson()
{
    return ['user_id' => Auth::id(), 'at' => now()->format('Y-m-d H:i:s')];
}
