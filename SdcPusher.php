<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Api\Values\User;

class SensorSdcPusher
{
    private static $instance;

    private static $debug;

    private static $devMode = true;

    private $client;

    public static $useCache = true;

    public static function instance(string $baseUri, string $username, string $password): SensorSdcPusher
    {
        if (self::$instance === null) {
            self::$instance = new SensorSdcPusher($baseUri, $username, $password);
        }

        return self::$instance;
    }

    public static function debug($message, $prefix = '  '): void
    {
        if (self::$debug) {
            eZCLI::instance()->output($prefix . $message);
        }
    }

    public static function isDevMode(): bool
    {
        return self::$devMode;
    }

    public static function enableDevMode(): void
    {
        self::$devMode = true;
    }

    public static function disableDevMode(): void
    {
        self::$devMode = false;
    }

    public static function enableDebug(): void
    {
        self::$debug = true;
    }

    private function __construct(string $baseUri, string $username, string $password)
    {
        if (self::$useCache) {
            $this->client = new SdcCachedClient(
                new SdcClient($baseUri, $username, $password)
            );
        }else{
            $this->client =new SdcClient($baseUri, $username, $password);
        }
    }

    public function clearCache($id = null): void
    {
        $this->client->clearCache($id);
    }

    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    public function push(Post $post, string $serviceId = "inefficiencies"): array
    {
        SensorSdcPusher::debug("Working on post $post->id", false);
        $userData = $this->pushUser($post->author);

        $imagesData = [];
        foreach ($post->images as $image) {
            $imagesData[] = $this->pushBinary($image);
        }
        $filesData = [];
        foreach ($post->files as $file) {
            $filesData[] = $this->pushBinary($file);
        }

        $data = $this->client->createApplication($post, $userData, $imagesData, $filesData, $serviceId);
        SensorSdcPusher::debug("Remote application id is " . $data['id']);

//        foreach ($post->comments as $message){
//            $this->pushMessage($data, $post, $message);
//        }
//
//        foreach ($post->privateMessages as $message){
//            $this->pushMessage($data, $post, $message);
//        }

        return $data;
    }

    public function pushUser(User $user): array
    {
        $user = $this->client->createUser($user);
        SensorSdcPusher::debug("Remote user id is " . $user['id']);
        return $user;
    }

    /**
     * @param Post\Field\Image|Post\Field\File $field
     * @return array
     */
    public function pushBinary(Post\Field $field): array
    {
        if (!$field instanceof Post\Field\Image && !$field instanceof Post\Field\File) {
            throw new InvalidArgumentException('Field must be an Image or a File');
        }

        return $this->client->uploadBinary($field);
    }

    public function pushMessage(array $application, Post $post, Message $message)
    {
        $message = $this->client->createMessage($application, $post, $message);
        SensorSdcPusher::debug("Remote message id is " . $message['id']);
        return $message;
    }
}