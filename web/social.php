<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";


class SocialEntryForm extends Form {
    function __construct($data=null,$files=null,$handler=null) {
        $this->handler = $handler;
        $opts = array(
            'label_suffix'=>'', // suppress a trailing ':' after labels
            /* 'prefix'=>'test', */
        );

        $this->filefields = array('journo_photo',
            'writing_1_copy',
            'writing_2_copy',
            'writing_3_copy',
            'social_copy',
            'photo_1_photo',
            'photo_2_photo',
            'photo_3_photo');

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

        $this['title'] = new CharField( array( 'required'=>TRUE, 'label'=>"Title of submission"));

        $this['journo_first_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"First name"));
        $this['journo_last_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"Last name"));
        $this['journo_address'] = new CharField(array('required'=>TRUE, 'label'=>"Correspondence address", 'widget'=>'TextArea' ));
        $this['journo_email'] = new EmailField(array('required'=>TRUE, 'label'=>"Email" ));
        $this['journo_twitter'] = new CharField(array('required'=>FALSE, 'label'=>"Twitter"));
        $this['journo_phone'] = new CharField(array('required'=>TRUE, 'label'=>"Telephone number"));

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
        $this['further_info'] = new CharField(array('required'=>FALSE, 'label'=>"Further information", 'widget'=>'TextArea' ));

        for( $n=1; $n<=3; ++$n) {
            $req = FALSE;   //($n<=4)?TRUE:FALSE;
            $this["writing_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["writing_{$n}_publication"] = new CharField(array('required'=>$req,'label'=>'Publication'));
            $this["writing_{$n}_pubdate"] = new CharField(array('required'=>$req,'label'=>'Date of first publication', 'help_text'=>'dd/mm/yyyy'));
            $this["writing_{$n}_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
            $this["writing_{$n}_copy"] = new FileField(array('required'=>$req,'label'=>'Copy', 'help_text'=>"PDF only, please"));
        }

        for( $n=1; $n<=3; ++$n) {
            $req = FALSE;   //($n<=4)?TRUE:FALSE;
            $this["video_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["video_{$n}_provider"] = new CharField(array('required'=>$req,'label'=>'Channel/Content provider'));
            $this["video_{$n}_pubdate"] = new CharField(array('required'=>$req,'label'=>'Date of first publication', 'help_text'=>'dd/mm/yyyy'));
            $this["video_{$n}_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
            $this["video_{$n}_password"] = new CharField(array('required'=>$req,'label'=>'Password (if required)', 'help_text'=>"Please don't use an important password! It's just to prevent casual viewing by others."));
        }

        for( $n=1; $n<=3; ++$n) {
            $req = FALSE;   //($n<=4)?TRUE:FALSE;
            $this["audio_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["audio_{$n}_provider"] = new CharField(array('required'=>$req,'label'=>'Channel/Content provider'));
            $this["audio_{$n}_pubdate"] = new CharField(array('required'=>$req,'label'=>'Date of first broadcast/release', 'help_text'=>'dd/mm/yyyy'));
            $this["audio_{$n}_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
            $this["audio_{$n}_password"] = new CharField(array('required'=>$req,'label'=>'Password (if required)', 'help_text'=>"Please don't use an important password! It's just to prevent casual viewing by others."));
        }

        $this["social_username"] = new CharField(array('required'=>FALSE,'label'=>'Username'));
        $this["social_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
        $this["social_copy"] = new FileField(array('required'=>FALSE,'label'=>'Copy', 'help_text'=>"PDF only, please"));

        for( $n=1; $n<=3; ++$n) {
            $req = FALSE;   //($n<=4)?TRUE:FALSE;
            $this["photo_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["photo_{$n}_date"] = new CharField(array('required'=>$req,'label'=>'Date taken', 'help_text'=>'dd/mm/yyyy'));
            $this["photo_{$n}_publication"] = new CharField(array('required'=>$req,'label'=>'Publication (if applicable)'));
            $this["photo_{$n}_url"] = new CharField(array('required'=>$req,'label'=>'URL'));
            $this["photo_{$n}_photo"] = new FileField(array('required'=>$req,'label'=>'Upload photo', 'help_text'=>"JPEG only please, max size 3 Meg"));
        }

        $this['declaration'] = new BooleanField(array('label'=>"I agree"));


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

        // make sure that link is filled in if dropdown is set to "other"
        $link = $this->cleaned_data['link_with_uk_or_ireland'];
        $link_other = $this->cleaned_data['link_other'];
        if($link=='other' && !$link_other) {
            $this->_errors["link_with_uk_or_ireland"] = array("Please specify the link to the UK or Ireland");
            unset($this->cleaned_data['link_with_uk_or_ireland']);
            unset($this->cleaned_data['link_other']);
        }


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
        // check sections are complete or empty
        // and make sure at least two sections filled in

        $section_cnt = $this->chk_writing() +
            $this->chk_video() +
            $this->chk_audio() +
            $this->chk_social() +
            $this->chk_photo();

        if($section_cnt < 2 ) {
            $this->_errors['__all__'] = array("Please submit at least two different kinds of work (got {$section_cnt})");
        }

        return $this->cleaned_data;
    }


    function chk_writing() {
        $cnt = 0;
        $fields = array('title','publication','pubdate','url','copy');
        $fields_req = array('title','publication','pubdate','copy');
        for( $n=1; $n<=3; ++$n) {
            if($this->chk_block("writing_{$n}_",$fields,$fields_req)) {
                $cnt++;
            }
        }
        return ($cnt>0) ? 1:0;
    }

    function chk_video() {
        $cnt = 0;
        $fields = array('title','provider','pubdate','url','password');
        $fields_req = array('title','pubdate','url');
        for( $n=1; $n<=3; ++$n) {
            if($this->chk_block("video_{$n}_",$fields,$fields_req)) {
                $cnt++;
            }
        }
        return ($cnt>0) ? 1:0;
    }

    function chk_audio() {
        $cnt = 0;
        $fields = array('title','provider','pubdate','url','password');
        $fields_req = array('title','pubdate','url');
        for( $n=1; $n<=3; ++$n) {
            if($this->chk_block("audio_{$n}_",$fields,$fields_req)) {
                $cnt++;
            }
        }
        return ($cnt>0) ? 1:0;
    }

    function chk_photo() {
        $cnt = 0;
        $fields = array('title','date','publication','url','photo');
        $fields_req = array('title','date','photo');
        for( $n=1; $n<=3; ++$n) {
            if($this->chk_block("photo_{$n}_",$fields,$fields_req)) {
                $cnt++;
            }
        }
        return ($cnt>0) ? 1:0;
    }

    function chk_social() {
        $fields = array('username','url','copy');
        $fields_req = array('username','url');

        $cnt=0;
        if($this->chk_block("social_",$fields,$fields_req)) {
            $cnt++;
        }
        return $cnt;
    }

    function chk_block($prefix, $fields,$fields_required) {

        $ok = TRUE;
        $used = FALSE;
        foreach ($fields as $postfix) {
            $fld = "{$prefix}{$postfix}";
            $val = array_key_exists($fld,$this->cleaned_data) ? $this->cleaned_data[$fld] : "";
            if($val != "" && !is_null($val)) {
                $used = TRUE;
            }
        }
        if($used) {
            foreach ($fields_required as $postfix) {
                $fld = "{$prefix}{$postfix}";
                $val = array_key_exists($fld,$this->cleaned_data) ? $this->cleaned_data[$fld] : "";
                //error_log("CHK $fld: $val");
                if($val == "" || is_null($val)) {
                    $this->_errors[$fld] = array("This field is required");
                    unset($this->cleaned_data[$fld]);
                    $ok = FALSE;
                }
            }
        }
        return ($used && $ok); 
    }

}



class SocialEntryHandler extends BaseEntryHandler {
    function __construct() {
        parent::__construct('social','SocialEntryForm');
    }


    function cook_data(&$data) {
        // cook the data to handle any uploaded files
        $this->cook_file($data, "journo_photo", "{$data['journo_first_name']}_{$data['journo_last_name']}");
        for($n=1; $n<=3; ++$n) {
            $this->cook_file($data, "writing_{$n}_copy", "{$data['journo_first_name']}_{$data['journo_last_name']}_writing_{$n}");
        }
        for($n=1; $n<=3; ++$n) {
            $this->cook_file($data, "photo_{$n}_photo", "{$data['journo_first_name']}_{$data['journo_last_name']}_photo_{$n}");
        }
        $this->cook_file($data, "social_copy", "{$data['journo_first_name']}_{$data['journo_last_name']}_social");
    }


    function do_alert($data) {
        // send out an email alert with the csv file and uploaded files
        $attachments = array($this->entries_file);

        /*
        if($data['journo_photo']) {
            $attachments[] = "{$this->entry_dir}/{$data['journo_photo']}";
        };
        for($n=1; $n<=6; ++$n) {
            if($data["item_{$n}_copy"]) {
                $attachments[] = "{$this->entry_dir}/{$data["item_{$n}_copy"]}";
            }
        }
        */

        $subject = "Orwell {$this->shortname} entry: '{$data['journo_first_name']} {$data['journo_last_name']}'";
        $this->email($subject,$data,$attachments);
    }
}

try {
    $v = new SocialEntryHandler();
    $v->handle();
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
