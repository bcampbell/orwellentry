<?php
require_once "config.php";
require_once "common.php";

function view() {
    global $g_config;
    $which = $_GET['entered'];
    if(!array_key_exists($which,$g_config)) {
        throw new Exception("Bad param (entered)");
    }
    include "templates/thanks_{$which}.php";
}

try {
    view();
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
