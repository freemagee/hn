<?php
/*
 * INCLUDES
 */

require_once realpath(__DIR__.'/..').'/src/inc/common_functions.php';

/*
 * VARIABLES
 */

$articles = [];
$dir      = realpath(__DIR__.'/..').'/src/data/';
$file     = 'articles.json';
$filename = $dir.$file;
$top_list = '';

/*
 * LOGIC
 */

if (file_exists($filename)) {
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
} else {
    create_file($dir, 'articles', 'json');
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
}

/*
 * FUNCTIONS
 */


/**
 * Get list of top Hacker News stories via API
 *
 * @return array list of top story IDs
 */
function get_list()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://hacker-news.firebaseio.com/v0/topstories.json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $list     = curl_exec($ch);
    $list_obj = json_decode($list);
    $list     = object_to_array($list_obj);

    return $list;

}//end get_list()


/**
 * Cycle through top HN stories and get each set of data
 *
 * @param array  $output list of top story data
 * @param array  $source list of top story IDs
 * @param string $dir    absolute path
 */
function save_article_list($output, $source, $dir)
{
    $count = 0;
    $limit = 60;
    $ch    = curl_init();

    for ($count; $count < $limit; $count++) {
        $id = $source[$count];
        curl_setopt($ch, CURLOPT_URL, 'https://hacker-news.firebaseio.com/v0/item/'.$id.'.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $article     = curl_exec($ch);
        $article_obj = json_decode($article);
        $output[]    = object_to_array($article_obj);
    }

    $json_data = json_encode($output);
    file_put_contents($dir.'/articles.json', $json_data);

}//end save_article_list()
