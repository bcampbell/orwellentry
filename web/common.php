<?php

// little helper template to output a single field
function fld($f, $extra_css="") {
?>
<div class="fld <?=$extra_css?> <?=$f->css_classes() ?>">
<?= $f->label_tag() ?>
<?= $f ?>
<span class="helptext"><?= $f->help_text ?></span>
<?php if($f->errors) { ?>
<ul class="errorlist">
<?php foreach($f->errors as $err) { ?>
<li><?= $err ?></li>
</ul><?php } ?>
<?php } ?>
</div>
<?php
}


function fld_select_with_other($f, $other) {
?>
<div class="fld <?=$f->css_classes() ?>">
<?=$f->label_tag() ?>
<?=$f ?>
<?=$other ?>
<span class="helptext"><?= $f->help_text ?></span>
<?php if($f->errors) { ?>
<ul class="errorlist">
<?php foreach($f->errors as $err) { ?>
<li><?= $err ?></li>
</ul><?php } ?>
<?php } ?>
</div>
<?php
}






?>
