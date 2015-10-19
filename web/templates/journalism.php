<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Journalism Prize 2015: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>
<div class="content">
<h1>Journalism Prize 2016: Entry form</h1>
<p>
The submission deadline is <em>15 January 2016</em>.
</p>
<p>
Journalism Prize entries should consist of at least four and no more than
six submissions, which may be articles (in print or online) or blog posts.
</p>
<p>
A byline photograph with no rights reserved must be
submitted with every entry.
</p>
<p>
Entry is FREE and there are no charges at any point. All work published for
the first time between 1st January 2015 and 31st December 2015 is eligible.
</p>
<p>
Entrants must have a clear relationship with the UK or Ireland (including,
but not limited to, residency, citizenship or first publication).
</p>
<p>For details, see the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">full list of rules</a>.</p>

<form enctype="multipart/form-data" method="POST">
<?php if($f->errors) { ?>
<div class="form-error">Please correct the fields marked in red, then try submitting the form again</div>
<?php } ?>

<?= $f['async_upload_token'] ?>
<fieldset>
<legend>Journalist</legend>
<div class="fieldset-notes"><span>*</span> Required fields</div>
<?php fld($f['journo_first_name']); ?>
<?php fld($f['journo_last_name']); ?>
<?php fld($f['journo_email']); ?>
<?php fld($f['journo_address']); ?>
<?php fld($f['journo_twitter']); ?>
<?php fld($f['journo_phone']); ?>
<?php fld($f['journo_photo']); ?>
<?php fld_select_with_other($f['link_with_uk_or_ireland'],$f['link_other']); ?>
</fieldset>

<fieldset>
<legend>Submissions (articles etc)</legend>

<?php
$nums = array('zero','one','two','three','four','five','six');
for($n=1; $n<=6; ++$n) {
?>
<fieldset class="submission-grp">
<legend><?= $n ?></legend>
<?php fld($f["item_{$n}_title"]); ?>
<?php fld($f["item_{$n}_publication"]); ?>
<?php fld($f["item_{$n}_pubdate"]); ?>
<?php fld($f["item_{$n}_url"]); ?>
<?php fld($f["item_{$n}_copy"]); ?>
</fieldset>
<?php } ?>
</fieldset>

<fieldset>
<legend>Professional Reference</legend>
<?php fld($f['publication_contact']); ?>
<?php fld($f['publication_email']); ?>
<?php fld($f['publication_phone']); ?>
<?php fld($f['publication_address']); ?>
</fieldset>


<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize 2016, is wholly or substantially that of the named author or authors, and does not contain any plagiarised or unacknowledged material.</p>

<?php fld_label_right($f['declaration']); ?>
</fieldset>

<fieldset>
<p>
I understand that, in the event of being long-listed for the Orwell Prize 2016, the author or authors
may be called upon to participate in workshops run by The Orwell Youth Prize, and to produce a
short piece of writing (no more than 500 words) for The Orwell Prize. I declare that the author or
authors understand this and consent to this requirement.
</p>

<?php fld_label_right($f['workshop']); ?>
</fieldset>

<input type="submit" value="Submit Entry"/>
</form>
</div>

<div class="sidebar">
<h3>Any questions?</h3>

<p>If you have any queries, please <a href="mailto:stephanie.lelievre@theorwellprize.co.uk">email us</a> or call 0207 848 7930.</p>
</div>


<script src="/helpers.js"></script>
<script>
selOrOther("link_with_uk_or_ireland", "link_other", "other");
hideShow('.submission-grp',4, "Add another submission");

inputs = ( document.querySelectorAll('input[type="file"]'));
for (var i=0; i<inputs.length; i++ ) {
    fancyUpload(inputs[i]);
}
</script>
<?php endblock() ?>
