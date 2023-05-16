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

    /**
     * @var SdcClient|SdcCachedClient
     */
    private $client;

    public static $useCache = true;

    private $currentPost;

    private $ignoreCacheForId;

    private $currentId;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

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

    public static function warning($message, $prefix = '  '): void
    {
        if (self::$verbose) {
            eZCLI::instance()->warning($prefix . $message);
        }
    }

    public static function error($message, $prefix = '  '): void
    {
        eZCLI::instance()->error($prefix . $message);
    }

    public static function warningOnDebug($message, $prefix = '  '): void
    {
        if (self::isDebugEnable()){
            self::warning($message, $prefix);
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
        $this->baseUri = $baseUri;
        $this->username = $username;
        $this->password = $password;
        SdcPayload::createSchemaIfNeeded();
        $this->setClient();
    }

    private function setClient(bool $readWrite = true)
    {
        if (self::$useCache) {
            $this->client = new SdcCachedClient(
                new SdcClient($this->baseUri, $this->username, $this->password)
            );
            if (!$readWrite){
                SensorSdcPusher::warning('Ignore cache for current post #' . $this->ignoreCacheForId);
                $this->client->setWriteOnly();
            }else{
                $this->client->unsetWriteOnly();
            }
        } else {
            $this->client = new SdcClient($this->baseUri, $this->username, $this->password);
        }
    }

    public function clearCache($id = null): void
    {
        $this->client->clearCache($id);
        $this->ignoreCacheForId = $id;
    }

    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    private function useCacheForCurrentId(): bool
    {
        return (int)$this->ignoreCacheForId !== (int)$this->currentId;
    }

    public function push(
        Post $post,
        string $serviceId = "inefficiencies",
        $pushComments = true,
        $pushBinaries = true,
        $pdfFileRelativePath = null,
        $officeId = null,
        $operatorId = null
    ): array {
        $this->currentPost = [
            'application' => null,
            'user' => null,
        ];
        $this->currentId = $post->id;
        $this->setClient($this->useCacheForCurrentId());
        SensorSdcPusher::debug("Working on post $post->id", false);
//        if (SensorSdcPusher::isDebugEnable()) SensorSdcPusher::debug(json_encode($post));
        $userData = $this->pushUser($post->author);
        SensorSdcPusher::warningOnDebug(json_encode($userData));

        $imagesData = [];
        $filesData = [];
        if ($pushBinaries) {
            foreach ($post->images as $image) {
                $imageItem = $this->pushBinary($image);
                SensorSdcPusher::debug("Image " . $imageItem['originalName']);
                SensorSdcPusher::warningOnDebug(json_encode($imageItem));
                $imagesData[] = $imageItem;
            }
            foreach ($post->files as $file) {
                $fileItem = $this->pushBinary($file);
                SensorSdcPusher::debug("File " . $fileItem['originalName']);
                SensorSdcPusher::warningOnDebug(json_encode($fileItem));
                $filesData[] = $fileItem;
            }
        }

        $data = $this->client->createApplication(
            $post,
            $userData,
            $imagesData,
            $filesData,
            $serviceId,
            $pdfFileRelativePath
        );
        SensorSdcPusher::warning("Remote application id is " . $data['id']);
        $this->currentPost['application'] = $data['id'];
        SensorSdcPusher::warningOnDebug(json_encode($data));

        $needAssign = $post->status->identifier === 'open'
            || $post->status->identifier === 'close'
            || $post->comments->count() > 0;

        if ($pushComments) {
            if ($needAssign) {
                $dateTime = null;
                $read = $post->timelineItems->getByType('read')->first();
                if ($read && $read->published instanceof DateTime) {
                    $dateTime = $read->published->format('c');
                }
                if ($post->status->identifier === 'close') {
                    SensorSdcPusher::debug("Assign to default office and operator if needed");
                    $this->client->assign($data['id'], $officeId, $dateTime, $operatorId);
                } else {
                    SensorSdcPusher::debug("Assign to default office if needed");
                    $this->client->assign($data['id'], $officeId, $dateTime);
                }
            }

            foreach ($post->comments as $message) {
                $messageData = $this->pushMessage($data, $post, $message);
                SensorSdcPusher::warningOnDebug(json_encode($messageData));
            }

            if ($post->status->identifier === 'close') {
                $message = $post->responses->count() > 0 ? $post->responses->lastMessage->text : '';
                SensorSdcPusher::debug("Close if needed");
                $responseData = $this->client->accept($data['id'], $message);
                SensorSdcPusher::warningOnDebug(json_encode($responseData));
            }
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
        if (SensorSdcPusher::isDebugEnable()) SensorSdcPusher::debug(json_encode($user));
        $this->currentPost['user'] = $user['id'];
        return $user;
    }

    /**
     * @param Post\Field\Image|Post\Field\File $field
     * @return array
     */
    public function pushBinary(Post\Field $field): array
    {
        if (SensorSdcPusher::isDebugEnable()) SensorSdcPusher::debug(json_encode($field));
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