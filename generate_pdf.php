<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

require 'autoload.php';
include(realpath(dirname(__FILE__) . '/_include.php'));

$script = eZScript::instance([
        'description' => ("Get pdf\n\n"),
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

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$repository = OpenPaSensorRepository::instance();

$object = eZContentObject::fetch((int)$options['id']);

try {
    $post = $repository->getPostService()->loadPost($object->attribute('id'));
    SensorPdfExport::instance($repository, $post->id)->generate();
} catch (Exception $e) {

}

$script->shutdown();