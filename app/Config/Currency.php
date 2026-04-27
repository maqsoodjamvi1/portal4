<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Currency extends BaseConfig
{
    public string $baseCode       = 'PKR';    // accounting base
    public string $defaultDisplay = 'PKR';    // used only as fallback
    public int    $cacheTTL       = 3600;     // seconds
    public string $provider       = 'exchangeratehost'; // see library below

    // If you add OpenExchangeRates, keep key here or env('openExchangeRates.key')
    public ?string $openExchangeRatesKey = null;
}
