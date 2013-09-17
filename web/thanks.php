<?php

require_once "config.php";
require_once "common.php";


function template_thanks() {
    template_header();
?>
<p>Thanks for your entry</p>
<p>More blurb goes here...</p>
<br/>
<?php
    template_footer();
}



function view() {
    template_thanks();
}

try {
    view();
} catch(Exception $e) {
    template_pearshaped($e);
}

?>
