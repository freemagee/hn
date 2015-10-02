<?php
set_time_limit(60);

/*******************************************************************************
 * VARIABLES
 ******************************************************************************/

$articles = [];
$dir = dirname(__FILE__);
$file = 'data.json';
$filename = $dir . '/../src/data/' . $file;
$top_list = '';

/*******************************************************************************
 * LOGIC
 ******************************************************************************/

if (file_exists($filename)) {
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
} else {
    create_file($dir . '/../src/data/', 'data', 'json');
    $top_list = get_list();
    save_article_list($articles, $top_list, $dir);
}

/*******************************************************************************
 * FUNCTIONS
 ******************************************************************************/

function get_list() {
    $list = file_get_contents('https://hacker-news.firebaseio.com/v0/topstories.json');
    $list_obj = json_decode($list);
    $list = object_to_array($list_obj);

    return $list;
}

function save_article_list($output, $source, $dir) {
    $count = 0;
    $limit = 60;

    for ($count; $count < $limit; $count++) {
        $id = $source[$count];
        $article = file_get_contents('https://hacker-news.firebaseio.com/v0/item/' . $id . '.json');
        $article_obj = json_decode($article);
        $output[] = object_to_array($article_obj);
    }

    $json_data = json_encode($output);
    file_put_contents($dir . '/../src/data/data.json', $json_data);
}

function create_file($dir, $n, $xt) {
    fopen($dir . $n . '.' . $xt, "w");
}

function pre_r($val){
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

function object_to_array($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}
?>