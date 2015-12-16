<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Book Prize 2016: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>

<div class="content">
<h1>Book Prize 2016: Entry form</h1>
<p> The submission deadline is <em>15th January, 2016</em>.  </p>

<p>
Entry is FREE and there are no charges at any point. All books published for
the first time between 1st January 2015 and 31st December 2015 are eligible.
<p>
</p>
Entrants must have a clear relationship with the UK or Ireland (including,
but not limited to, residency, citizenship or first publication).
</p>

<p>Five copies of each submitted book should be sent to:
<div class="address">
    The Orwell Prize<br/>
    King's College London<br/>
    Virginia Woolf Building<br/>
    22 Kingsway<br/>
    London WC2B 6NR<br/>
</div>
</p>
<p>For details, see the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">full list of rules</a>.</p>

<form enctype="multipart/form-data" method="POST">

<?php if($f->errors) { ?>
<div class="form-error">Please correct the fields marked in red, then try submitting the form again</div>
<?php } ?>

<?= $f['async_upload_token'] ?>
<fieldset>
<legend>Book</legend>
<div class="fieldset-notes"><span>*</span> Required fields</div>
<?php fld($f['book_title']); ?>
<?php fld($f['publication_date']); ?>
<?php fld($f['author_first_name']); ?>
<?php fld($f['author_last_name']); ?>
<?php fld_select_with_other($f['link_with_uk_or_ireland'],$f['link_other']); ?>
<?php fld($f['book_cover']); ?>
<?php fld($f['primary_contact']); ?>
</fieldset>

<fieldset>
<legend>Author</legend>
<?php fld($f['author_email']); ?>
<?php fld($f['author_address']); ?>
<?php fld($f['author_phone']); ?>
<?php fld($f['author_twitter']); ?>
</fieldset>

<fieldset>
<legend>Publisher</legend>
<?php fld($f['publisher_name']); ?>
<?php fld($f['publisher_email']); ?>
<?php fld($f['publisher_address']); ?>
<?php fld($f['publisher_phone']); ?>
</fieldset>

<fieldset>
<legend>Agent</legend>
<?php fld($f['agent_name']); ?>
<?php fld($f['agent_email']); ?>
<?php fld($f['agent_address']); ?>
<?php fld($f['agent_phone']); ?>
</fieldset>

<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize for Books 2016, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.</p>
<?php fld_label_right($f['declaration']); ?>
</fieldset>

<fieldset>
<p>
I understand that, in the event of being long-listed for the Orwell Prize 2016, the author or authors
may be called upon to participate in workshops run by The Orwell Youth Prize, and to produce a
short piece of writing (no more than 500 words) for The Orwell Prize. I declare that the author or
authors understand this and consent to this requirement.
</p>
<?php fld_label_right($f['workshop_consent']); ?>
</fieldset>

<fieldset>
<p>
I understand that, in the event of winning the Orwell Prize 2016, the author or authors may be
called upon to appear at an event in 2016. I declare that the author or authors understand and
consent to this requirement.
</p>
<?php fld_label_right($f['event_consent']); ?>
</fieldset>

<input type="submit" value="Submit Entry"/>
</form>
</div>

<div class="sidebar">
<h3>Any questions?</h3>
<p>If you have any queries, please <a href="stephanie.lelievre@theorwellprize.co.uk">email us</a> or call 0207 848 7930.</p>
</div>

<script src="/helpers.js"></script>
<script>
selOrOther("link_with_uk_or_ireland", "link_other", "other");
inputs = ( document.querySelectorAll('input[type="file"]'));
for (var i=0; i<inputs.length; i++ ) {
    fancyUpload(inputs[i]);
}
</script>

<?php endblock() ?>
