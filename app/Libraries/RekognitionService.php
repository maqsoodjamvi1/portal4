<?php

namespace App\Libraries;

use Aws\Rekognition\RekognitionClient;

class RekognitionService
{
    protected RekognitionClient $client;

    /** @var \Config\Aws */
    protected $awsConfig;

    public function __construct()
    {
        $this->awsConfig = config('Aws');

        $this->client = new RekognitionClient([
            'region' => $this->awsConfig->region,
            'version' => 'latest',
            'credentials' => [
                'key' => $this->awsConfig->key,
                'secret' => $this->awsConfig->secret,
            ],
        ]);
    }

    private function collection($campusId)
    {
        return 'campus_' . $campusId;
    }

    public function ensureCollection($campusId)
    {
        try {
            $this->client->describeCollection([
                'CollectionId' => $this->collection($campusId),
            ]);
        } catch (\Exception $e) {
            $this->client->createCollection([
                'CollectionId' => $this->collection($campusId),
            ]);
        }
    }

    public function indexFace($imageBytes, $studentId, $campusId)
    {
        $this->ensureCollection($campusId);

        return $this->client->indexFaces([
            'CollectionId' => $this->collection($campusId),
            'Image' => ['Bytes' => $imageBytes],
            'ExternalImageId' => (string)$studentId,
            'MaxFaces' => $this->awsConfig->rekognitionIndexMaxFaces,
            'QualityFilter' => $this->awsConfig->rekognitionIndexQualityFilter,
        ]);
    }

    public function searchFace($imageBytes, $campusId)
    {
        return $this->client->searchFacesByImage([
            'CollectionId' => $this->collection($campusId),
            'Image' => ['Bytes' => $imageBytes],
            'MaxFaces' => $this->awsConfig->rekognitionSearchMaxFaces,
            'FaceMatchThreshold' => $this->awsConfig->rekognitionFaceMatchThreshold,
            'QualityFilter' => $this->awsConfig->rekognitionSearchQualityFilter,
        ]);
    }

    public function deleteFace($faceId, $campusId)
    {
        return $this->client->deleteFaces([
            'CollectionId' => $this->collection($campusId),
            'FaceIds' => [$faceId],
        ]);
    }
}