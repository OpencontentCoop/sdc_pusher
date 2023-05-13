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
        $payload = SdcPayload::fetchByIdAndType($id, $type);
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

    public function getCurrentUserId(): ?string
    {
        return $this->client->getCurrentUserId();
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

    public function createApplication(Post $post, array $userData, array $images, array $files, string $serviceId = "inefficiencies", $pdfFileRelativePath = null): array
    {
        return $this->getCacheItem($post->id, 'post', function () use ($post, $userData, $images, $files, $serviceId, $pdfFileRelativePath) {
            return $this->client->createApplication($post, $userData, $images, $files, $serviceId, $pdfFileRelativePath);
        });
    }

    public function createMessage(array $application, Post $post, Message $message, $remoteUserId = null)
    {
        return $this->getCacheItem($message->id, 'message', function () use ($application, $post, $message, $remoteUserId) {
            return $this->client->createMessage($application, $post, $message, $remoteUserId);
        });
    }

    public function assign($applicationId, $officeId, $operatorId)
    {
        return $this->getCacheItem($applicationId, 'assign', function () use ($applicationId, $officeId, $operatorId) {
            return $this->client->assign($applicationId, $officeId, $operatorId);
        });
    }

    public function accept($applicationId, $message)
    {
        return $this->getCacheItem($applicationId, 'accept', function () use ($applicationId, $message) {
            return $this->client->accept($applicationId, $message);
        });
    }
}