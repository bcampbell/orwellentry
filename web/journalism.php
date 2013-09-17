<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";

# TODO: prevent files from overwriting each other

class JournalismEntryForm extends Form {
    function __construct($data=null,$files=null) {
        $opts = array(
            'label_suffix'=>'', // suppress a trailing ':' after labels
            /* 'prefix'=>'test', */
        );

        parent::__construct($data,$files,$opts);
        // these should be opts?
        $this->error_css_class = 'fld-error';
        $this->required_css_class = 'fld-required';


        $this['journo_first_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"First name"));
        $this['journo_last_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"Last name"));
        $this['journo_address'] = new CharField(array('required'=>FALSE, 'label'=>"Address", 'widget'=>'TextArea' ));
        $this['journo_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email" ));
        $this['journo_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Telephone number"));
        $this['link_with_uk_or_ireland'] = new CharField(array(
            'label'=>'Relationship of entry to UK or Ireland',
            'help_text'=>'Tell us how you are linked to UK or Ireland<br/>(including, but not limited to, residency, citizenship or first publication)',
        ));
        $this["journo_photo"] = new FileField(array(
            'required'=>TRUE,
            'label'=>"Photograph",
            'help_text'=>"A byline photograph, with no rights reserved.<br/>Please keep it below 1MB",
        ));

        for( $n=1; $n<=6; ++$n) {
            $this["item_{$n}_title"] = new CharField(array('required'=>FALSE));
            $this["item_{$n}_publication"] = new CharField(array('required'=>FALSE));
            $this["item_{$n}_pubdate"] = new DateField(array('required'=>FALSE));
            $this["item_{$n}_url"] = new URLField(array('required'=>FALSE));
            $this["item_{$n}_copy"] = new FileField(array('required'=>FALSE));
        }

        $this['publication_contact'] = new CharField(array('required'=>FALSE, 'label'=>'Contact name'));
        $this['publication_email'] = new EmailField(array('required'=>FALSE, 'label'=>'Email address'));
        $this['publication_phone'] = new CharField(array('required'=>FALSE, 'label'=>'Telephone number'));
        $this['publication_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea', 'label'=>'Address' ));

        $this['declaration'] = new BooleanField(array('label'=>"I agree"));

    }
}



class JournalismEntryHandler {
    function __construct() {
        global $g_config;
        $this->shortname = "journalism";
        $this->upload_dir = $g_config[$this->shortname]['upload_dir'];
        $this->entries_file = "{$this->upload_dir}/{$this->shortname}_entries.csv";

        $this->sanity_check();
    }

    function handle()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $f= new JournalismEntryForm($_POST,$_FILES);
            if($f->is_valid()) {
                $this->process($f);
                // redirect to prevent doubleposting
                header("HTTP/1.1 303 See Other");
                header("Location: /thanks");
                return;
            }
        } else {
            // provide an unbound form
            $f = new JournalismEntryForm(null,null);
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

<h1>Journalism Prize !!!2013: Entry form</h1>
<hr/>
<p>
The submission deadline is <strong>!!! Wednesday 9th January, 2013</strong>.
</p>
<p>
Journalism Prize entries should consist of between four and six items,
which may be printed articles (in print or online), blog posts, radio broadcasts
or television packages. A byline photograph with no rights reserved must be
submitted with every entry.
Entry is FREE and there are no charges at any point. All work published for
the first time between 1st January !!!2013 and 31st December !!!2013 is eligible.
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
<legend>Journalist</legend>
<?php fld($f['journo_first_name']); ?>
<?php fld($f['journo_last_name']); ?>
<?php fld($f['journo_address']); ?>
<?php fld($f['journo_email']); ?>
<?php fld($f['journo_phone']); ?>
<?php fld($f['journo_photo']); ?>
<?php fld($f['link_with_uk_or_ireland']); ?>
</fieldset>

<fieldset>
<legend>Items (articles, broadcasts, etc)</legend>
<?php for($n=1; $n<=6; ++$n) { ?>
<?php fld($f["item_{$n}_title"]); ?>
<?php fld($f["item_{$n}_publication"]); ?>
<?php fld($f["item_{$n}_pubdate"]); ?>
<?php fld($f["item_{$n}_url"]); ?>
<?php fld($f["item_{$n}_copy"]); ?>
<?php } ?>
</fieldset>

<fieldset>
<legend>Publication</legend>
<?php fld($f['publication_contact']); ?>
<?php fld($f['publication_email']); ?>
<?php fld($f['publication_phone']); ?>
<?php fld($f['publication_address']); ?>
</fieldset>


<fieldset>
<legend>Disclaimer</legend>
<p>I declare that this work, submitted for consideration for the Orwell Prize !!!YEAR, is wholly or substantially that of the names author or authors, and does not contain any plagiarised or unacknowledged material.</p>
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


    function cook_file(&$data, $filefield, $namebase) {
        if($data[$filefield]) {
            // use namebase as the basis for filename
            $ext = pathinfo($data[$filefield]['name'], PATHINFO_EXTENSION);
            $cooked_file = strtolower(preg_replace("/[^-_0-9a-zA-Z\.]/","", $namebase));
            if(!$cooked_file) {
                throw new Exception("Internal error - couldn't save {$filefield} because of bad name ({$cooked_file})");
            }
            $cooked_file .= ".".$ext;

            if(move_uploaded_file($data[$filefield]['tmp_name'], "{$this->upload_dir}/{$cooked_file}") !== TRUE) {
                throw new Exception("Internal error - couldn't save {$filefield} ({$this->upload_dir}/{$cooked_file})");
            }

            $data[$filefield] = $cooked_file;
        }
    }


    function cook_data(&$data) {
        // cook the data to handle any uploaded cover images
        $this->cook_file($data, "journo_photo", "{$data['journo_first_name']}_{$data['journo_last_name']}");
        for($n=1; $n<=6; ++$n) {
            $this->cook_file($data, "item_{$n}_copy", "{$data['journo_first_name']}_{$data['journo_last_name']}_item_{$n}");
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
    $v = new JournalismEntryHandler();
    $v->handle();
} catch(Exception $e) {
    template_pearshaped($e);
}

?>
