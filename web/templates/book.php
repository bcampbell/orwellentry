<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Journalism Prize 2014: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>

<div class="content">
<h1>Book Prize 2014: Entry form</h1>
<p>Five copies of each submitted book should be sent to:
<div class="address">
The Orwell Prize<br/>
Kings College London<br/>
Strand Bridge House<br/>
138-142 The Strand<br/>
London WC2R 1HH
</div>
<p>
The submission deadline is <em>Wednesday 15th January, 2014</em>.
</p>
<p>
Entry is FREE and there are no charges at any point. All books published for
the first time between 1st January 2013 and 31st December 2013 are eligible.
Entrants must have a clear relationship with the UK or Ireland (including,
but not limited to, residency, citizenship or first publication).
</p>
<p>For details, see the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">full list of rules</a>.</p>

<form enctype="multipart/form-data" method="POST">

<?php if($f->errors) { ?>
<div class="form-error">Please correct the fields marked in red, then try submitting the form again</div>
<?php } ?>

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
<p>I declare that this work, submitted for consideration for the Orwell Prize for Books 2014, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.</p>
<?php fld_label_right($f['declaration']); ?>
</fieldset>

<input type="submit" value="Submit Entry"/>
</form>
</div>

<div class="sidebar">
<h3>Any questions?</h3>
<p>If you have any queries, please <a href="mailto:theorwellprize@mediastandardstrust.org">email us</a> or call 0207 848 7930.</p>
</div>

<script>
var select = document.getElementById("link_with_uk_or_ireland");
var other = document.getElementById("link_other");
var f=function(){
    if(select.value=="other"){
       other.style.display="block";
    } else {
       other.style.display="none";
    }
}
select.onchange=f;
f();
</script>
<?php endblock() ?>
