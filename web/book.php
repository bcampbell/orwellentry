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
        $this['author_email'] = new EmailField(array('required'=>FALSE, 'label'=>"Email" ));
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

        $this['primary_contact'] = new ChoiceField( array(
            'required'=>TRUE,
            'choices'=>array('author'=>"Author", 'publisher'=>"Publisher", 'agent'=>"Agent") ));
        $this['declaration'] = new BooleanField(array('label'=>"I agree"));

    }

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



class BookEntryHandler extends BaseEntryHandler {
    function __construct() {
        parent::__construct('book','BookEntryForm');
    }


    // cook the data to handle any uploaded cover images
    function cook_data(&$data) {
        $this->cook_file($data, "book_cover", $data['book_title']);
    }

    function do_alert($data) {
    // send out an email alert with the csv file and uploaded files
        $attachments = array($this->entries_file);
        if($data['book_cover']) {
            $attachments[] = "{$this->upload_dir}/{$data['book_cover']}";
        };

        $subject = "Orwell {$this->shortname} entry: '${data['book_title']}'";
        $this->email($subject,$data,$attachments);
    }
}


try {
    $v = new BookEntryHandler();
    $v->handle();
} catch(Exception $err) {
    include "templates/pearshaped.php";
}

?>
