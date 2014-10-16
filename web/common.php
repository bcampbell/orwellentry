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


function fld_label_right($f, $extra_css="") {
?>
<div class="fld <?=$extra_css?> <?=$f->css_classes() ?>">
<?= $f ?>
<?= $f->label_tag() ?>
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
        global $g_output_dir;
        $this->shortname = $shortname;
        $this->config = $g_config[$this->shortname];
        $this->tmp_dir = "{$g_output_dir}/tmp/{$shortname}";
        $this->entry_dir = "{$g_output_dir}/entries/{$shortname}";
        $this->entries_file = "{$this->entry_dir}/{$shortname}_entries.csv";

        $this->formtype = $formtype;
        $this->sanity_check();
        // fields to remove from output csv
        $this->suppressed_fields = array("async_upload_token");
    }

    function handle()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(array_key_exists('async_upload_field', $_POST) ) {
                $this->handle_async_upload();
                return;
            }
            $f= new $this->formtype($_POST, $_FILES, $this);
            if($f->is_valid()) {
                $this->process($f);
                // redirect to prevent doubleposting
                header("HTTP/1.1 303 See Other");
                header("Location: /thanks?entered={$this->shortname}");
                return;
            }
        } else {
            // provide an unbound form
            $f = new $this->formtype(null,null,$this);
        }

        $this->render_page($f);
    }



    function find_uploaded_file($tok,$field_name) {
        $got = glob("{$this->tmp_dir}/{$tok}_{$field_name}.*");
        if(count($got)==0) {
            return NULL;
        }
        return $got[0];
    }

    function async_filename($tok,$field_name)
    {
        return "{$this->tmp_dir}/{$tok}_{$field_name}";
    }

    function handle_async_upload()
    {
        $field_name = $_POST['async_upload_field'];
        $file = $_FILES['async_upload_file'];
        $tok = $_POST['async_upload_token'];

        // TODO: apply file validation rules here?


        // already got a file?
        $old = $this->find_uploaded_file($tok,$field_name);
        if($old!==NULL) {
            // delete old file
            if(!unlink($old)) {
                error_log("Couldn't delete {$old}");
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = "{$this->tmp_dir}/{$tok}_{$field_name}.{$ext}";

        // stash file for later...
        if(move_uploaded_file($file['tmp_name'], $name) !== TRUE) {
            throw new Exception("Internal error - couldn't save {$field_name}");
        }
    }


    // the main template for filing an entry
    function render_page( $f ) {
        include "templates/{$this->shortname}.php";
    }

    function sanity_check() {
        if(!file_exists($this->entry_dir)) {
            if (!mkdir($this->entry_dir, 0777, true)) {
                throw new Exception("Internal error - couldn't create entry dir ({$this->entry_dir})");
            }
        }
        if(!is_writable($this->entry_dir)) {
            throw new Exception("Internal error - Entry dir isn't writable ({$this->entry_dir})");
        }
        if(!file_exists($this->tmp_dir)) {
            if (!mkdir($this->tmp_dir, 0777, true)) {
                throw new Exception("Internal error - couldn't create tmp dir ({$this->tmp_dir})");
            }
        }
        if(!is_writable($this->tmp_dir)) {
            throw new Exception("Internal error - Tmp dir isn't writable ({$this->tmp_dir})");
        }
    }

    function cook_file(&$data, $filefield, $namebase) {
        if(array_key_exists($filefield,$data) && $data[$filefield]) {
            // use namebase as the basis for filename
            $ext = pathinfo($data[$filefield]['name'], PATHINFO_EXTENSION);
            $cooked_file = strtolower(preg_replace("/[^-_0-9a-zA-Z\.]/","", $namebase));
            if(!$cooked_file) {
                throw new Exception("Internal error - couldn't save {$filefield} because of bad name ({$cooked_file})");
            }

            $foo = $cooked_file.".".$ext;
            // rename to avoid overwriting
            $n = 1;
            while(file_exists("{$this->entry_dir}/{$foo}") ) {
                $foo = "{$cooked_file}-{$n}.{$ext}";
                $n++;
            }
            $cooked_file = $foo;

            if(move_uploaded_file($data[$filefield]['tmp_name'], "{$this->entry_dir}/{$cooked_file}") !== TRUE) {
                throw new Exception("Internal error - couldn't save {$filefield} ({$this->entry_dir}/{$cooked_file})");
            }

            $data[$filefield] = $cooked_file;
        } else {

            error_log("missing $filefield");

            // maybe file was uploaded previously?
            $tok = $data['async_upload_token'];
            $uploaded_name = $this->find_uploaded_file($tok,$filefield);
            if ($uploaded_name !== NULL ) {
                error_log("found it! ({$uploaded_name})");
                $ext = pathinfo($uploaded_name, PATHINFO_EXTENSION);
                $cooked_file = strtolower(preg_replace("/[^-_0-9a-zA-Z\.]/","", $namebase));
                if(!$cooked_file) {
                    throw new Exception("Internal error - couldn't save {$filefield} because of bad name ({$cooked_file})");
                }

                $foo = $cooked_file.".".$ext;
                // rename to avoid overwriting
                $n = 1;
                while(file_exists("{$this->entry_dir}/{$foo}") ) {
                    $foo = "{$cooked_file}-{$n}.{$ext}";
                    $n++;
                }
                $cooked_file = $foo;

                // move
                if( !rename($uploaded_name, "{$this->entry_dir}/{$cooked_file}") ) {
                    throw new Exception("Internal error - couldn't rename {$uploaded_name} to {$filefield} {$this->entry_dir}/{$cooked_file}");
                }

                $data[$filefield] = $cooked_file;
            }
        }
    }


    // a valid form has been submitted - handle it!
    function process($f) {
        $this->sanity_check();

        $data = $f->cleaned_data;
        $this->cook_data($data);

        // get field list from form, as data might be missing some values...
        $fieldnames = array_diff(array_keys($f->fields), $this->suppressed_fields);

        error_log(join(",",$fieldnames));


        // add a new entry to the csv file

        // if starting new file, output field names in first row
        if(!file_exists($this->entries_file)) {
            if(file_put_contents($this->entries_file, join(',',$fieldnames) . "\n") === FALSE) {
                throw new Exception("Internal error - couldn't create entry list ({$this->entries_file})");
            }
        }

        // format a line of data
        // (do this to ensure order is consistant)
        $datline = array();
        foreach( $fieldnames as $fld) {
            $datline[] = $data[$fld];
        }


        $obuf = fopen('php://output', 'w');
        ob_start();
        fputcsv($obuf, $datline);
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
