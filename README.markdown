# OrwellEntry

Entry form micro site for The Orwell Prize.

## Installation

### Requirements

The site relies on three third-party libraries:

- [ti.php](http://arshaw.com/phpti/) - a nice simple template system
- [drongo-forms](https://github.com/bcampbell/drongo-forms)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer)

`config.php` adds an `inc/` directory to the include path by default. I
just copy or symlink in the appropriate libraries in there.

So you'd have a directory structure like this:

    orwellentry/
      README.markdown
      LICENCE
      web/
        templates/
        fonts/
        config.php
        ...
      inc/
        drongo-forms/
        ti.php
        PHPMailer/

### Configuring

You'll need to set up a `config.php`. Look at the example one for a starting
point.

`$g_output_dir` is the location where the submitted entries are placed.
Each category (book/journalism/social) has:

1. a subdirectory to hold uploaded files
2. `.csv` file containing all the entry data, one record per entry.

Upon completion of each entry, an email alert will be sent out,
containing the entry data (its row from the `.csv` file) with any uploaded
files as attachements. If the uploaded files are too big, they'll be left
off the email, so it's important to note that the emails are _not_ a complete
record of entries.

The `$g_output_dir` should be considered the definitive set of entries.


### Web server setup

It's assumed that the web server is set up to handle URLs _without_ the
`.php` suffix.


