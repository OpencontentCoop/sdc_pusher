<?php

use GuzzleHttp\Client;
use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Api\Values\User;

class SdcClient
{
    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $password;

    private $baseUri;

    private $apiUri;

    private $locale;

    private static $token;

    private $client;

    private $postSerializer;

    private $userSerializer;

    private $binarySerializer;

    private $messageSerializer;

    public function __construct(string $baseUri = null, string $username = null, string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUri = rtrim($baseUri, '/');
        $this->apiUri = $this->baseUri . '/api';
        $this->locale = 'it';
        $this->client = new Client();

        $this->postSerializer = new SdcPostSerializer();
        $this->userSerializer = new SdcUserSerializer();
        $this->binarySerializer = new SdcBinarySerializer();
        $this->messageSerializer = new SdcMessageSerializer();
    }

    public function getAccessToken(): ?string
    {
        if (self::$token === null) {
            $response = json_decode(
                (string)$this->client->request(
                    'POST',
                    $this->apiUri . '/auth',
                    [
                        'json' => [
                            'username' => $this->username,
                            'password' => $this->password,
                        ],
                    ]
                )->getBody(),
                true
            );
            self::$token = $response['token'];
        }

        return self::$token;
    }

    public function createApplication(Post $post, array $userData, array $images, array $files, string $serviceId = "inefficiencies"): array
    {
        $data = $this->postSerializer->serialize($post, $userData, $images, $files, $serviceId);

        SensorSdcPusher::debug("Create application $post->id");
        $response = (string)$this->client->request(
            'POST',
            $this->apiUri . '/applications',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'json' => $data,
            ]
        )->getBody();

        return json_decode($response, true);
    }

    private function getUserByFiscalCode(string $fiscalCode): ?array
    {
        SensorSdcPusher::debug("Get user by cf $fiscalCode");
        $response = (string)$this->client->request(
            'GET',
            $this->apiUri . '/users',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'query' => ['cf' => $fiscalCode],
            ]
        )->getBody();
        $response = json_decode($response, true);
        if (count($response) > 0) {
            return $response[0];
        }

        return null;
    }

    public function createUser(User $user): array
    {
        $data = $this->userSerializer->serialize($user);

        if ($user = $this->getUserByFiscalCode($data['codice_fiscale'])) {
            return $user;
        }

        SensorSdcPusher::debug("Create user " . implode(' ', $data));
        $response = (string)$this->client->request(
            'POST',
            $this->apiUri . '/users',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'json' => $data,
            ]
        )->getBody();

        return json_decode($response, true);
    }

    public function uploadBinary(Post\Field $field): array
    {
        $data = $this->binarySerializer->serialize($field);
        /** @var eZClusterFileHandlerInterface $handler */
        $handler = $data['_handler'];
        unset($data['_handler']);

        if (!$handler->exists()){
            throw new Exception("File $handler->filePath not found");
        }

        SensorSdcPusher::debug("Get upload pre-signed uri for " . $data['name']);
        $handler->fetch();
        $response = (string)$this->client->request(
            'POST',
            $this->baseUri . '/' . $this->locale . '/upload',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'json' => $data,
            ]
        )->getBody();

        $fileInfo = json_decode($response, true);
        $fileContents = file_get_contents($handler->filePath);

        SensorSdcPusher::debug("Put file to " . $fileInfo['uri']);
        $curl = curl_init();
        $curlOptions = [
            CURLOPT_URL => $fileInfo['uri'],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_ENCODING => "",
            CURLOPT_POST => 1,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_INFILESIZE => $handler->size(),
            CURLOPT_HTTPHEADER => [
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Content-Length: " . $handler->size(),
                "Content-Type: multipart/form-data"
            ],
        ];
        curl_setopt_array($curl, $curlOptions);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fileContents);
        $res = curl_exec($curl);

        SensorSdcPusher::debug("Update upload " . $fileInfo['id']);
        $fileHash = hash('sha256', $fileContents);
        $res = (string)$this->client->request(
            'PUT',
            $this->baseUri . '/' . $this->locale . '/upload/' . $fileInfo['id'],
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'json' => [
                    'file_hash' => $fileHash,
                    'check_signature' => false,
                ],
            ]
        )->getBody();

        $handler->deleteLocal();

        return [
            "name" => $data['name'],
            "url" => null,
            "size" => $data['size'],
            "type" => $data['mime_type'],
            "data" => $fileInfo,
            "originalName" => $data['original_filename'],
            "hash" => $fileHash,
            "preview" => null,
        ];
    }

    public function createMessage(array $application, Post $post, Message $message)
    {
        $data = $this->messageSerializer->serialize($post, $message);

        // soft workaround per errore 500 ma messaggio creato
        if ($response = $this->getMessageByText($application['id'], $data['message'])) {
            return $response;
        }

        $messageType = get_class($message);
        SensorSdcPusher::debug("Create message ($messageType) $message->id");
        $response = (string)$this->client->request(
            'POST',
            $this->apiUri . '/applications/' . $application['id'] . '/messages',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                'json' => $data,
            ]
        )->getBody();
        return json_decode($response, true);
    }

    private function getMessageByText($applicationId, $text)
    {
        $response = (string)$this->client->request(
            'GET',
            $this->apiUri . '/applications/' . $applicationId . '/messages',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ]
            ]
        )->getBody();
        $messages = json_decode($response, true);

        foreach ($messages as $message){
            if ($message['message'] === $text){
                SensorSdcPusher::debug("(workaround) message already pushed");
                return $message;
            }
        }

        return false;
    }
}