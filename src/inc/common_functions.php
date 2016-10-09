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