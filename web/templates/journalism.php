<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Journalism Prize 2014: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>

<h1>Journalism Prize 2014: Entry form</h1>
<hr/>
<p>
The submission deadline is <strong>15th January, 2014</strong>.
</p>
<p>
Journalism Prize entries should consist of between four and six submissions,
which may be printed articles (in print or online), blog posts, !!!radio broadcasts
or television packages. A byline photograph with no rights reserved must be
submitted with every entry.
Entry is FREE and there are no charges at any point. All work published for
the first time between 1st January 2013 and 31st December 2013 is eligible.
Entrants must have a clear relationship with the UK or Ireland (including,
but not limited to, residency, citizenship or first publication).
</p>
<p>The full list of rules is available on <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">theorwellprize.co.uk</a>.</p>
<p>If you have any queries, please contact theorwellprize@mediastandardstrust.org or 0207 848 7930.</p>

<form enctype="multipart/form-data" method="POST">
<?php if($f->errors) { ?>
<div class="form-error">Please correct the fields marked in red, then try submitting the form again</div>
<?php } ?>

<fieldset>
<legend>Journalist</legend>
<?php fld($f['journo_first_name']); ?>
<?php fld($f['journo_last_name']); ?>
<?php fld($f['journo_address']); ?>
<?php fld($f['journo_email']); ?>
<?php fld($f['journo_twitter']); ?>
<?php fld($f['journo_phone']); ?>
<?php fld($f['journo_photo']); ?>
<?php fld_select_with_other($f['link_with_uk_or_ireland'],$f['link_other']); ?>
</fieldset>

<fieldset>
<legend>Submissions (articles etc)</legend>
<?php for($n=1; $n<=6; ++$n) { ?>
<h3>Submission <?=$n?></h3>
<div class="fld-compact">
<?php fld($f["item_{$n}_title"]); ?>
<?php fld($f["item_{$n}_publication"]); ?>
<?php fld($f["item_{$n}_pubdate"]); ?>
<?php fld($f["item_{$n}_url"]); ?>
<?php fld($f["item_{$n}_copy"]); ?>
</div>
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
<p>I declare that this work, submitted for consideration for the Orwell Prize 2014, is wholly or substantially that of the names author or authors, and does not contain any plagiarised or unacknowledged material.</p>
<?php fld($f['declaration']); ?>
</fieldset>

<input type="submit" value="Submit Entry"/>
</form>

<script>
var select = document.getElementById("link_with_uk_or_ireland");
var other = document.getElementById("link_other");
other.style.display="none";
select.onchange=function(){
    if(select.value=="other"){
       other.style.display="block";
    } else {
       other.style.display="none";
    }
}
</script>
<?php endblock() ?>
