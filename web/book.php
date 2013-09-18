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

        $relationship_choices = array(
            ''=>"-- please select --",
            'resident'=>"Residency in UK or Ireland",
            'citizen'=>"Citizen of UK or Ireland",
            'first_publication'=>"First publication of entry",
            'foreign_correspondent'=>"Foreign correspondent for British or Irish publication",
            'resident_at_time_of_writing'=>"Resident in the UK or Ireland at the time of writing or publication",
            'other'=>"Other (please specify)");

        $this['book_title'] = new CharField(
            array( 'required'=>TRUE, 'label'=>"Title"));
        $this['publication_date'] = new DateField(array( 'required'=>FALSE, 'help_text'=>"" ));
        $this['author_first_name'] = new CharField(array( 'required'=>TRUE ));
        $this['author_last_name'] = new CharField(array( 'required'=>TRUE ));
        $this['book_cover'] = new FileField(array(
            'label'=>"Cover art",
            'required'=>TRUE,
            'help_text'=>"A image file of the cover art for press with no rights reserved.<br/>Please keep it below 3MB<br/>" ));
        $this['link_with_uk_or_ireland'] = new ChoiceField(array(
            'label'=>'Relationship of entry to UK or Ireland',
            'choices'=>$relationship_choices,
            'help_text'=>'See point 9 of the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules/">rules</a> for details.'));
        $this['link_other'] = new CharField(array('required'=>FALSE,'label'=>""));
        $this['author_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email", 'help_text'=>'Email address of the author'));
        $this['author_twitter'] = new CharField(array('required'=>FALSE, 'label'=>"Twitter"));
        $this['author_address'] = new CharField(array('required'=>FALSE, 'label'=>"Address", 'widget'=>'TextArea' ));
        $this['author_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Phone"));

        $this['publisher_name'] = new CharField(array('required'=>TRUE, 'label'=>"Name"));
        $this['publisher_email'] = new EmailField(array('required'=>TRUE, 'label'=>"Email"));
        $this['publisher_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea', 'label'=>"Address" ));
        $this['publisher_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Telephone number"));

        $this['agent_name'] = new CharField(array('required'=>FALSE, 'label'=>"Name"));
        $this['agent_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email"));
        $this['agent_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea', 'label'=>"Address" ));
        $this['agent_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Telephone number"));
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
        include "templates/book.php";
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
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
