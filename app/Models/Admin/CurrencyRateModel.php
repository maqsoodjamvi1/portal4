<?php 
namespace App\Models\Admin;

use CodeIgniter\Model;

class CurrencyRateModel extends Model
{
    protected $table         = 'currency_rates';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['rate_date','base_code','quote_code','rate','source','created_at'];

    public function latest(string $base, string $quote, ?string $date = null): ?float
    {
        $b = $this->builder()->select('rate')
            ->where('base_code',  $base)
            ->where('quote_code', $quote);

        if ($date) {
            $b->where('rate_date <=', $date)
              ->orderBy('rate_date','DESC');
        } else {
            $b->orderBy('rate_date','DESC');
        }

        $row = $b->get(1)->getRowArray();
        return $row['rate'] ?? null;
    }
}
