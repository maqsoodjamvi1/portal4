<?php 
namespace App\Libraries\Fx;

class ExchangeRateHost
{
    public function fetch(string $base, array $quotes): array
    {
        if (empty($quotes)) return [];
        $symbols = implode(',', array_unique($quotes));
        $url = "https://api.exchangerate.host/latest?base={$base}&symbols={$symbols}";
        $resp = @file_get_contents($url);
        if (!$resp) return [];
        $json = json_decode($resp, true);
        return $json['rates'] ?? [];
    }
}
