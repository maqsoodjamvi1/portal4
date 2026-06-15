<?php

namespace App\Controllers\Admin;

/**
 * Legacy alias — routes now point to FeeChalanPay::advanceFee / saveAdvanceBalances.
 */
class AdvanceFee extends FeeChalanPay
{
    public function index()
    {
        return $this->advanceFee();
    }

    public function save()
    {
        return $this->saveAdvanceBalances();
    }
}
