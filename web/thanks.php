<?php

require_once "config.php";
require_once "common.php";


function template_thanks() {
    global $G_UPLOAD_DIR, $G_ENTRIES_FILE;
    template_header();
?>
<p>Thanks for your entry</p>
<p>More blurb goes here...</p>
<br/>
<br/>
<br/>
<hr/>
<br/>
<br/>
<p>...and here's what the entries.csv file looks like now:</p>
<pre>
<?= file_get_contents( $G_ENTRIES_FILE); ?>
</pre>
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
