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

$csvFile = __DIR__ . '/application_id_map.csv';
$options = new SQLICSVOptions([
    'csv_path' => $csvFile,
]);
$csvDoc = new SQLICSVDoc($options);
$csvDoc->parse();

foreach ($csvDoc->rows as $row) {
}


$script->shutdown();