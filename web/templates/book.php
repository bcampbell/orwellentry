<?php require_once "common.php"; ?>
<?php include 'base.php' ?>

<?php startblock('title') ?>The Orwell Journalism Prize 2014: Entry form<?php endblock() ?>

<?php startblock('head_extra') ?>
<?php endblock() ?>


<?php startblock('main') ?>

<h1>Book Prize 2014: Entry form</h1>
<hr/>
<p>Five copies of each submitted book should be sent to:<br/><br/>
<strong>
The Orwell Prize<br/>
Kings College London<br/>
Strand Bridge House<br/>
138-142 The Strand<br/>
London WC2R 2LS
</strong>
<p>
The submission deadline is <strong>15th January, 2014</strong>.
</p>
<p>
Entry is FREE and there are no charges at any point. All books published for
the first time between 1st January 2013 and 31st December 2013 are eligible.
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
<legend>Book</legend>
<?php fld($f['book_title']); ?>
<?php fld($f['publication_date']); ?>
<?php fld($f['author_first_name']); ?>
<?php fld($f['author_last_name']); ?>
<?php fld_select_with_other($f['link_with_uk_or_ireland'],$f['link_other']); ?>
<?php fld($f['book_cover']); ?>
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
<legend>Publisher</legend>
<?php fld($f['agent_name']); ?>
<?php fld($f['agent_email']); ?>
<?php fld($f['agent_address']); ?>
<?php fld($f['agent_phone']); ?>
</fieldset>

<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize for Books 2014, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.</p>
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
