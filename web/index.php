<?php
    // redirect to prevent doubleposting
    header("HTTP/1.1 303 See Other");
    header("Location: /book");
    return;
?>
