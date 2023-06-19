<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';
include(realpath(dirname(__FILE__) . '/_include.php'));

$script = eZScript::instance([
        'description' => ("Push all posts to sdc\n\n"),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true,
    ]
);

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$startTotalTime = time();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

$csvFile = __DIR__ . '/application_id_map-open.csv';
$options = new SQLICSVOptions([
    'csv_path' => $csvFile, 'delimiter' => ','
]);
$csvDoc = new SQLICSVDoc($options);
$csvDoc->parse();
$operatorId = 'bfde2a0b-947b-4420-80b2-12910b6a5729';
$repository = OpenPaSensorRepository::instance();
$missing = [
    'p' => [],
    'm' => [],
];
$rows = [];
foreach ($csvDoc->rows as $row) {
//    $payload = SdcPayload::fetchByIdAndType($row->idV3, 'post');
//    if (!$payload instanceof SdcPayload) {
//        $cli->error('post ' . $row->idV3);
//        $missing['p'][] = $row->idV3;
//        continue;
//    }
//
//    $postData = $payload->getPayload();

    $post = $repository->getPostService()->loadPost((int)$row->idV3);
    foreach ($post->comments as $message) {
        $messageSerializer = new SdcMessageSerializer();
        $data = $messageSerializer->serialize($post, $message, $row->userId);
        if (!empty($data['message'])) {
            $rows[] = [
                'id' => (string)Ramsey\Uuid\Uuid::uuid4(),
                'user_id' => $data['author_id'] ?? $operatorId,
                'pratica_id' => $row->id,
                'message' => $data['message'],
                'visibility' => $data['visibility'],
                'created_at' => $message->published->format('U'),
                'protocol_required' => false,
            ];
        }
    }
    foreach ($post->responses as $message) {
        $messageSerializer = new SdcMessageSerializer();
        $data = $messageSerializer->serialize($post, $message, $row->userId);
        if (!empty($data['message'])) {
            $rows[] = [
                'id' => (string)Ramsey\Uuid\Uuid::uuid4(),
                'user_id' => $data['author_id'] ?? $operatorId,
                'pratica_id' => $row->id,
                'message' => $data['message'],
                'visibility' => $data['visibility'],
                'created_at' => $message->published->format('U'),
                'protocol_required' => false,
            ];
        }
    }
}
$export = __DIR__ . '/export_messages-open.csv';
eZFile::create('export_messages-open', __DIR__, '');
$cli->output('Create csv');
$fp = fopen($export, 'w');
foreach ($rows as $fields) {
    fputcsv($fp, $fields, '|');
}
fclose($fp);

$script->shutdown();