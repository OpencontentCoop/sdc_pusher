<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;
use Opencontent\Sensor\Api\Values\User;

class SdcCachedClient
{
    /**
     * @var SdcClient
     */
    private $client;

    public function __construct(SdcClient $client)
    {
        $this->client = $client;
        SdcPayload::createSchemaIfNeeded();
    }

    public function clearCache($id = null)
    {
        SdcPayload::createSchemaIfNeeded($id, true);
    }

    private function getCacheItem($id, $type, $callBack)
    {
        $payload = SdcPayload::fetch($id);
        if (!$payload instanceof SdcPayload) {
            $data = call_user_func($callBack);
            $payload = SdcPayload::create(
                $id,
                $type,
                $data
            );
        }

        return $payload->getPayload();
    }

    public function getAccessToken(): ?string
    {
        return $this->client->getAccessToken();
    }

    public function createUser(User $user): array
    {
        return $this->getCacheItem($user->id, 'user', function () use ($user) {
            return $this->client->createUser($user);
        });
    }

    /**
     * @param Post\Field\Image|Post\Field\File $field
     * @return array
     */
    public function uploadBinary(Post\Field $field): array
    {
        return $this->getCacheItem($field->apiUrl, 'binary', function () use ($field) {
            return $this->client->uploadBinary($field);
        });
    }

    public function createApplication(Post $post, array $userData, array $images, array $files, string $serviceId = "inefficiencies"): array
    {
        return $this->getCacheItem($post->id, 'post', function () use ($post, $userData, $images, $files, $serviceId) {
            return $this->client->createApplication($post, $userData, $images, $files, $serviceId);
        });
    }

    public function createMessage(array $application, Post $post, Message $message)
    {
        return $this->getCacheItem($message->id, 'message', function () use ($application, $post, $message) {
            return $this->client->createMessage($application, $post, $message);
        });
    }
}