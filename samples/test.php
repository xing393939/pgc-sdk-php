<?php
$url = 'http://localhost/samples/test.php';

// form field separator
$delimiter = '-------------' . uniqid();
// file upload fields: name => array(type=>'mime/type',content=>'raw data')
$fileFields = array(
    'file1' => array(
        'type' => 'text/plain',
        'content' => '...your raw file content goes here...'
    ), /* ... */
);
// all other fields (not file upload): name => value
$postFields = array(
    'otherformfield'   => 'content of otherformfield is this text',
    /* ... */
);

$data = '';

// populate normal fields first (simpler)
foreach ($postFields as $name => $content) {
   $data .= "--" . $delimiter . "\r\n";
    $data .= 'Content-Disposition: form-data; name="' . $name . '"';
    $data .= $content . "\r\n";
    $data .= "\r\n\r\n";
}
// populate file fields
foreach ($fileFields as $name => $file) {
    $data .= "--" . $delimiter . "\r\n";
    // "filename" attribute is not essential; server-side scripts may use it
    $data .= 'Content-Disposition: form-data; name="' . $name . '";' .
             ' filename="' . $name . '"' . "\r\n";
    // this is, again, informative only; good practice to include though
    $data .= 'Content-Type: ' . $file['type'] . "\r\n";
    // this endline must be here to indicate end of headers
    $data .= "\r\n";
    // the file itself (note: there's no encoding of any kind)
    $data .= $file['content'] . "\r\n";
}
// last delimiter
$data .= "--" . $delimiter . "--\r\n";

$handle = curl_init($url);
curl_setopt($handle, CURLOPT_POST, true);
curl_setopt($handle, CURLOPT_HTTPHEADER , array(
    'Content-Type: multipart/form-data; boundary=' . $delimiter,
    'Content-Length: ' . strlen($data)));
curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
$r = curl_exec($handle);

echo $r;