#!/usr/local/bin/php
<?php

/**
 * This is the SiteID as assinged by the database.
 */
define('SITE_ID', 0);
/**
 * This is the P25ID as assigned by the database.
 */
define('P25_ID', 0);

[$me, $wav, $json, $m4a] = $argv;
[$path, $audio, $system, $year, $month, $day, $file] = explode('/', $wav);
[$tgid, $time, $hz, $call, $number, $ext] = preg_split('/[-_.]/', $file);

define('MULTIPART_BOUNDARY', '--------------------------'.microtime());
$header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;

/**
 * $_FILES
 */
// WAV File Upload
$content =  "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"wav\"; filename=\"".basename($wav)."\"\r\n".
            "Content-Type: audio/wav\r\n\r\n".
            file_get_contents($wav)."\r\n";
// M4A File Upload
$content .=  "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"m4a\"; filename=\"".basename($m4a)."\"\r\n".
            "Content-Type: audio/m4a\r\n\r\n".
            file_get_contents($m4a)."\r\n";
// JSON File Upload
$content .=  "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"json\"; filename=\"".basename($json)."\"\r\n".
            "Content-Type: application/json\r\n\r\n".
            file_get_contents($json)."\r\n";

/**
 * $_POST
 */
// SITE ID
$content .= "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"siteId\"\r\n\r\n".
            SITE_ID."\r\n";
// P25 ID
$content .= "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"p25Id\"\r\n\r\n".
            P25_ID."\r\n";
// Year
$content .= "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"year\"\r\n\r\n".
            "{$year}\r\n";
// Month
$content .= "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"month\"\r\n\r\n".
            "{$month}\r\n";
// Day
$content .= "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"day\"\r\n\r\n".
            "{$day}\r\n";

// signal end of request (note the trailing "--")
$content .= "--".MULTIPART_BOUNDARY."--\r\n";


$context = stream_context_create([
    'http' => [
          'method' => 'POST',
          'header' => $header,
          'content' => $content,
    ]
]);

echo file_get_contents('<PointToWhereYourHTTPServerIs>/upload.php', false, $context);

