<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";

# TODO: prevent files from overwriting each other

class JournalismEntryForm extends Form {
    function __construct($data=null,$files=null,$handler=null) {
        global $g_entry_year;
        $this->handler = $handler;
        $opts = array(
            'label_suffix'=>'', // suppress a trailing ':' after labels
            /* 'prefix'=>'test', */
        );

        $this->filefields = array('journo_photo',
            'item_1_copy',
            'item_2_copy',
            'item_3_copy',
            'item_4_copy',
            'item_5_copy',
            'item_6_copy' );

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

        // id to let us tie form submission to previously-uploaded files
        $async_upload_token = bin2hex(openssl_random_pseudo_bytes(6));
        $this['async_upload_token'] = new CharField( array('required'=>TRUE, 'initial'=>$async_upload_token, 'widget'=>'HiddenInput' ) ); 

        $this['journo_first_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"First name"));
        $this['journo_last_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"Last name"));
        $this['journo_address'] = new CharField(array('required'=>TRUE, 'label'=>"Correspondence address", 'widget'=>'TextArea' ));
        $this['journo_email'] = new EmailField(array('required'=>TRUE, 'label'=>"Email", 'widget'=>newemailwidget() ));
        $this['journo_twitter'] = new CharField(array('required'=>FALSE, 'label'=>"Twitter"));
        $this['journo_phone'] = new CharField(array('required'=>TRUE, 'label'=>"Telephone number"));
        $this['link_with_uk_or_ireland'] = new ChoiceField(array(
            'label'=>'Relationship of entry to UK or Ireland',
            'choices'=>$relationship_choices,
            'help_text'=>'See point 10 of the <a href="http://theorwellprize.co.uk/the-orwell-prize/how-to-enter/rules/">rules</a> for details.'));
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
            $this["item_{$n}_pubdate"] = new CharField(array('required'=>$req,'label'=>'Date of first publication', 'help_text'=>'dd/mm/yyyy', 'widget'=>newdatewidget(), 'validators'=>array(array(new MyDateValidator($g_entry_year),'execute')) ));
            $this["item_{$n}_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
            $this["item_{$n}_copy"] = new FileField(array('required'=>$req,'label'=>'Copy', 'help_text'=>"PDF only, please"));
        }

        $this['publication_contact'] = new CharField(array('required'=>TRUE, 'label'=>'Contact name'));
        $this['publication_email'] = new EmailField(array('required'=>TRUE, 'label'=>'Email address', 'widget'=>newemailwidget()));
        $this['publication_phone'] = new CharField(array('required'=>TRUE, 'label'=>'Telephone number'));
        $this['publication_address'] = new CharField(array('required'=>TRUE, 'widget'=>'TextArea', 'label'=>'Address' ));

        $this['declaration'] = new BooleanField(array('required'=>TRUE, 'label'=>"I agree"));
        $this['workshop_consent'] = new BooleanField(array('required'=>FALSE, 'label'=>"I agree" ));

        if(array_key_exists('async_upload_token', $_POST)) {
            // check for already-uploaded files on the file fields:
            $tok = $_POST['async_upload_token'];

            foreach($this->filefields as $fld) {
                if ($this->handler->find_uploaded_file($tok,$fld) !== NULL ) {
                    // got one! set an attr on the field so the javascript knows...
                    $this->fields[$fld]->widget->attrs['data-uploaded'] = "File already uploaded...";
                }
            } 
        }
    }


    //
    function clean() {
        // remove validation errors on any files that were previously uploaded
        $tok = $this->cleaned_data['async_upload_token'];

        foreach( $this->filefields as $fld) {
            $uploaded = $this->handler->find_uploaded_file($tok,$fld);
            if($uploaded !== NULL ) {
                //error_log("found uploaded file for $fld");
                unset( $this->_errors[$fld] );
                // set the data to the name of the previously-uploaded file
                // (instead of an array from $_FILES)
                $this->cleaned_data[$fld] = $uploaded;
            }
        }

        // make sure that link is filled in if dropdown is set to "other"
        $link = $this->cleaned_data['link_with_uk_or_ireland'];
        $link_other = $this->cleaned_data['link_other'];
        if($link=='other' && !$link_other) {
            $this->_errors["link_with_uk_or_ireland"] = array("Please specify the link to the UK or Ireland");
            unset($this->cleaned_data['link_with_uk_or_ireland']);
            unset($this->cleaned_data['link_other']);
        }

        return $this->cleaned_data;
    }
}



class JournalismEntryHandler extends BaseEntryHandler {
    function __construct() {
        parent::__construct('journalism','JournalismEntryForm');
    }


    function cook_data(&$data) {
        // cook the data to handle any uploaded cover images
        $this->cook_file($data, "journo_photo", "{$data['journo_first_name']}_{$data['journo_last_name']}");
        for($n=1; $n<=6; ++$n) {
            $this->cook_file($data, "item_{$n}_copy", "{$data['journo_first_name']}_{$data['journo_last_name']}_item_{$n}");
        }

    }


    function do_alert($data) {
        // send out an email alert with the csv file and uploaded files
        $attachments = array();
        if($data['journo_photo']) {
            $attachments[] = "{$this->entry_dir}/{$data['journo_photo']}";
        };
        for($n=1; $n<=6; ++$n) {
            if($data["item_{$n}_copy"]) {
                $attachments[] = "{$this->entry_dir}/{$data["item_{$n}_copy"]}";
            }
        }

        $subject = "Orwell {$this->shortname} entry: '{$data['journo_first_name']} {$data['journo_last_name']}'";
        $this->email($subject,$data,$attachments);
    }
}

try {
    $v = new JournalismEntryHandler();
    $v->handle();
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
