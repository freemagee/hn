<?php
/*******************************************************************************
* INCLUDES
 ******************************************************************************/

include_once(realpath(__DIR__ . '/..') . '/src/inc/common_functions.php');

/*******************************************************************************
 * VARIABLES
 ******************************************************************************/

$dir = realpath(__DIR__ . '/..') . '/src/data/';
$source_file = $dir . 'articles.json';
$output_file = $dir . 'comments.json';

if (!file_exists($output_file)) {
    create_file($dir, 'comments', 'json');
}

$source_data = file_get_contents($source_file);
$source_obj = json_decode($source_data);
$hn_article_list = object_to_array($source_obj);

/******************************************************************************
 * LOGIC
 *****************************************************************************/

if (!empty($hn_article_list)) {
    $comments = array();
    $limit = count($hn_article_list);

    for ($i = 0; $i < $limit; $i++) {
        $id = $hn_article_list[$i]['id'];

        if (isset($id)) {
            $comments[] = $id;
        }
    }

    if (!empty($comments)) {
        //$time_start = microtime(true);
        $comments_list = generate_comment_list($comments);
        //echo 'Total execution time in seconds: ' . (microtime(true) - $time_start);
        $json_data = json_encode($comments_list);
        file_put_contents($output_file, $json_data);
    }
}

/*******************************************************************************
 * FUNCTIONS
 ******************************************************************************/

function generate_comment_url($id) {
    //return 'https://hacker-news.firebaseio.com/v0/item/' . $id . '.json';
    // Now using an alternative unofficial API
    return 'http://node-hnapi.herokuapp.com/item/' .$id;
}

function generate_comment_list($array) {
    $output = array();
    $limit = count($array);

    for ($i = 0; $i < $limit; $i++) {
        $id = $array[$i];
        $url = generate_comment_url($id);
        $comment = get_comment($url);
        $output[$id] = $comment;
    }

    return $output;
}

function get_comment($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $data_obj = json_decode($data);
    $comment = object_to_array($data_obj);

    return $comment;
}
