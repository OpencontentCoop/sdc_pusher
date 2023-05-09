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
    '[id:][clear][b:|baseurl:][u:|username:][p:|password:][s:|service:][only-closed][no-comments][no-files][limit:][offset:][dry-run]',
    '',
    [
        'id' => 'Filter by post id',
        'only-closed' => 'Push only closed',
        'clear' => 'Clear table cache before run',
        'no-comments' => 'Skip pushing comments',
        'no-files' => 'Skip pushing files and images'
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
$cli = eZCLI::instance();
$baseUri = $options['baseurl'];
$username = $options['username'];
$password = $options['password'];
$debug = $options['verbose'];
$serviceId = $options['service'] ?? "inefficiencies";
$limit = (int)$options['limit'];
$offset = (int)$options['offset'];

eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
try {
    $pusher = SensorSdcPusher::instance($baseUri, $username, $password);
    $pusher::enableDevMode();
    if ($debug) {
        $pusher::enableDebug();
    }

    if ($options['clear']) {
        $cli->warning('Clear cache');
        $pusher->clearCache($options['id']);
    }

    $repository = OpenPaSensorRepository::instance();

    $closeStateIdList = $openStateIdList = [];
    $states = $repository->getSensorPostStates('sensor');
    foreach ($states as $state){
        if ($state->attribute('identifier') === 'close'){
            $closeStateIdList[] = $state->attribute('id');
        }else{
            $openStateIdList[] = $state->attribute('id');
        }
    }
    $filterStateIdList = $options['only-closed'] ? $closeStateIdList : $openStateIdList;

    if ($options['id']) {
        $objects = [eZContentObject::fetch((int)$options['id'], false)];
    } else {
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
            ' AND ezcobj_state_link.contentobject_id = ezcontentobject.id and ezcobj_state_link.contentobject_state_id in (' . implode(',', $filterStateIdList) . ')'
        );
    }
    $objectsCount = count($objects);
    $cli->warning('Now push ' . $objectsCount . ' post(s)');

    if (!$debug) {
        $output = new ezcConsoleOutput();
        $progressBarOptions = ['emptyChar' => ' ', 'barChar' => '='];
        $progressBar = new ezcConsoleProgressbar($output, $objectsCount, $progressBarOptions);
        $progressBar->start();
    }

    $pushComments = !($options['no-comments']);
    $pushBinaries = !($options['no-files']);

    $stats = [];
    foreach ($objects as $index => $object) {
        if (!$debug) {
            $progressBar->advance();
        }

        try {
            $post = $repository->getPostService()->loadPost((int)$object['id']);
        } catch (Exception $e) {
            $cli->error($e->getMessage());
            continue;
        }

        $pdfDirectory = SdcPostSerializer::serializaPdfDirectory($post);
        $pdfFilePath = __DIR__ . '/pdf/' . $pdfDirectory . '/'. $post->id . '.pdf';
        if ($debug) {
            $cli->output($object['id'] . " ({$post->status->identifier}) $pdfFilePath");
        }
        $pdf = shell_exec('php extension/sdc_pusher/generate_pdf.php -q --id=' . $post->id);
        eZDir::mkdir(__DIR__ . '/pdf/' . $pdfDirectory, false, true);
        file_put_contents($pdfFilePath, $pdf);
        if (!$options['dry-run']) {
            $pusher->push($post, $serviceId, $pushComments, $pushBinaries);
        }
        $stats++;

        eZContentObject::clearCache();
    }

    if (!$debug) {
        $progressBar->finish();
    }
    $cli->output();

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
