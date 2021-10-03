<?php

ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

session_set_cookie_params(432000);

function ami_interface_url($route, $args=array()) {

    global $SITE_LINK;

    $qs = '';

    if (!empty($args)) {
        $qs = '&' . http_build_query($args);
    }

    return 'main.interface.php?route=' . $route . $qs;

}

// Fix for removed Session functions 
function fix_session_register(){ 
    function session_register(){ 
        $args = func_get_args(); 
        foreach ($args as $key){ 
            $_SESSION[$key]=$GLOBALS[$key]; 
        } 
    } 
    function session_is_registered($key){ 
        return isset($_SESSION[$key]); 
    } 
    function session_unregister($key){ 
        unset($_SESSION[$key]); 
    } 
} 
if (!function_exists('session_register')) fix_session_register(); 

/* CONNECT TO DB */

define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "fveapdmkgw");
define("MYSQL_PASSWORD", "vSr7vDFCj4");
define("MYSQL_DATABASE", "fveapdmkgw");

mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);

//$db = mysqli_connect("localhost", "fveapdmkgw", "vSr7vDFCj4", "fveapdmkgw") or die ("Error: Could not connect to database.");
mysql_select_db(MYSQL_DATABASE) or die ("db select failed");
?>
