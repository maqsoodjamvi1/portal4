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



    /**

     * @param string $type student|employee

     */

    private function collectionId($campusId, string $type = 'student'): string

    {

        return $type === 'employee'

            ? 'campus_emp_' . $campusId

            : 'campus_' . $campusId;

    }



    /**

     * @param string $type student|employee

     */

    public function ensureCollection($campusId, string $type = 'student'): void

    {

        $collectionId = $this->collectionId($campusId, $type);



        try {

            $this->client->describeCollection([

                'CollectionId' => $collectionId,

            ]);

        } catch (\Exception $e) {

            $this->client->createCollection([

                'CollectionId' => $collectionId,

            ]);

        }

    }



    /**

     * @param string $type student|employee

     */

    public function indexFace($imageBytes, $externalId, $campusId, string $type = 'student')

    {

        $this->ensureCollection($campusId, $type);

        $collectionId = $this->collectionId($campusId, $type);



        return $this->client->indexFaces([

            'CollectionId' => $collectionId,

            'Image' => ['Bytes' => $imageBytes],

            'ExternalImageId' => (string) $externalId,

            'MaxFaces' => $this->awsConfig->rekognitionIndexMaxFaces,

            'QualityFilter' => $this->awsConfig->rekognitionIndexQualityFilter,

        ]);

    }



    /**

     * @param string $type student|employee

     */

    public function searchFace($imageBytes, $campusId, string $type = 'student')

    {

        $this->ensureCollection($campusId, $type);

        $collectionId = $this->collectionId($campusId, $type);



        return $this->client->searchFacesByImage([

            'CollectionId' => $collectionId,

            'Image' => ['Bytes' => $imageBytes],

            'MaxFaces' => $this->awsConfig->rekognitionSearchMaxFaces,

            'FaceMatchThreshold' => $this->awsConfig->rekognitionFaceMatchThreshold,

            'QualityFilter' => $this->awsConfig->rekognitionSearchQualityFilter,

        ]);

    }



    /**

     * @param string $type student|employee

     */

    public function deleteFace($faceId, $campusId, string $type = 'student')

    {

        return $this->client->deleteFaces([

            'CollectionId' => $this->collectionId($campusId, $type),

            'FaceIds' => [$faceId],

        ]);

    }

}
