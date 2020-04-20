<?php
require_once realpath(__DIR__).'/vendor/autoload.php';
require_once realpath(__DIR__).'/inc/story_functions.php';

$loader      = new \Twig\Loader\FilesystemLoader(realpath(__DIR__).'/static/twig');
$twig        = new \Twig\Environment(
    $loader,
    [
        'cache'       => realpath(__DIR__).'/cache',
        'auto_reload' => true,
    ]
);
$template    = $twig->load('index.html.twig');
$dir         = dirname(__FILE__);
$sourceFile  = file_get_contents($dir.'/../data/stories.json');
$sourceObj   = json_decode($sourceFile);
$source      = objectToArray($sourceObj);
$storiesList = makeHnList($source);

echo $template->render([ 'storiesList' => $storiesList ]);
