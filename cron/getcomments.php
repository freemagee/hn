<?php
/*******************************************************************************
 * VARIABLES
 ******************************************************************************/

$comments = [];
$dir = dirname(__FILE__);
$file = 'data.json';
$filename = $dir . '/../src/data/' . $file;
$source_file = file_get_contents($filename);
$source_obj = json_decode($source_file);
$source = object_to_array($source_obj);

/*******************************************************************************
 * LOGIC
 ******************************************************************************/

//pre_r($source);
if (!empty($source)) {
    $articles = count($source);

    for ($i = 0; $i < 1; $i++) {
        if (isset($source[$i]['kids'])) {
            $source[$i]['kids'] = get_kids($source[$i]['kids']);
        } else {
            //echo "No kids";
        }
    }
}

function get_kids($array) {
    $output = array();

    for ($i = 0; $i < count($array); $i++) {
        $id = $array[$i];
        $url = generate_comment_url($id);
        $comment = get_comment($url);
        $output['kids'][$i] = $comment;
    }

    return $output;
}

//pre_r($source);
// for ($i = 0, $count = count($source[0]['kids']); $i < $count; $i++) {
//     echo "<p>" . $source[0]['kids'][$i][1]['text'] . "</p>";
// }

/*******************************************************************************
 * FUNCTIONS
 ******************************************************************************/

function generate_comment_url($id) {
    return 'https://hacker-news.firebaseio.com/v0/item/' . $id . '.json';
}

function get_comment($url) {
    $time_start = microtime(true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $data_obj = json_decode($data);
    $comment = object_to_array($data_obj);
    echo 'Total execution time in seconds: ' . (microtime(true) - $time_start);

    return $comment;
}

/**
 * [pre_r | a pretty version of print_r - helps with debugging arrays]
 * @param  [array] $val
 */
function pre_r($val){
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

/**
 * [object_to_array description]
 * @param  [type] $d [description]
 * @return [type]    [description]
 */
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