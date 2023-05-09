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
    '[id:][clear][b:|baseurl:][u:|username:][p:|password:][s:|service:]',
    '',
    [
        'id' => 'Filter by post id',
        'clear' => 'Clear table cache before run',
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
$debug = $options['debug'];
//https://servizi.comune-qa.bugliano.pi.it/lang/api/services/cc4507a7-c569-4b59-a15a-0ea4c34e5d74
$serviceId = $options['service'] ?? "inefficiencies";

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
    if ($options['id']) {
        $objects = [eZContentObject::fetch((int)$options['id'])];
    } else {
        $cli->output('Fetching objects... ', false);
        $conditions = [
            'contentclass_id' => $repository->getPostContentClass()->attribute('id'),
            'status' => eZContentObject::STATUS_PUBLISHED,
        ];
        $objects = eZPersistentObject::fetchObjectList(
            eZContentObject::definition(),
            ['id', 'published'],
            $conditions,
            ['published' => 'asc']
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

    foreach ($objects as $index => $object) {
        if (!$debug) {
            $progressBar->advance();
        }
        try {
            $post = $repository->getPostService()->loadPost($object->attribute('id'));
        } catch (Exception $e) {
            continue;
        }

        $do = true;
        if ($post->status->identifier === 'close'){
            $do = false;
        }

        if ($do) {
            $pdf = shell_exec('php extension/sdc_pusher/generate_pdf.php -q --id=' . $post->id);
            eZDir::mkdir('pdf');
            file_put_contents('pdf/' . $post->id . '.pdf', $pdf);
            $pusher->push($post, $serviceId);
        }

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
