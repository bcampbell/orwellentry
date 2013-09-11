<?php



function template_header() {
?><html>
<head>
<link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
<h1>The Orwell prize</h1>
<hr/>
<?php
}



function template_footer() {
?>
<hr/>
</body>
</html>
<?php
}





// display an error page
function template_pearshaped( $err ) {
    template_header();
?>
<p>Uhoh... Something went wrong:</p>
<p><em><?=$err->getmessage(); ?></em></p>
<?php
    template_footer();
}


?>
