<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Prize for Exposing Britain's Social Evils 2016: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>
<div class="content">
    <h1>The Orwell Prize for Exposing Britain's Social Evils 2016: Entry form</h1>
    <p>The submission deadline is <em>15 January 2016</em></p>
    <p>
    An entry for The Orwell Prize for Exposing Britain's Social Evils
    should consist of a story that has enhanced the public understanding
    of social problems and public policy in the UK. It must be communicated
    across at least two of the following platforms: journalistic
    writing, video content, audio content, social media, or photojournalism. 
    </p>
    <p>
    The story must be clearly and primarily concerned with an aspect of
    UK society.
    </p>
    <p>
    A byline photograph with no rights reserved must be submitted with every
    entry.
    </p>

    <p>
    Entry is FREE and there are no charges at any point.
    All work published for the first time between
    1st January 2015 and 31st December 2015 is eligible.
    </p>
    
    <p>Entrants must have a clear relationship with the UK or Ireland (including, but not limited to,
    residency, citizenship or first publication).
    </p>

    <p>For details, see the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">full list of rules</a>.</p>

<form enctype="multipart/form-data" method="POST">
<?php if($f->errors) { ?>
<?php if(array_key_exists('__all__', $f->errors)) { ?>
<div class="form-error"><?= $f->errors['__all__'][0] ?></div>
<?php } else {?>
<div class="form-error">Please correct the fields marked in red, then try submitting the form again</div>
<?php } ?>
<?php } ?>

<?= $f['async_upload_token'] ?>
<fieldset>
    <legend>Entrant details</legend>
<div class="fieldset-notes"><span>*</span> Required fields</div>
<?php fld($f['title']); ?>
<p class="helptext">
Please enter your details below. If entering as part of a small team of journalists, please put their details in the 'Further information' box below.
</p>

<?php fld($f['journo_first_name']); ?>
<?php fld($f['journo_last_name']); ?>
<?php fld($f['journo_address']); ?>
<?php fld($f['journo_email']); ?>
<?php fld($f['journo_twitter']); ?>
<?php fld($f['journo_phone']); ?>
<?php fld($f['journo_photo']); ?>
<?php fld($f['further_info']); ?>
<?php fld_select_with_other($f['link_with_uk_or_ireland'],$f['link_other']); ?>
</fieldset>

<fieldset>
<legend>Journalistic writing</legend>

<?php
for($n=1; $n<=3; ++$n) {
?>
<fieldset class="submission-grp submission-grp-writing">
<legend><?= $n ?></legend>
<?php fld($f["writing_{$n}_title"]); ?>
<?php fld($f["writing_{$n}_publication"]); ?>
<?php fld($f["writing_{$n}_pubdate"]); ?>
<?php fld($f["writing_{$n}_url"]); ?>
<?php fld($f["writing_{$n}_copy"]); ?>
</fieldset>
<?php } ?>
</fieldset>

<fieldset>
<legend>Video content</legend>
<p>
Please provide permanent, accessible, and non-expiring URLs to video content.
URLs for Vimeo or YouTube videos would be ideal, and password protected
content would be fine.
</p>
<?php
for($n=1; $n<=3; ++$n) {
?>
<fieldset class="submission-grp submission-grp-video">
<legend><?= $n ?></legend>
<?php fld($f["video_{$n}_title"]); ?>
<?php fld($f["video_{$n}_provider"]); ?>
<?php fld($f["video_{$n}_pubdate"]); ?>
<?php fld($f["video_{$n}_url"]); ?>
<?php fld($f["video_{$n}_password"]); ?>
</fieldset>
<?php } ?>
</fieldset>

<fieldset>
<legend>Audio content</legend>
<p>
Please provide permanent, accessible, and non-expiring URLs to audio content. 
</p>
<?php
for($n=1; $n<=3; ++$n) {
?>
<fieldset class="submission-grp submission-grp-audio">
<legend><?= $n ?></legend>
<?php fld($f["audio_{$n}_title"]); ?>
<?php fld($f["audio_{$n}_provider"]); ?>
<?php fld($f["audio_{$n}_pubdate"]); ?>
<?php fld($f["audio_{$n}_url"]); ?>
<?php fld($f["audio_{$n}_password"]); ?>
</fieldset>
<?php } ?>
</fieldset>


<fieldset>
<legend>Social Media</legend>
<p>
Please upload a PDF of social media content to a maximum of 3000 characters
(or 20 tweets), or provide a link to a
'<a href="http://www.storify.com">Storify</a>' story of equivalent length.
</p>
<div class="social-media">
<?php fld($f["social_username"]); ?>
<?php fld($f["social_url"]); ?>
<?php fld($f["social_copy"]); ?>
</div>
</fieldset>



<fieldset>
<legend>Photojournalism</legend>
<?php
for($n=1; $n<=3; ++$n) {
?>
<fieldset class="submission-grp submission-grp-photo">
<legend><?= $n ?></legend>
<?php fld($f["photo_{$n}_title"]); ?>
<?php fld($f["photo_{$n}_date"]); ?>
<?php fld($f["photo_{$n}_publication"]); ?>
<?php fld($f["photo_{$n}_url"]); ?>
<?php fld($f["photo_{$n}_photo"]); ?>
</fieldset>
<?php } ?>
</fieldset>


<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize 2015, is wholly or substantially that of the names author or authors, and does not contain any plagiarised or unacknowledged material.</p>
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

    hideShow('.submission-grp-writing',0,"Add a writing submission");
    hideShow('.submission-grp-video',0, "Add a video submission");
    hideShow('.social-media',0, "Add a social media submission");
    hideShow('.submission-grp-audio',0, "Add an audio submission");
    hideShow('.submission-grp-photo',0, "Add a photo submission");


    inputs = ( document.querySelectorAll('input[type="file"]'));
    for (var i=0; i<inputs.length; i++ ) {
        fancyUpload(inputs[i]);
    }
</script>

<?php endblock() ?>
