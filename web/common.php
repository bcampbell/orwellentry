<?php



function template_header() {
?><html>
<head>
<link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
<header>
<h1 class="logo"><a href="http://theorwellprize.co.uk">
 <img class="logo" alt="The Orwell Prize" src="http://theorwellprize.co.uk/wp-content/themes/orwell/images/logo.png" width="365" height="37" />
</a></h1>
<img class="slogan" src="http://theorwellprize.co.uk/wp-content/themes/orwell/images/slogan.png" width="464" height="70" alt="&lsquo;What I have most wanted to do...  is to make political writing into an art&rsquo;" />
</header>
<div class="main">
<?php
}



function template_footer() {
?>
</div>
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
