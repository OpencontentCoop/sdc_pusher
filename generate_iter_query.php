<?php

use Opencontent\Sensor\Api\Values\Participant;
use Opencontent\Sensor\Api\Values\Post;

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

$options = $script->getOptions(
    '[id:]',
    '',
    [
        'id' => 'Filter by post id',
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$startTotalTime = time();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

if ($options['id']) {
    $sourceData = [
        (object)[
            'idV3' => $options['id'],
        ],
    ];
} else {
    $csvFile = __DIR__ . '/application_id_map.csv';
    $sqlOptions = new SQLICSVOptions([
        'csv_path' => $csvFile,
        'delimiter' => ',',
    ]);
    $csvDoc = new SQLICSVDoc($sqlOptions);
    $csvDoc->parse();
    $sourceData = $csvDoc->rows;
}

$operator = 'SegnalaCi Genova';
$repository = OpenPaSensorRepository::instance();
$rows = [];
foreach ($sourceData as $row) {
    $cli->output('.', false);
    $post = $repository->getPostService()->loadPost((int)$row->idV3);

    $statusChangeTpl = [
        'evento' => null,
        'operatore' => null,
        'user_group' => null,
        'responsabile' => null,
        'struttura' => null,
        'timestamp' => null,
        'message' => null,
        'message_id' => null,
    ];

    $changes = [];
    $publishedAt = $post->published->format('U');
    $changes[$publishedAt] = [
        [
            2000,
            array_merge($statusChangeTpl, [
                'evento' => 'Creazione pratica da altro soggetto',
                'operatore' => $operator,
                'timestamp' => $publishedAt,
            ]),
        ],
    ];

    $read = $post->timelineItems->getByType('read')->first();
    if ($read && $read->published instanceof DateTime) {
        $readAt = $read->published->format('U');
        $changes[$readAt] = [
            [
                4000,
                array_merge($statusChangeTpl, [
                    'evento' => 'Presa in carico',
                    'operatore' => $operator,
                    'user_group' => 'Ufficio Relazioni con il Pubblico',
                    'timestamp' => $readAt,
                    'responsabile' => $operator,
                ]),
            ],
        ];
    }

    $assignedList = $post->timelineItems->getByType('assigned');
    $lastAssigned = false;
    foreach ($assignedList->messages as $message) {
        foreach ($message->extra as $id) {
            $participant = $post->participants->getParticipantById($id);
            if ($participant && $participant->type == Participant::TYPE_GROUP) {
                $lastAssigned = [
                    'name' => $participant->name,
                    'at' => $message->published->format('U'),
                ];
            }
        }
    }

    if ($lastAssigned) {
        $changes[$lastAssigned['at']] = [
            [
                4000,
                array_merge($statusChangeTpl, [
                    'evento' => 'Presa in carico',
                    'operatore' => $operator,
                    'user_group' => $lastAssigned['name'],
                    'timestamp' => $lastAssigned['at'],
                    'responsabile' => $operator,
                ]),
            ],
        ];
    }


    $closed = $post->timelineItems->getByType('closed')->last();
    if ($closed && $closed->published instanceof DateTime) {
        $closedAt = $closed->published->format('U');
        $changes[$closedAt] = [
            [
                7000,
                array_merge($statusChangeTpl, [
                    'evento' => 'Approvazione pratica',
                    'operatore' => $operator,
                    'user_group' => 'Ufficio Relazioni con il Pubblico',
                    'timestamp' => $closedAt,
                    'responsabile' => $operator,
                ]),
            ],
        ];
    }

    if ($options['id']) {
        print_r($changes);
        echo serialize($changes);
    }else{
        $rows[] = [
            'id' => $row->id,
            'storico_stati' => serialize($changes),
        ];
    }
}

$export = __DIR__ . '/export_iter.csv';
eZFile::create('export_iter.csv', __DIR__, '');
$cli->output('Create csv');
$fp = fopen($export, 'w');
foreach ($rows as $fields) {
    fputcsv($fp, $fields, '|');
}
fclose($fp);

$script->shutdown();