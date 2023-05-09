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

$options = $script->getOptions(
    '[id:][clear][b:|baseurl:][u:|username:][p:|password:][service:][only-closed][no-comments][no-files][limit:][offset:][dry-run][office:][operator:][no-dev][clear-cache][sleep:][skip-file][file:][slack-endpoint:]',
    '',
    [
        'id' => 'Filter by post id',
        'file' => 'Read id list from file (one per line)',
        'only-closed' => 'Push only closed',
        'clear' => 'Clear table cache before run',
        'no-comments' => 'Skip pushing comments',
        'no-files' => 'Skip pushing files and images',
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$startTotalTime = time();

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();

$baseUri = $options['baseurl'];
$username = $options['username'];
$password = $options['password'];
$verbose = $options['verbose'];
$debug = $options['debug'];
$sleepSecs = $options['sleep'] ? (int)$options['sleep'] : 20;
$skipFile = $options['skip-file'];

$serviceId = $options['service'] ?? "inefficiencies";
$cli->warning('Use service ' . $serviceId);

$officeId = $options['office'];
$cli->warning('Use office ' . $officeId);

$operatorId = $options['operator'];
$limit = (int)$options['limit'];
$offset = (int)$options['offset'];

$pushComments = !($options['no-comments']);
$pushBinaries = !($options['no-files']);

$slackEndpoint = $options['slack-endpoint'];

//SensorSdcPusher::$categories = json_decode(file_get_contents(__DIR__ . '/categories.json'), true);


$doneItems = [];
$csvFile = __DIR__ . '/application_id_map.csv';
$sqliOptions = new SQLICSVOptions([
    'csv_path' => $csvFile, 'delimiter' => ','
]);
$csvDoc = new SQLICSVDoc($sqliOptions);
$csvDoc->parse();
foreach ($csvDoc->rows as $row) {
    $doneItems[$row->idV3] = $row->id;
}

eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
try {
    $pusher = SensorSdcPusher::instance($baseUri, $username, $password);

    if ($verbose) {
        $pusher::enableVerbose();
        $cli->warning('Enable verbose');
    }

    if ($debug) {
        $pusher::enableDebug();
        $cli->warning('Enable debug');
    }

    if (!$options['no-dev']) {
        $pusher::enableDevMode();
    } else {
        $cli->warning('Run in production mode');
        $pusher::disableDevMode();
    }

    if ($options['clear-cache']) {
        $cli->warning('Clear internal cache');
        $pusher->clearCache();
    }

    if ($options['clear']) {
        $cli->warning('Clear internal cache for selected id');
        $pusher->clearCache($options['id']);
    }

    if ($pushComments) {
        $cli->warning('Push comments enabled');
    }

    if ($pushBinaries) {
        $cli->warning('Push binaries enabled');
    }

    $repository = OpenPaSensorRepository::instance();
    if ($options['file']) {
        if (!file_exists($options['file'])){
            $cli->error('File not found: '. $options['file']);
            die(1);
        }
        $objects = [];
        $data = explode(PHP_EOL, file_get_contents($options['file']));
        foreach ($data as $item) {
            $item = (int)$item;
            if ($item > 0) {
                $objects[] = ['id' => $item];
            }
        }

    } elseif ($options['id']) {
        $objects = [eZContentObject::fetch((int)$options['id'], false)];

    } else {
        $closeStateIdList = $openStateIdList = [];
        $states = $repository->getSensorPostStates('sensor');
        foreach ($states as $state) {
            if ($state->attribute('identifier') === 'close') {
                $closeStateIdList[] = $state->attribute('id');
            } else {
                $openStateIdList[] = $state->attribute('id');
            }
        }
        $filterStateIdList = $options['only-closed'] ? $closeStateIdList : $openStateIdList;

        $cli->output('Fetching objects... ', false);
        $limits = $limit > 0 ? ['limit' => $limit, 'offset' => $offset] : null;
        $conditions = [
            'contentclass_id' => $repository->getPostContentClass()->attribute('id'),
            'status' => eZContentObject::STATUS_PUBLISHED,
        ];
        $objects = eZPersistentObject::fetchObjectList(
            eZContentObject::definition(),
            ['id', 'published'],
            $conditions,
            ['published' => 'asc'],
            $limits,
            null,
            false,
            ['contentobject_state_id'],
            ['ezcobj_state_link'],
            ' AND ezcobj_state_link.contentobject_id = ezcontentobject.id and ezcobj_state_link.contentobject_state_id in (' . implode(
                ',',
                $filterStateIdList
            ) . ')'
        );
    }

    $objectsCount = count($objects);
    $cli->warning('Now push ' . $objectsCount . ' post(s)');

    if (!$verbose) {
        $output = new ezcConsoleOutput();
        $progressBarOptions = ['emptyChar' => ' ', 'barChar' => '='];
        $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
        $progressBar->start();
    }

    $stats = [];
    $delayForComments = [];
    $i = 0;
    foreach ($objects as $index => $object) {
        $i++;
        try {
            $startTime = time();

            if (!$verbose) {
                $progressBar->advance();
            } else {
                $cli->output();
                $cli->warning("$i/$objectsCount Post #" . $object['id']);
            }

            if (isset($doneItems[$object['id']])){
                if ($verbose) {
                    //$cli->output(' - skip (done list)');
                }
                continue;
            }

            if (SdcPayload::fetchByIdAndType($object['id'], 'post') instanceof SdcPayload
                || SdcPayload::fetchByIdAndType($object['id'], 'accept') instanceof SdcPayload){
                if ($verbose) {
                    //$cli->output(' - already done');
                }
                continue;
            }

            try {
                $post = $repository->getPostService()->loadPost((int)$object['id']);
            } catch (Exception $e) {
                if (!$verbose) {
                    $cli->error($e->getMessage());
                }
                SensorSdcPusher::notify(
                    $slackEndpoint,
                    'Error on post ' . $object['id'] . ': ' . $e->getMessage()
                );
                continue;
            }

            $pdfDirectory = SdcPostSerializer::serializaPdfDirectory($post);
            $pdfFileRelativePath = '/pdf/' . $pdfDirectory . '/' . $post->id . '.pdf';
            $pdfFilePath = __DIR__ . $pdfFileRelativePath;

            if (!$skipFile) {
                if ($verbose) {
                    $cli->output($object['id'] . " ({$post->status->identifier}) $pdfFilePath ", false);
                }
                if (!file_exists($pdfFilePath)) {
                    $pdf = shell_exec(
                        'php /mnt/efs/cluster-openpa/migration/sdc_pusher/generate_pdf.php -sbackend -q --id=' . $post->id
                    );
                    eZDir::mkdir(dirname($pdfFilePath), false, true);
                    file_put_contents($pdfFilePath, $pdf);
                    if ($verbose) {
                        $cli->warning('stored');
                    }
                } else {
                    if ($verbose) {
                        $cli->output('already stored');
                    }
                }
            }

            if (!$options['dry-run']) {
                $delayForComments[$post->id] = [
                    'post' => $post,
                    'serviceId' => $serviceId,
                    'pdfFileRelativePath' => $pdfFileRelativePath,
                    'officeId' => $officeId,
                    'operatorId' => $operatorId,
                ];
                $pusher->push(
                    $post,
                    $serviceId,
                    false,
                    $pushBinaries,
                    $pdfFileRelativePath,
                    $officeId,
                    $operatorId
                );
            }else{
                SensorSdcPusher::debug("Working on post $post->id", false);sleep(1);
            }
            $stats++;
            eZContentObject::clearCache();
            $endTime = time();
            if ($verbose) {
                $cli->output('Elapsed: ' . ($endTime - $startTime) . ' secs');
            }
        } catch (Exception $e) {
            eZLog::write($object['id'] . " " . $e->getMessage(), 'sdc_push.log');
            if ($verbose) {
                $cli->error('#' . $object['id'] . ' ' . $e->getMessage());
            }
            SensorSdcPusher::notify(
                $slackEndpoint,
                'Error on post ' . $object['id'] . ': ' . $e->getMessage()
            );
        }
    }
    if (!$verbose) {
        $progressBar->finish();
    }

//    if ($pushComments) {
//        $cli->output();
//        $cli->output();
//        $cli->warning('Now push assignments and comments');
//
//        $countDelayForComments = count($delayForComments);
//        $cli->output("Wait for $sleepSecs secs");
//        sleep($sleepSecs);
//
//        if (!$verbose) {
//            $output = new ezcConsoleOutput();
//            $progressBarOptions = ['emptyChar' => ' ', 'barChar' => '='];
//            $progressBar = new ezcConsoleProgressbar($output, $countDelayForComments, $progressBarOptions);
//            $progressBar->start();
//        }
//
//        $i = 0;
//        foreach ($delayForComments as $id => $item) {
//            $i++;
//            try {
//                if (!$verbose) {
//                    $progressBar->advance();
//                } else {
//                    $cli->output();
//                    $cli->warning("$i/$countDelayForComments Post #" . $id);
//                }
//                if (!$options['dry-run']) {
//                    $pusher->push(
//                        $item['post'],
//                        $item['serviceId'],
//                        true,
//                        false,
//                        $item['pdfFileRelativePath'],
//                        $item['officeId'],
//                        $item['operatorId']
//                    );
//                }
//
//            } catch (Exception $e) {
//                eZLog::write("$id " . $e->getMessage(), 'sdc_push.log');
//                if ($verbose) {
//                    $cli->error('#' . $post->id . ' ' . $e->getMessage());
//                }
//                SensorSdcPusher::notify(
//                    $slackEndpoint,
//                    'Error on post ' . $post->id . ': ' . $e->getMessage()
//                );
//            }
//        }
//        if (!$verbose) {
//            $progressBar->finish();
//        }
//        $cli->output();
//    }

    $endTotalTime = time();
    $cli->output('Elapsed: ' . ($endTotalTime - $startTotalTime) . ' secs');

    $script->shutdown();

} catch (Throwable $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $cli->output();
    $cli->error('#' . $post->id . ' ' . $e->getMessage());
    if ($debug) {
        $cli->error($e->getTraceAsString());
    }
    $script->shutdown($errCode);
}
