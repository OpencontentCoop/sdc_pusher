<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;

class SdcMessageSerializer
{
    public function serialize(Post $post, Message $message)
    {
        $visibility = $message instanceof Message\Comment ? 'applicant' : 'internal';

        $prefix = '[' . $message->creator->name . '] ';
        return [
            'message' => $prefix . $message->richText, //$message->text
            'visibility' => $visibility,
            'created_at' => $message->published->format('c'),
        ];
    }
}