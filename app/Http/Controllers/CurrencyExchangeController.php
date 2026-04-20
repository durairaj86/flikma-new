<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyExchangeController extends Controller
{
    public function getExchangeRate($base, $target, $amount = 1)
    {
        $apiKey = config('services.exchangerate.key'); // keep your API key in .env
        if ($base == 'USD') {
            return [
                "conversion_rate" => 3.76,
                "conversion_result" => 3.76
            ];
        } elseif ($base == $target) {
            return [
                "conversion_rate" => 1.00,
                "conversion_result" => 1.00
            ];
        }
        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$base}/{$target}/{$amount}";

        $response = Http::get($url);

        if ($response->failed()) {
            return ['error' => true, 'message' => 'API request failed'];
        }
        return $response->json();

    }
}
