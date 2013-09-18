<?php include 'base.php' ?>
<?php
// page for showing exceptions
//
// $err - the exception that was thrown

require_once "common.php";
?>

<?php startblock('main') ?>
<p>Uhoh... Something went wrong:</p>
<p><em><?=$err->getmessage(); ?></em></p>
<?php endblock() ?>
