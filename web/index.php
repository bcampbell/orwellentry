<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";

# TODO: prevent files from overwriting each other
# TODO: indicate required fields
# TODO: if errors, show an indicator at top of form

class BookEntryForm extends Form {
    function __construct($data=null,$files=null, $opts=null) {
        parent::__construct($data,$files,$opts);
        $disclaimer = "I declare that this work, submitted for consideration for the Orwell Prize for Books 2013, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.";

        $this->fields['book_title'] = new CharField(
            array( 'required'=>TRUE, 'label'=>"Title", 'help_text'=>"e.g. Zapp Brannigan's Big Book of War"));
        $this->fields['author_first_name'] = new CharField(array( 'required'=>TRUE ));
        $this->fields['author_last_name'] = new CharField(array( 'required'=>TRUE ));
        $this->fields['book_cover'] = new FileField(array( 'required'=>FALSE, 'help_text'=>"Please keep it below 2MB<br/>\nAccepted formats: png, jpeg, gif, tiff" ));
        // TODO: publication_month (use regex?)
        $this->fields['link_with_uk_or_ireland'] = new CharField(array(
            'label'=>'Link with UK or Ireland',
            'help_text'=>'Tell us how you are linked to UK or Ireland<br/>(including, but not limited to, residency, citizenship or first publication)'
        ));
        $this->fields['author_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email", 'help_text'=>'Email address of the author'));
        $this->fields['author_address'] = new CharField(array('required'=>FALSE, 'label'=>"Address", 'widget'=>'TextArea' ));
        $this->fields['author_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Phone"));

        $this->fields['publisher_email'] = new EmailField(array('required'=>FALSE));
        $this->fields['publisher_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this->fields['publisher_phone'] = new CharField(array('required'=>FALSE));
        $this->fields['agent_email'] = new EmailField(array('required'=>FALSE));
        $this->fields['agent_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this->fields['agent_phone'] = new CharField(array('required'=>FALSE));
        $this->fields['declaration'] = new BooleanField(array('label'=>"I agree"));

        $this->error_css_class = 'fld-error';
        $this->required_css_class = 'fld-required';
    }
}



function view()
{
    $form_opts = array(
        'label_suffix'=>'',
        /* 'prefix'=>'test', */
    );

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $f= new BookEntryForm($_POST,$_FILES,$form_opts);
        if($f->is_valid()) {
            process($f);
            // redirect to prevent doubleposting
            header("HTTP/1.1 303 See Other");
            header("Location: /thanks");
            return;
        }
    } else {
        // provide an unbound form
        $f = new BookEntryForm(null,null,$form_opts);
    }

    template_enter($f);
}


// little helper template to output a single field
function fld($f) {
?>
<div class="fld <?=$f->css_classes() ?>">
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


// the main template for filing an entry
function template_enter( $f ) {
    // TODO: add a MAX_FILE_SIZE hidden element to enable early-out on
    // oversize files
    template_header();
?>

<h1>Book Prize 2013: Entry form</h1>
<hr/>
<p>blurb goes here...</p>

<form enctype="multipart/form-data" action="" method="POST">
<fieldset>
<legend>Book</legend>
<?php fld($f['book_title']); ?>
<?php /* fld($f['publication_date']); */?>
<?php fld($f['author_first_name']); ?>
<?php fld($f['author_last_name']); ?>
<?php fld($f['link_with_uk_or_ireland']); ?>
<?php fld($f['book_cover']); ?>
</fieldset>


<fieldset>
<legend>Author</legend>
<?php fld($f['author_email']); ?>
<?php fld($f['author_address']); ?>
<?php fld($f['author_phone']); ?>
</fieldset>

<fieldset>
<legend>Publisher</legend>
<?php fld($f['publisher_email']); ?>
<?php fld($f['publisher_address']); ?>
<?php fld($f['publisher_phone']); ?>
</fieldset>

<fieldset>
<legend>Agent</legend>
<?php fld($f['agent_email']); ?>
<?php fld($f['agent_address']); ?>
<?php fld($f['agent_phone']); ?>
</fieldset>

<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize for Books 2013, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.</p>
<?php fld($f['declaration']); ?>
</fieldset>

<input type="submit" value="Submit Entry"/>
</form>
<br/>

<?php
    template_footer();
}




// a valid form has been submitted - handle it!
function process($f) {
    global $G_UPLOAD_DIR, $G_ENTRIES_FILE;

    if(!file_exists($G_UPLOAD_DIR)) {
        throw new Exception("Internal error - Output dir doesn't exist (${G_UPLOAD_DIR})");
    }
    if(!is_writable($G_UPLOAD_DIR)) {
        throw new Exception("Internal error - Output dir isn't writable (${G_UPLOAD_DIR})");
    }

    $data = $f->cleaned_data;

    // handle any uploaded cover images
    if( $data['book_cover'] ) {
        // use the book title as the basis for filename
        $ext = pathinfo($data['book_cover']['name'], PATHINFO_EXTENSION);
        $cover_file = preg_replace("/[^a-zA-Z\.]/","", $data['book_title']);
        if(!$cover_file) {
            throw new Exception("Internal error - couldn't save cover image because of bad name (${cover_file})");
        }
        $cover_file .= ".".$ext;

        if(move_uploaded_file($data['book_cover']['tmp_name'], "$G_UPLOAD_DIR/$cover_file") !== TRUE) {
            throw new Exception("Internal error - couldn't save cover image $G_UPLOAD_DIR/$cover_file");
        }

        $data['book_cover'] = $cover_file;
    }


    // add a new entry to the csv file

    // if starting new file, output field names in first row
    if(!file_exists($G_ENTRIES_FILE)) {
        $fieldnames = array_keys($data);
        if(file_put_contents($G_ENTRIES_FILE, join(',',$fieldnames) . "\n") === FALSE) {
            throw new Exception("Internal error - couldn't create entry list ($G_ENTRIES_FILE)");
        }
    }

    // format a line of data
    $obuf = fopen('php://output', 'w');
    ob_start();
    fputcsv($obuf, $data);
    fclose($obuf);
    $line = ob_get_clean();

    // append it (with locking in case of simultaneous access!)
    if( file_put_contents( $G_ENTRIES_FILE, $line, FILE_APPEND|LOCK_EX) === FALSE ) {
        throw new Exception("Internal error - couldn't record details");
    }
}



try {
    view();
} catch(Exception $e) {
    template_pearshaped($e);
}
?>
