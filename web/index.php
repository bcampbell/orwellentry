<?php

require_once "drongo-forms/forms.php";


# TODO: prevent files from overwriting each other

$G_UPLOAD_DIR = '/tmp/fook';
$G_ENTRIES_FILE = "${G_UPLOAD_DIR}/entries.csv";

class BookEntryForm extends Form {
    function __construct($data=null,$files=null, $opts=null) {
        parent::__construct($data,$files,$opts);
        $disclaimer = "I declare that this work, submitted for consideration for the Orwell Prize for Books 2013, is wholly or substantially my own, and does not contain any plagiarised or unacknowledged material.";

        $this->fields['book_title'] = new CharField(
            array( 'required'=>TRUE, 'help_text'=>"e.g. Zapp Brannigan's Big Book of War"));
        $this->fields['author_first_name'] = new CharField(array( 'required'=>TRUE ));
        $this->fields['author_last_name'] = new CharField(array( 'required'=>TRUE ));
        $this->fields['book_cover'] = new FileField(array( 'required'=>FALSE, 'help_text'=>"Please keep it below 2MB<br/>\nAccepted formats: png, jpeg, gif, tiff" ));
        // TODO: publication_month (use regex?)
        $this->fields['link_with_uk_or_ireland'] = new CharField(array(
            'label'=>'Link with UK or Ireland',
            'help_text'=>'Tell us how you are linked to UK or Ireland'
        ));
        $this->fields['author_email'] = new EmailField(array('required'=>FALSE, 'help_text'=>'Email address of the author'));
        $this->fields['author_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this->fields['author_phone_number'] = new CharField(array('required'=>FALSE));

        $this->fields['publisher_email'] = new EmailField(array('required'=>FALSE));
        $this->fields['publisher_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this->fields['publisher_phone'] = new CharField(array('required'=>FALSE));
        $this->fields['agent_email'] = new EmailField(array('required'=>FALSE));
        $this->fields['agent_address'] = new CharField(array('required'=>FALSE, 'widget'=>'TextArea' ));
        $this->fields['agent_phone'] = new CharField(array('required'=>FALSE));
        $this->fields['declaration'] = new BooleanField(array('help_text'=>$disclaimer));
    }
}



function view()
{
    $form_opts = array(
        'label_suffix'=>'',
        /* 'prefix'=>'test', */
    );

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $f= new BookEntryForm($_POST,$_FILES,$form_opts);
        if($f->is_valid()) {
            process($f);
            // process form here...
            // then, redirect to prevent doubleposting
            //header("HTTP/1.1 303 See Other");
            //header("Location: /thanks");
            template_thanks($f);
            return;
        }
    } else {
        // provide an unbound form
        $f = new BookEntryForm(null,null,$form_opts);
    }

    template_enter($f);
}



function template_header() {
?><html>
<head>
<link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
<h1>The Orwell prize</h1>
<hr/>
<?php
}

function template_footer() {
?>
</body>
</html>
<?php
}



// the main template for filing an entry
function template_enter( $f ) {
    // TODO: add a MAX_FILE_SIZE hidden element to enable early-out on
    // oversize files
    template_header();
?>

<h2>Book Prize 2013 Entry</h2>

<form enctype="multipart/form-data" action="" method="POST">
<table>
<?= $f->as_table(); ?>
</table>

<p>
<em>
</em>
</p>

<input type="submit" />
</form>
<br/>

<?php
    template_footer();
}


// TODO: should be in separate file
function template_thanks( $f ) {
    global $G_UPLOAD_DIR, $G_ENTRIES_FILE;
    template_header();
?>
<p>Thanks for your entry</p>
<br/>
<br/>
<br/>
<hr/>
<br/>
<br/>
<p>(here's what the entries file looks like now:</p>
<pre>
<?= file_get_contents( $G_ENTRIES_FILE); ?>

</pre>
<?php
    template_footer();
}



// display an error
function template_pearshaped( $err ) {
    template_header();
?>
<p>Uhoh... Something went wrong:</p>
<p><em><?=$err->getmessage(); ?></em></p>
<?php
    template_footer();
}




// a valid form has been submitted - handle it!
function process($f) {
    global $G_UPLOAD_DIR, $G_ENTRIES_FILE;

    if(!is_writable($G_UPLOAD_DIR)) {
        throw new Exception("Internal error - Output dir doesn't exist, or isn't writable");
    }

    $data = $f->cleaned_data;

    if( $data['book_cover'] ) {
        $ext = pathinfo($data['book_cover']['name'], PATHINFO_EXTENSION);
        $cover_file = preg_replace("/[^a-zA-Z\.]/","", $data['book_title']);
        if(!$cover_file) {
            throw new Exception("Internal error - couldn't save cover image because of bad name");
        }
        $cover_file .= ".".$ext;

        if(move_uploaded_file($data['book_cover']['tmp_name'], "$G_UPLOAD_DIR/$cover_file") !== TRUE) {
            throw new Exception("Internal error - couldn't save cover image $G_UPLOAD_DIR/$cover_file");
        }

        $data['book_cover'] = $cover_file;
    }


    // add a new entry to the csv file

    // if starting new file, output field names in first row
    if(!file_exists($G_ENTRIES_FILE)) {
        $fieldnames = array_keys($data);
        if(file_put_contents($G_ENTRIES_FILE, join(',',$fieldnames) . "\n") === FALSE) {
            throw new Exception("Internal error - couldn't create entry list ($G_ENTRIES_FILE)");
        }
    }

    $obuf = fopen('php://output', 'w');
    ob_start();
    fputcsv($obuf, $data);
    fclose($obuf);
    $line = ob_get_clean();

    if( file_put_contents( $G_ENTRIES_FILE, $line, FILE_APPEND|LOCK_EX) === FALSE ) {
        throw new Exception("Internal error - couldn't record details");
    }
}

try {
    view();
} catch(Exception $e) {
    template_pearshaped($e);
}
?>
