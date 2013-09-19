<?php
require_once "PHPMailer/class.phpmailer.php";

// little helper template to output a single field
function fld($f, $extra_css="") {
?>
<div class="fld <?=$extra_css?> <?=$f->css_classes() ?>">
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


function fld_select_with_other($f, $other) {
?>
<div class="fld <?=$f->css_classes() ?>">
<?=$f->label_tag() ?>
<?=$f ?>
<?=$other ?>
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



class BaseEntryHandler {
    function __construct($shortname, $formtype) {
        global $g_config;
        $this->shortname = $shortname;
        $this->config = $g_config[$this->shortname];
        $this->upload_dir = $this->config['upload_dir'];
        $this->entries_file = "{$this->upload_dir}/{$this->shortname}_entries.csv";

        $this->formtype = $formtype;

        $this->sanity_check();
    }

    function handle()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $f= new $this->formtype($_POST,$_FILES);
            if($f->is_valid()) {
                $this->process($f);
                // redirect to prevent doubleposting
                header("HTTP/1.1 303 See Other");
                header("Location: /thanks?entered={$this->shortname}");
                return;
            }
        } else {
            // provide an unbound form
            $f = new $this->formtype(null,null);
        }

        $this->render_page($f);
    }


    // the main template for filing an entry
    function render_page( $f ) {
        include "templates/{$this->shortname}.php";
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

            $foo = $cooked_file.".".$ext;
            // rename to avoid overwriting
            $n = 1;
            while(file_exists("{$this->upload_dir}/{$foo}") ) {
                $foo = "{$cooked_file}-{$n}.{$ext}";
                $n++;
            }
            $cooked_file = $foo;

            if(move_uploaded_file($data[$filefield]['tmp_name'], "{$this->upload_dir}/{$cooked_file}") !== TRUE) {
                throw new Exception("Internal error - couldn't save {$filefield} ({$this->upload_dir}/{$cooked_file})");
            }

            $data[$filefield] = $cooked_file;
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

        if( $this->config['alert'] ) {
            $this->do_alert($data);
        }
    }




    function email($subject, $entry_data, $filenames=array()) {

        $from = $this->config['alert']['from'];
        $recipients = $this->config['alert']['recipients'];


        $mail = new PHPMailer();

        // to run local smtp server for testing email sending:
        // $ sudo python -m smtpd -n -c DebuggingServer localhost:25
        //$mail->isSMTP();
        //$mail->Host       = "localhost"; // SMTP server
        //$mail->Port       = 25;

        $mail->Subject = $subject;
        $mail->setFrom($from);
        foreach($recipients as $to) {
            $mail->addAddress($to);
        }

//        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        foreach( $filenames as $file) {
            $mail->addAttachment($file);
        }

        $msg = "Here is the submitted data:\n\n";
        foreach($entry_data as $key=>$value) {
            $value = preg_replace('/[\n]/',"\n                          ", $value);
            $msg .= sprintf("%24s: %s\n",$key,$value);
        }

        $mail->Body = $msg;

        if(!$mail->send()) {
	    	// just quietly log the failure - don't bother the site user with this.
            error_log ("Failed to send alert email: " . $mail->ErrorInfo);
        }
    }


    // OVERRIDE to cook the data to handle any uploaded files
    function cook_data(&$data) {
    }

    // OVERRIDE to send out email alert
    function do_alert($data) {
    }

}



?>
