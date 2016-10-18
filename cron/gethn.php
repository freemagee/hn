<?php
/*******************************************************************************
* INCLUDES
 ******************************************************************************/

include_once(realpath(__DIR__ . '/..') . '/src/inc/common_functions.php');

/*******************************************************************************
 * VARIABLES
 ******************************************************************************/

$articles = [];
$dir = realpath(__DIR__ . '/..') . '/src/data/';
$file = 'data.json';
$filename = $dir . $file;
$top_list = '';

/*******************************************************************************
 * LOGIC
 ******************************************************************************/

if (file_exists($filename)) {
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
} else {
    create_file($dir, 'data', 'json');
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
}

/*******************************************************************************
 * FUNCTIONS
 ******************************************************************************/

/**
 * [get_list get list of top Hacker News stories via API]
 * @return [array] [list of top story IDs]
 */
function get_list() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://hacker-news.firebaseio.com/v0/topstories.json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $list = curl_exec($ch);
    $list_obj = json_decode($list);
    $list = object_to_array($list_obj);

    return $list;
}

/**
 * [save_article_list Cycle through top HN stories and get each set of data]
 * @param  [array] $output [list of top story data]
 * @param  [array] $source [list of top story IDs]
 * @param  [str] $dir    [absolute path]
 */
function save_article_list($output, $source, $dir) {
    $count = 0;
    $limit = 60;
    $ch = curl_init();

    for ($count; $count < $limit; $count++) {
        $id = $source[$count];
        curl_setopt($ch, CURLOPT_URL, 'https://hacker-news.firebaseio.com/v0/item/' . $id . '.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $article = curl_exec($ch);
        $article_obj = json_decode($article);
        $output[] = object_to_array($article_obj);
    }

    $json_data = json_encode($output);
    file_put_contents($dir . '/data.json', $json_data);
}

/**
 * [create_file if file does not exist, create it]
 * @param  [str] $dir [path to place file]
 * @param  [str] $n   [file name]
 * @param  [str] $xt  [file extension]
 */
function create_file($dir, $n, $xt) {
    fopen($dir . $n . '.' . $xt, "w");
}
