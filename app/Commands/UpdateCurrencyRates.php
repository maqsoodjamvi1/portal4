<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\Admin\CurrencyModel;
use App\Models\Admin\CurrencyRateModel;
use App\Libraries\Fx\ExchangeRateHost; 

class UpdateCurrencyRates extends BaseCommand
{
    protected $group       = 'Currency';
    protected $name        = 'currency:update';
    protected $description = 'Fetch and store latest currency rates';

    public function run(array $params)
    {
        $cfg     = config('Currency');
        $base    = $cfg->baseCode;
        $cModel  = new CurrencyModel();
        $rModel  = new CurrencyRateModel();

        // If PK is currency_id, query by where('code IS NOT NULL')
        $rows = $cModel->select('code')->orderBy('code')->findAll();
        $quotes = array_values(array_filter(array_column($rows, 'code'), fn($c) => $c && $c !== $base));
        if (!$quotes) { CLI::write('No quote currencies found.', 'yellow'); return; }

        $provider = new ExchangeRateHost();
        $rates = $provider->fetch($base, $quotes);
        if (!$rates) { CLI::error('No rates fetched'); return; }

        $today = date('Y-m-d');
        foreach ($rates as $quote => $rate) {
            $rModel->ignore(true)->insert([
                'rate_date'  => $today,
                'base_code'  => $base,
                'quote_code' => $quote,
                'rate'       => $rate,
                'source'     => 'exchangerate.host',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            CLI::write("Saved {$base}?{$quote} = {$rate}");
        }
        CLI::write('Done', 'green');
    }
}
