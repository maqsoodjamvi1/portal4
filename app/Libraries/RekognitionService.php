<?php

namespace App\Libraries;

use Aws\Rekognition\RekognitionClient;

class RekognitionService
{
    protected $client;

    public function __construct()
    {
        $config = new \Config\Aws();

        $this->client = new RekognitionClient([
            'region' => $config->region,
            'version' => 'latest',
            'credentials' => [
                'key' => $config->key,
                'secret' => $config->secret,
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
        ]);
    }

    public function searchFace($imageBytes, $campusId)
    {
        return $this->client->searchFacesByImage([
            'CollectionId' => $this->collection($campusId),
            'Image' => ['Bytes' => $imageBytes],
            'MaxFaces' => 1,
            'FaceMatchThreshold' => 85,
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