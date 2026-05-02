<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Builds fixed-layout fee table rows for challan print views:
 * 4 detail slots + 1 remainder row (5 rows total).
 */
final class FeeChalanDisplayRows
{
    /** Detail slots before arrears/remainder row */
    public const DETAIL_SLOTS = 4;

    /** Total body rows (detail + remainder) */
    public const BODY_ROWS = 5;

    /**
     * @param array<int, array<string, mixed>> $chalans student unpaid rows (decorated)
     *
     * @return array<int, array<string, mixed>>
     */
    public static function studentRows(array $chalans): array
    {
        $chalans = array_values($chalans);
        $n       = count($chalans);

        $blankRow = static function (): array {
            return [
                'is_blank'             => true,
                'particulars_label'    => '',
                'amount'               => '',
                'discount'             => '',
                'net_amount'           => 0,
                'fee_month_label'      => '',
                'amount_formatted'     => '',
                'discount_formatted'   => '',
                'net_amount_formatted' => '',
            ];
        };

        $displayRows = [];

        if ($n === 0) {
            for ($i = 0; $i < self::BODY_ROWS; $i++) {
                $displayRows[] = $blankRow();
            }

            return $displayRows;
        }

        $slots = self::DETAIL_SLOTS;

        for ($i = 0; $i < $slots; $i++) {
            if ($i < $n) {
                $chalan    = $chalans[$i];
                $netAmount = (float) ($chalan['net_amount'] ?? 0);
                $amount    = (float) ($chalan['amount'] ?? 0);
                $discount  = (float) ($chalan['discount'] ?? 0);

                $displayRows[] = [
                    'particulars_label'    => $chalan['particulars_label'] ?? '',
                    'amount'               => $amount,
                    'discount'             => $discount,
                    'net_amount'           => $netAmount,
                    'fee_month_label'      => $chalan['fee_month_label'] ?? '',
                    'amount_formatted'     => number_format($amount, 0),
                    'discount_formatted'   => $discount > 0 ? number_format($discount, 0) : '',
                    'net_amount_formatted' => number_format($netAmount, 0),
                ];
            } else {
                $displayRows[] = $blankRow();
            }
        }

        if ($n <= $slots) {
            $displayRows[] = $blankRow();

            return $displayRows;
        }

        $remAmt  = 0.0;
        $remDisc = 0.0;
        $months  = [];

        for ($j = $slots; $j < $n; $j++) {
            $chalan   = $chalans[$j];
            $amount   = (float) ($chalan['amount'] ?? 0);
            $discount = (float) ($chalan['discount'] ?? 0);
            $remAmt  += $amount;
            $remDisc += $discount;
            if (! empty($chalan['fee_month_label'])) {
                $months[] = $chalan['fee_month_label'];
            }
        }

        $remNet = $remAmt - $remDisc;

        $remainingCount = $n - $slots;
        if ($remainingCount === 1) {
            $c = $chalans[$slots];
            $partLabel = trim(($c['particulars_label'] ?? '') . (! empty($c['fee_month_label']) ? ' (' . $c['fee_month_label'] . ')' : ''));
            if ($partLabel === '') {
                $partLabel = 'Other dues';
            }
        } else {
            $monthRange = count($months) > 0 ? min($months) . ' - ' . max($months) : 'Previous';
            $partLabel  = 'Arrears (' . $monthRange . ')';
        }

        $displayRows[] = [
            'is_arrears'           => true,
            'particulars_label'    => $partLabel,
            'amount'               => $remAmt,
            'discount'             => $remDisc,
            'net_amount'           => $remNet,
            'fee_month_label'      => '',
            'amount_formatted'     => number_format($remAmt, 0),
            'discount_formatted'   => $remDisc > 0 ? number_format($remDisc, 0) : '',
            'net_amount_formatted' => number_format($remNet, 0),
        ];

        return $displayRows;
    }

    /**
     * @param array<int, array<string, mixed>> $feeByParticular
     *
     * @return array<int, array<string, mixed>>
     */
    public static function familyRows(array $feeByParticular): array
    {
        $rows = array_values($feeByParticular);
        $n    = count($rows);

        $blankRow = static function (): array {
            return [
                'is_blank'             => true,
                'particulars_label'    => '',
                'amount'               => '',
                'discount'             => '',
                'net_amount'           => 0,
                'amount_formatted'     => '',
                'discount_formatted'   => '',
                'net_amount_formatted' => '',
                'month_display'        => '',
            ];
        };

        $displayRows = [];

        if ($n === 0) {
            for ($i = 0; $i < self::BODY_ROWS; $i++) {
                $displayRows[] = $blankRow();
            }

            return $displayRows;
        }

        $slots = self::DETAIL_SLOTS;

        for ($i = 0; $i < $slots; $i++) {
            if ($i < $n) {
                $row        = $rows[$i];
                $amount     = (float) ($row['total_amount'] ?? 0);
                $discount   = (float) ($row['total_discount'] ?? 0);
                $netAmount  = $amount - $discount;
                $displayRows[] = [
                    'particulars_label'    => $row['particulars_label'] ?? '',
                    'amount'               => $amount,
                    'discount'             => $discount,
                    'net_amount'           => $netAmount,
                    'amount_formatted'     => number_format($amount, 0),
                    'discount_formatted'   => $discount > 0 ? number_format($discount, 0) : '',
                    'net_amount_formatted' => number_format($netAmount, 0),
                    'month_display'        => $row['month_display'] ?? '',
                ];
            } else {
                $displayRows[] = $blankRow();
            }
        }

        if ($n <= $slots) {
            $displayRows[] = $blankRow();

            return $displayRows;
        }

        $arrearsAmount   = 0.0;
        $arrearsDiscount = 0.0;
        $arrearsNet      = 0.0;
        $arrearsMonths   = [];

        for ($j = $slots; $j < $n; $j++) {
            $row = $rows[$j];
            $amount    = (float) ($row['total_amount'] ?? 0);
            $discount  = (float) ($row['total_discount'] ?? 0);
            $netAmount = $amount - $discount;
            $arrearsAmount   += $amount;
            $arrearsDiscount += $discount;
            $arrearsNet      += $netAmount;
            if (! empty($row['month_display'])) {
                $arrearsMonths[] = $row['month_display'];
            }
        }

        $remainingCount = $n - $slots;
        if ($remainingCount === 1) {
            $r = $rows[$slots];
            $partLabel = trim(($r['particulars_label'] ?? '') . (! empty($r['month_display']) ? ' (' . $r['month_display'] . ')' : ''));
            if ($partLabel === '') {
                $partLabel = 'Other dues';
            }
        } else {
            if (! empty($arrearsMonths)) {
                sort($arrearsMonths);
                $monthRange = $arrearsMonths[0] . ' - ' . $arrearsMonths[count($arrearsMonths) - 1];
            } else {
                $monthRange = 'Previous Months';
            }
            $partLabel = 'Arrears (' . $monthRange . ')';
        }

        $displayRows[] = [
            'is_other'             => true,
            'particulars_label'    => $partLabel,
            'amount'               => $arrearsAmount,
            'discount'             => $arrearsDiscount,
            'net_amount'           => $arrearsNet,
            'amount_formatted'     => number_format($arrearsAmount, 0),
            'discount_formatted'   => $arrearsDiscount > 0 ? number_format($arrearsDiscount, 0) : '',
            'net_amount_formatted' => number_format($arrearsNet, 0),
            'month_display'        => '',
        ];

        return $displayRows;
    }
}
