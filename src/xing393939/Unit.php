<?php namespace xing393939;

class Unit
{
    public static function send_json_post($url, $arr, $header = array())
    {
        $json = json_encode($arr);
        $header[] = 'Content-Type: application/json';
        $header[] = 'Content-Length: ' . strlen($json);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 75);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function send_get($url, $header = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 75);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function send_put_file($url, $name, $content, $header = array())
    {
        // form field separator
        $delimiter = '-------------' . uniqid();
        // file upload fields: name => array(type=>'mime/type',content=>'raw data')
        $fileFields = array(
            $name => array(
                'type' => 'text/plain',
                'content' => $content
            ),
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

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER , array_merge($header, array(
            'Content-Type: multipart/form-data; boundary=' . $delimiter,
            'Content-Length: ' . strlen($data))));
        curl_setopt($ch, CURLOPT_TIMEOUT, 86400);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    public static function getPpFeature($filePath)
    {
        $size = filesize($filePath);
        $length = 12288;
        $str = '';
        $fp = fopen($filePath, 'r');
        fseek($fp, 0);
        $str .= fread($fp, $length);
        fseek($fp, $size / 5);
        $str .= fread($fp, $length);
        fseek($fp, 2 * $size / 5);
        $str .= fread($fp, $length);
        fseek($fp, 3 * $size / 5);
        $str .= fread($fp, $length);
        fseek($fp, $size - $length);
        $str .= fread($fp, $length);
        fclose($fp);

        return $size . '_' . sha1($str);
    }
}