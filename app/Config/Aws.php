<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Aws extends BaseConfig
{
    public $key = 'YOUR_AWS_KEY';
    public $secret = 'YOUR_AWS_SECRET';
    public $region = 'us-east-1';

    /**
     * Rekognition collection search: minimum similarity (0–100) for a face to be considered a match.
     * Higher = fewer false positives, slightly more false negatives.
     */
    public int $rekognitionFaceMatchThreshold = 88;

    /**
     * Extra guard on the returned Similarity score (same scale). Must be >= FaceMatchThreshold in practice.
     */
    public int $rekognitionMinSimilarityPercent = 88;

    /**
     * Enrollment: only index the largest face (important when multiple people are in frame).
     */
    public int $rekognitionIndexMaxFaces = 1;

    /**
     * Attendance scan: return at most this many candidate matches (1 is fastest and usual).
     */
    public int $rekognitionSearchMaxFaces = 1;

    /**
     * AUTO | NONE | LOW | MEDIUM | HIGH — enrollment quality gate (HIGH rejects blur/low quality).
     */
    public string $rekognitionIndexQualityFilter = 'AUTO';

    /**
     * Input quality filter before search (AUTO ignores worst-quality detections).
     */
    public string $rekognitionSearchQualityFilter = 'AUTO';

    /** Max image payload sent to Rekognition (bytes). */
    public int $rekognitionMaxImageBytes = 5242880;
}


