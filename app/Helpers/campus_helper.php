<?php

if (!function_exists('getCampusExpiryInfo')) {
    function getCampusExpiryInfo($campusId)
    {
        $db = \Config\Database::connect();
        
        // Get the latest active bill (status = 1) for this campus
        $latestBill = $db->table('campus_bills')
            ->select('campus_expiry, bill_id, plan_id, bill_issue_date')
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->orderBy('bill_id', 'DESC')
            ->get()
            ->getRow();
        
        if (!$latestBill || empty($latestBill->campus_expiry)) {
            return [
                'expiry_date' => null,
                'days_left' => null,
                'status' => 'unknown',
                'message' => 'No active subscription',
                'css_class' => 'text-muted',
                'icon' => 'fa-question-circle',
                'badge_class' => 'bg-secondary',
                'details' => 'No active subscription found'
            ];
        }
        
        $expiryDate = new \DateTime($latestBill->campus_expiry);
        $today = new \DateTime();
        $interval = $today->diff($expiryDate);
        $daysLeft = (int)$interval->format('%r%a'); // Negative if expired
        
        // Determine status based on days left
        if ($daysLeft < 0) {
            return [
                'expiry_date' => $latestBill->campus_expiry,
                'days_left' => $daysLeft,
                'status' => 'expired',
                'message' => 'EXPIRED!',
                'css_class' => 'text-danger font-weight-bold',
                'icon' => 'fa-exclamation-triangle',
                'badge_class' => 'bg-danger',
                'details' => 'Subscription expired ' . abs($daysLeft) . ' days ago on ' . date('d M Y', strtotime($latestBill->campus_expiry))
            ];
        } elseif ($daysLeft <= 30) {
            return [
                'expiry_date' => $latestBill->campus_expiry,
                'days_left' => $daysLeft,
                'status' => 'critical',
                'message' => '?? Expires in ' . $daysLeft . ' days!',
                'css_class' => 'text-danger font-weight-bold',
                'icon' => 'fa-exclamation-circle',
                'badge_class' => 'bg-danger',
                'details' => '?? CRITICAL: Subscription expires in ' . $daysLeft . ' days on ' . date('d M Y', strtotime($latestBill->campus_expiry)) . '. Please renew immediately!'
            ];
        } elseif ($daysLeft <= 90) {
            return [
                'expiry_date' => $latestBill->campus_expiry,
                'days_left' => $daysLeft,
                'status' => 'warning',
                'message' => '?? Expires in ' . $daysLeft . ' days',
                'css_class' => 'text-warning font-weight-bold',
                'icon' => 'fa-clock',
                'badge_class' => 'bg-warning',
                'details' => 'Subscription expires in ' . $daysLeft . ' days on ' . date('d M Y', strtotime($latestBill->campus_expiry))
            ];
        } else {
            return [
                'expiry_date' => $latestBill->campus_expiry,
                'days_left' => $daysLeft,
                'status' => 'good',
                'message' => '? Expires: ' . date('d M Y', strtotime($latestBill->campus_expiry)),
                'css_class' => 'text-success',
                'icon' => 'fa-check-circle',
                'badge_class' => 'bg-success',
                'details' => 'Subscription active until ' . date('d M Y', strtotime($latestBill->campus_expiry)) . ' (' . $daysLeft . ' days remaining)'
            ];
        }
    }
}