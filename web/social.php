<?php

require_once "config.php";
require_once "common.php";

require_once "drongo-forms/forms.php";


class SocialEntryForm extends Form {
    function __construct($data=null,$files=null) {
        $opts = array(
            'label_suffix'=>'', // suppress a trailing ':' after labels
            /* 'prefix'=>'test', */
        );

        parent::__construct($data,$files,$opts);
        // these should be opts?
        $this->error_css_class = 'fld-error';
        $this->required_css_class = 'fld-required';


        $this['title'] = new CharField( array( 'required'=>TRUE, 'label'=>"Title of submission"));

        $this['journo_first_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"First name"));
        $this['journo_last_name'] = new CharField( array( 'required'=>TRUE, 'label'=>"Last name"));
        $this['journo_address'] = new CharField(array('required'=>TRUE, 'label'=>"Correspondence address", 'widget'=>'TextArea' ));
        $this['journo_email'] = new EmailField(array('required'=>TRUE, 'label'=>"Email" ));
        $this['journo_twitter'] = new CharField(array('required'=>FALSE, 'label'=>"Twitter"));
        $this['journo_phone'] = new CharField(array('required'=>TRUE, 'label'=>"Telephone number"));

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
            $this["video_{$n}_password"] = new CharField(array('required'=>$req,'label'=>'Password (if required)', help_text=>"Please don't use an important password! It's just to prevent casual viewing by others."));
        }

        for( $n=1; $n<=3; ++$n) {
            $req = FALSE;   //($n<=4)?TRUE:FALSE;
            $this["audio_{$n}_title"] = new CharField(array('required'=>$req,'label'=>'Title'));
            $this["audio_{$n}_provider"] = new CharField(array('required'=>$req,'label'=>'Channel/Content provider'));
            $this["audio_{$n}_pubdate"] = new CharField(array('required'=>$req,'label'=>'Date of first broadcast/release', 'help_text'=>'dd/mm/yyyy'));
            $this["audio_{$n}_url"] = new CharField(array('required'=>FALSE,'label'=>'URL'));
            $this["audio_{$n}_password"] = new CharField(array('required'=>$req,'label'=>'Password (if required)', help_text=>"Please don't use an important password! It's just to prevent casual viewing by others."));
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



class SocialEntryHandler extends BaseEntryHandler {
    function __construct() {
        parent::__construct('social','SocialEntryForm');
    }


    function cook_data(&$data) {
        // cook the data to handle any uploaded cover images
        /*
        $this->cook_file($data, "journo_photo", "{$data['journo_first_name']}_{$data['journo_last_name']}");
        for($n=1; $n<=6; ++$n) {
            $this->cook_file($data, "item_{$n}_copy", "{$data['journo_first_name']}_{$data['journo_last_name']}_item_{$n}");
        }
        */
    }


    function do_alert($data) {
        /*
        // send out an email alert with the csv file and uploaded files
        $attachments = array($this->entries_file);
        if($data['journo_photo']) {
            $attachments[] = "{$this->upload_dir}/{$data['journo_photo']}";
        };
        for($n=1; $n<=6; ++$n) {
            if($data["item_{$n}_copy"]) {
                $attachments[] = "{$this->upload_dir}/{$data["item_{$n}_copy"]}";
            }
        }

        $subject = "Orwell {$this->shortname} entry: '{$data['journo_first_name']} {$data['journo_last_name']}'";
        $this->email($subject,$data,$attachments);
        */
    }
}

try {
    $v = new SocialEntryHandler();
    $v->handle();
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
