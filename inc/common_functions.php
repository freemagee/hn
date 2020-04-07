<?php


/**
 * A pretty version of print_r - helps with debugging arrays
 *
 * @param array $val An array to output.
 *
 * @return void
 */
function prettyPrint(array $val)
{
    echo '<pre>';
    print_r($val);
    echo '</pre>';

}//end prettyPrint()


/**
 * Turns an object into an array.
 *
 * @param object $obj The object.
 *
 * @return array
 */
function objectToArray(object $obj)
{
    if (is_object($obj) === true) {
        // Gets the properties of the given object with get_object_vars function.
        $obj = get_object_vars($obj);
    }

    if (is_array($obj) === true) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */

        return array_map(__FUNCTION__, $obj);
    } else {
        // Return array.
        return $obj;
    }

}//end objectToArray()
