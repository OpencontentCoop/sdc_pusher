<?php

use Opencontent\Sensor\Api\Values\Message;
use Opencontent\Sensor\Api\Values\Post;

class SdcMessageSerializer
{
    public function serialize(Post $post, Message $message, $remoteUserId = null)
    {
        $visibility = $message instanceof Message\Comment ? 'applicant' : 'internal';

        $prefix = '[' . $message->creator->name . '] ';
        $author = null;
        if ($post->author->id == $message->creator->id && $remoteUserId){
            $prefix = '';
            $author = $remoteUserId;
        }

        $data = [
            'message' => $prefix . $message->richText, //$message->text
            'visibility' => $visibility,
            'created_at' => $message->published->format('c'),
        ];

        if ($author){
            $data['author'] = $author;
        }

        return $data;
    }
}