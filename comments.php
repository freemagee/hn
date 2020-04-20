<?php
require_once realpath(__DIR__).'/vendor/autoload.php';
require_once realpath(__DIR__).'/inc/comments_functions.php';

$loader   = new \Twig\Loader\FilesystemLoader(realpath(__DIR__).'/static/twig');
$twig     = new \Twig\Environment(
    $loader,
    [
        'cache'       => realpath(__DIR__).'/cache',
        'auto_reload' => true,
    ]
);
$template = $twig->load('comments.html.twig');
$_GET     = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$storyId  = $_GET['id'];

// Check id matched pattern, this may need to be more adaptable for the future. Also check the source data and format it.
try {
    if (validateId($storyId) === false) {
        throw new Exception('Story ID is not valid');
    }

    $theSource = validateSource($storyId);

    if (empty($theSource) === true) {
        throw new Exception('Unable to retrieve comments');
    }

    $story          = processStory($theSource);
    $sourceComments = $theSource['comments'];

    if (empty($sourceComments) === true) {
        throw new Exception('This story has no comments yet');
    }

    $comments = processComments($sourceComments);
} catch (Exception $e) {
    $comments = [];
}//end try


echo $template->render([ 'story' => $story, 'comments' => $comments ]);
