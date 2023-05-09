<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;

class SdcMessageSerializer
{
    public function serialize(Post $post, Message $message)
    {
        $visibility = $message instanceof Message\Comment ? 'applicant' : 'internal';

        return [
            'message' => $message->richText, //$message->text
            'visibility' => $visibility,
            'created_at' => $message->published->format('c'),
        ];
    }
}