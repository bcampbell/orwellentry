<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";


class BookEntryForm extends Form {
    function __construct($data=null,$files=null) {
        $opts = array(
            'label_suffix'=>'', // suppress a trailing ':' after labels
            /* 'prefix'=>'test', */
        );

        parent::__construct($data,$files,$opts);
        // these should be opts?
        $this->error_css_class = 'fld-error';
        $this->required_css_class = 'fld-required';


        $this['book_title'] = new CharField(
            array( 'required'=>TRUE, 'label'=>"Title"));
        $this['publication_date'] = new DateField(array( 'required'=>FALSE, 'help_text'=>"" ));
        $this['author_first_name'] = new CharField(array( 'required'=>TRUE ));
        $this['author_last_name'] = new CharField(array( 'required'=>TRUE ));
        $this['book_cover'] = new FileField(array(
            'required'=>TRUE,
            'help_text'=>"A image file of the cover art, with no rights reserved.<br/>Please keep it below 2MB<br/>\nAccepted formats are png, jpeg, gif, tiff" ));
        $this['link_with_uk_or_ireland'] = new CharField(array(
            'label'=>'Link with UK or Ireland',
            'help_text'=>'Tell us how you are linked to UK or Ireland<br/>(including, but not limited to, residency, citizenship or first publication)'
        ));
        $this['author_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email", 'help_text'=>'Email address of the author'));
        $this['author_address'] = new CharField(array('required'=>FALSE, 'label'=>"Address", 'widget'=>'TextArea' ));
        $this['author_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Phone"));

        $this['publisher_email'] = new EmailField(array('required'=>FALSE));
        $this['publisher_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this['publisher_phone'] = new CharField(array('required'=>FALSE));
        $this['agent_email'] = new EmailField(array('required'=>FALSE));
        $this['agent_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this['agent_phone'] = new CharField(array('required'=>FALSE));
        $this['declaration'] = new BooleanField(array('label'=>"I agree"));

    }
}



class BookEntryHandler {
    function __construct() {
        global $g_config;
        $this->shortname = "book";
        $this->upload_dir = $g_config[$this->shortname]['upload_dir'];
        $this->entries_file = "{$this->upload_dir}/{$this->shortname}_entries.csv";

        $this->sanity_check();
    }

    function handle()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $f= new BookEntryForm($_POST,$_FILES);
            if($f->is_valid()) {
                $this->process($f);
                // redirect to prevent doubleposting
                header("HTTP/1.1 303 See Other");
                header("Location: /thanks");
                return;
            }
        } else {
            // provide an unbound form
            $f = new BookEntryForm(null,null);
        }

        $this->render_page($f);
    }



    // the main template for filing an entry
    function render_page( $f ) {
        // TODO: add a MAX_FILE_SIZE hidden element to enable early-out on
        // oversize files
        template_header();

        // TODO - check:
        // need to send in cover art?
        // deadline
        // pubdate eligiblity range
        // contact details (not katriona)
?>

<h1>Book Prize !!!2013: Entry form</h1>
<hr/>
<p>Four copies of each submitted book (and cover art?) should be sent to:<br/><br/>
<strong>!!!Person | The Orwell Prize | Address... | Address... | London A11 1AA</strong>
<p>
The submission deadline is <strong>!!!Wednesday 9th January, 2013</strong>.
</p>
<p>
Entry is FREE and there are no charges at any point. All books published for
the first time between 1st January !!!2013 and 31st December !!!2013 are eligible.
Entrants must have a clear relationship with the UK or Ireland (including,
but not limited to, residency, citizenship or first publication).
</p>
<p>The full list of rules is available on <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules">theorwellprize.co.uk</a>.</p>
<p>If you have any queries, please contact !!!katriona.lewis@mediastandardstrust.org or 0207 229 5722.</p>

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


    function sanity_check() {
        if(!file_exists($this->upload_dir)) {
            throw new Exception("Internal error - Output dir doesn't exist ({$this->upload_dir})");
        }
        if(!is_writable($this->upload_dir)) {
            throw new Exception("Internal error - Output dir isn't writable ({$this->upload_dir})");
        }
    }


    // cook the data to handle any uploaded cover images
    function cook_data(&$data) {
        if( $data['book_cover'] ) {
            // use the book title as the basis for filename
            $ext = pathinfo($data['book_cover']['name'], PATHINFO_EXTENSION);
            $cover_file = preg_replace("/[^a-zA-Z\.]/","", $data['book_title']);
            if(!$cover_file) {
                throw new Exception("Internal error - couldn't save cover image because of bad name ({$cover_file})");
            }
            $cover_file .= ".".$ext;

            if(move_uploaded_file($data['book_cover']['tmp_name'], "$this->upload_dir/$cover_file") !== TRUE) {
                throw new Exception("Internal error - couldn't save cover image ({$cover_file})");
            }

            $data['book_cover'] = $cover_file;
        }
    }

    // a valid form has been submitted - handle it!
    function process($f) {
        $this->sanity_check();

        $data = $f->cleaned_data;

        $this->cook_data($data);

        // add a new entry to the csv file

        // if starting new file, output field names in first row
        if(!file_exists($this->entries_file)) {
            $fieldnames = array_keys($data);
            if(file_put_contents($this->entries_file, join(',',$fieldnames) . "\n") === FALSE) {
                throw new Exception("Internal error - couldn't create entry list ({$this->entries_file})");
            }
        }

        // format a line of data
        $obuf = fopen('php://output', 'w');
        ob_start();
        fputcsv($obuf, $data);
        fclose($obuf);
        $line = ob_get_clean();

        // append it (with locking in case of simultaneous access!)
        if( file_put_contents( $this->entries_file, $line, FILE_APPEND|LOCK_EX) === FALSE ) {
            throw new Exception("Internal error - couldn't record details");
        }
    }


}

try {
    $v = new BookEntryHandler();
    $v->handle();
} catch(Exception $e) {
    template_pearshaped($e);
}

?>
