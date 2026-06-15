<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class BoardPrep extends BaseConfig
{
    /** Hostnames that serve the board-prep portal (comma-separated in .env). */
    public string $hosts = 'prep.timesoftsol.com';

    /**
     * When true, routes are also available under /prep/* on any host (local dev).
     * Set boardPrep.enablePathPrefix = true in .env for XAMPP testing.
     */
    public bool $enablePathPrefix = false;

    public string $pathPrefix = 'prep';

    public string $productName = 'Board Exam Prep';

    public int $rateLimitAttempts = 8;

    public int $rateLimitWindow = 3600;

    /** Grade level => display label */
    public array $gradeLabels = [
        'ssc1'  => 'SSC-I (9th)',
        'ssc2'  => 'SSC-II (10th)',
        'hssc1' => 'HSSC-I (11th)',
        'hssc2' => 'HSSC-II (12th)',
    ];

    /**
     * System used for board-prep class / subject / topic lookups (your main school classes).
     */
    public int $boardPrepSystemId = 1;

    /**
     * Campus that hosts class sections for saving board-prep quizzes (0 = any campus with the class).
     */
    public int $platformCampusId = 0;

    /**
     * Portal grade key => class name aliases on boardPrepSystemId (first match wins).
     */
    public array $gradeClassNames = [
        'ssc1'  => ['9th', 'grade 9', 'g9', 'Class 9', 'Grade 9', 'SSC-I', 'SSC I', 'SSC-1'],
        'ssc2'  => ['10th', 'grade 10', 'g10', 'Class 10', 'Grade 10', 'SSC-II', 'SSC II', 'SSC-2'],
        'hssc1' => ['11th', '11', 'Class 11', 'Grade 11', 'HSSC-I', 'HSSC I', 'HSSC-1', '1st Year'],
        'hssc2' => ['12th', '12', 'Class 12', 'Grade 12', 'HSSC-II', 'HSSC II', 'HSSC-2', '2nd Year'],
    ];
}
