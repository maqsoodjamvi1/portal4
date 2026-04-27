<?php

namespace App\Helpers;

class BMICalculator
{
    /**
     * Calculate BMI from height and weight
     * @param float $height Height in cm
     * @param float $weight Weight in kg
     * @return float BMI value
     */
    public static function calculate($height, $weight)
    {
        if ($height <= 0 || $weight <= 0) {
            return null;
        }
        
        // Convert height from cm to meters
        $heightInMeters = $height / 100;
        
        // BMI = weight / (height^2)
        $bmi = $weight / ($heightInMeters * $heightInMeters);
        
        return round($bmi, 2);
    }
    
    /**
     * Get BMI category based on value and age
     * @param float $bmi BMI value
     * @param int $age Age in years (for pediatric BMI)
     * @return string Category
     */
    public static function getCategory($bmi, $age = null)
    {
        // For adults (age >= 18) or when age not provided
        if (!$age || $age >= 18) {
            if ($bmi < 18.5) return 'underweight';
            if ($bmi < 25) return 'normal';
            if ($bmi < 30) return 'overweight';
            return 'obese';
        }
        
        // For children (2-17 years) - using simplified percentiles
        // In production, use CDC/WHO growth charts
        $percentile = self::getPercentileForAge($bmi, $age);
        
        if ($percentile < 5) return 'underweight';
        if ($percentile < 85) return 'normal';
        if ($percentile < 95) return 'overweight';
        return 'obese';
    }
    
    /**
     * Get BMI percentile for children (simplified)
     * In production, use proper growth charts database
     */
    public static function getPercentileForAge($bmi, $age)
    {
        // Simplified percentiles - replace with actual growth chart data
        $idealBmi = self::getIdealBmiForAge($age);
        $deviation = ($bmi - $idealBmi) / $idealBmi * 100;
        
        // Rough approximation
        $percentile = 50 + ($deviation * 2);
        
        return max(0, min(100, round($percentile)));
    }
    
    /**
     * Get ideal BMI for age (simplified)
     */
    private static function getIdealBmiForAge($age)
    {
        if ($age <= 5) return 15.5;
        if ($age <= 8) return 16.5;
        if ($age <= 11) return 17.5;
        if ($age <= 14) return 19;
        if ($age <= 17) return 21;
        return 22;
    }
    
    /**
     * Get color code for BMI category
     */
    public static function getCategoryColor($category)
    {
        $colors = [
            'underweight' => '#3498db',  // Blue
            'normal' => '#2ecc71',       // Green
            'overweight' => '#f39c12',   // Orange
            'obese' => '#e74c3c'         // Red
        ];
        
        return $colors[$category] ?? '#95a5a6';
    }
    
    /**
     * Get emoji for BMI category
     */
    public static function getCategoryEmoji($category)
    {
        $emojis = [
            'underweight' => '??',
            'normal' => '?',
            'overweight' => '??',
            'obese' => '????'
        ];
        
        return $emojis[$category] ?? '??';
    }
}