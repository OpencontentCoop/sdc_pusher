<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';
include(realpath(dirname(__FILE__) . '/_include.php'));

$script = eZScript::instance([
        'description' => ("Get Token\n\n"),
        'use-session' => false,
        'use-modules' => false,
        'use-extensions' => false,
    ]
);

$script->startup();

$options = $script->getOptions(
    '[b:|baseurl:][u:|username:][p:|password:]',
    '',
    [
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();
$baseUri = $options['baseurl'];
$username = $options['username'];
$password = $options['password'];
$debug = $options['debug'];

if ($debug) {
    print_r($options);
}

//eZDB::setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);
try {
    SensorSdcPusher::$useCache = false;
    $pusher = SensorSdcPusher::instance($baseUri, $username, $password);
    $cli->output($pusher->getAccessToken());
    $script->shutdown();
} catch (Throwable $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $cli->output();
    $cli->error($e->getMessage());
    if ($debug) {
        $cli->error($e->getTraceAsString());
    }
    $script->shutdown($errCode);
}
