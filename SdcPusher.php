<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Api\Values\User;

class SensorSdcPusher
{
    private static $instance;

    private static $verbose;

    private static $debug;

    private static $devMode = true;

    private $client;

    public static $useCache = true;

    private $currentPost;

    public static function instance(string $baseUri, string $username, string $password): SensorSdcPusher
    {
        if (self::$instance === null) {
            self::$instance = new SensorSdcPusher($baseUri, $username, $password);
        }

        return self::$instance;
    }

    public static function debug($message, $prefix = '  '): void
    {
        if (self::$verbose) {
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

    public static function enableVerbose(): void
    {
        self::$verbose = true;
    }

    public static function disableVerbose(): void
    {
        self::$verbose = false;
    }

    public static function enableDebug(): void
    {
        self::$debug = true;
    }

    public static function disableDebug(): void
    {
        self::$debug = false;
    }

    public static function isDebugEnable(): bool
    {
        return (bool)self::$debug;
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

    public function push(Post $post, string $serviceId = "inefficiencies", $pushComments = true, $pushBinaries = true, $pdfFileRelativePath = null, $officeId = null, $operatorId = null): array
    {
        $this->currentPost = [
            'application' => null,
            'user' => null,
        ];
        SensorSdcPusher::debug("Working on post $post->id", false);
//        if (SensorSdcPusher::isDebugEnable()) SensorSdcPusher::debug(json_encode($post));
        $userData = $this->pushUser($post->author);

        $imagesData = [];
        $filesData = [];
        if ($pushBinaries) {
            foreach ($post->images as $image) {
                $imageItem = $this->pushBinary($image);
                SensorSdcPusher::debug("Image " . $imageItem['originalName']);
                $imagesData[] = $imageItem;
            }
            foreach ($post->files as $file) {
                $fileItem = $this->pushBinary($file);
                SensorSdcPusher::debug("File " . $fileItem['originalName']);
                $filesData[] = $fileItem;
            }
        }

        $data = $this->client->createApplication($post, $userData, $imagesData, $filesData, $serviceId, $pdfFileRelativePath, $officeId);
        SensorSdcPusher::debug("Remote application id is " . $data['id']);
        $this->currentPost['application'] = $data['id'];

        $needAssign = $post->status->identifier === 'open' ||  $post->status->identifier === 'close' || $post->comments->count() > 0;
        if ($needAssign){
            try {
                sleep(1);
                $this->client->assign($data['id'], $officeId, $operatorId);
            }catch (Exception $e){
                SensorSdcPusher::debug("ERROR: " . $e->getMessage());
            }
        }

        if ($pushComments) {
            foreach ($post->comments as $message) {
                $this->pushMessage($data, $post, $message);
            }
        }

        if ($post->status->identifier === 'close'){
            $message = $post->responses->count() > 0 ? $post->responses->lastMessage->text : '';
            $this->client->accept($data['id'], $message);
        }

//        foreach ($post->privateMessages as $message){
//            $this->pushMessage($data, $post, $message);
//        }

        return $data;
    }

    public function pushUser(User $user): array
    {
        $user = $this->client->createUser($user);
        SensorSdcPusher::debug("Remote user id is " . $user['id']);
        $this->currentPost['user'] = $user['id'];
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
        $message = $this->client->createMessage($application, $post, $message, $this->currentPost['user']);
        SensorSdcPusher::debug("Remote message id is " . $message['id']);
        return $message;
    }
}