<?php
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

/**
 * [create_file if file does not exist, create it]
 * @param  [str] $dir [path to place file]
 * @param  [str] $n   [file name]
 * @param  [str] $xt  [file extension]
 */
function create_file($dir, $n, $xt) {
    makeDir($dir);
    fopen($dir . $n . '.' . $xt, "w");
}

/**
 * Make dir helper checks dir exists then creates it.
 * @param  [str] $path [path to create]
 * @return
 */
function makeDir($path) {
    return is_dir($path) || mkdir($path);
}
