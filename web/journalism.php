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


        $relationship_choices = array(
            ''=>"-- please select --",
            'resident'=>"Residency in UK or Ireland",
            'citizen'=>"Citizen of UK or Ireland",
            'first_publication'=>"First publication of entry",
            'foreign_correspondent'=>"Foreign correspondent for British or Irish publication",
            'resident_at_time_of_writing'=>"Resident in the UK or Ireland at the time of writing or publication",
            'other'=>"Other (please specify)");

        $this['journo_first_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"First name"));
        $this['journo_last_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"Last name"));
        $this['journo_address'] = new CharField(array('required'=>FALSE, 'label'=>"Correspondence address", 'widget'=>'TextArea' ));
        $this['journo_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email" ));
        $this['journo_twitter'] = new CharField(array('required'=>FALSE, 'label'=>"Twitter"));
        $this['journo_phone'] = new CharField(array('required'=>FALSE, 'label'=>"Telephone number"));
        $this['link_with_uk_or_ireland'] = new ChoiceField(array(
            'label'=>'Relationship of entry to UK or Ireland',
            'choices'=>$relationship_choices,
            'help_text'=>'See point 9 of the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules/">rules</a> for details.'));
        $this['link_other'] = new CharField(array('required'=>FALSE,'label'=>""));

        $this["journo_photo"] = new FileField(array(
            'required'=>TRUE,
            'label'=>"Photograph",
            'help_text'=>"A byline photograph, with no rights reserved.<br/>Please keep it below 1MB",
        ));

        for( $n=1; $n<=6; ++$n) {
            $req = ($n<=4)?TRUE:FALSE;
            $this["item_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["item_{$n}_publication"] = new CharField(array('required'=>$req,'label'=>'Publication'));
            $this["item_{$n}_pubdate"] = new DateField(array('required'=>$req,'label'=>'Date of first publication'));
            $this["item_{$n}_url"] = new URLField(array('required'=>FALSE,'label'=>'URL'));
            $this["item_{$n}_copy"] = new FileField(array('required'=>$req,'label'=>'Copy', 'help_text'=>"PDF only, please"));
        }

        $this['publication_contact'] = new CharField(array('required'=>FALSE, 'label'=>'Contact name'));
        $this['publication_email'] = new EmailField(array('required'=>FALSE, 'label'=>'Email address'));
        $this['publication_phone'] = new CharField(array('required'=>FALSE, 'label'=>'Telephone number'));
        $this['publication_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea', 'label'=>'Address' ));

        $this['declaration'] = new BooleanField(array('label'=>"I agree"));

    }


    //
    function clean() {
        // make sure that link is filled in if dropdown is set to "other"
        $link = $this->cleaned_data['link_with_uk_or_ireland'];
        $link_other = $this->cleaned_data['link_other'];
        if($link=='other' && !$link_other) {
            print("<pre>'$link' '$link_other'</pre>");
            $this->_errors["link_with_uk_or_ireland"] = array("Please specify the link to the UK or Ireland");
            unset($this->cleaned_data['link_with_uk_or_ireland']);
            unset($this->cleaned_data['link_other']);
        }

        return $this->cleaned_data;
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
                header("Location: /thanks?entered={$this->shortname}");
                return;
            }
        } else {
            // provide an unbound form
            $f = new JournalismEntryForm(null,null);
        }

        $this->render_page($f);
    }

    function render_page( $f ) {
        include "templates/journalism.php";
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
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
