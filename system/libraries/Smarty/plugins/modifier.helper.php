<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File:     modifier.helper.php
* Type:     modifier
* Name:     helper
* Purpose:  Call O2System helpers from within Smarty.
* -------------------------------------------------------------
*/
function smarty_modifier_helper($string, $helper_file, $helper_func)
{
    if (!function_exists("get_instance")) {
        return "Can't get O2System instance";
    }

    if (!function_exists($helper_func)) {
        $O2 =& get_instance();
        $O2->load->helper($helper_file);
    }

    // Get all the params passed in as there might be a few
    $params = func_get_args();

    // String provided should be the first param and we dont want helper file or helper func being passed
    $params[0] = $string;
    unset($params[1]);
    unset($params[2]);

    // Call the function with the params provided
    return call_user_func_array($helper_func, array_values($params));
}
?>