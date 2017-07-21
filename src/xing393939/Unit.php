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

    public static function send_put_file($url, $json, $header = array())
    {
        $header[] = 'Content-Type: multipart/form-data';
        $header[] = 'Content-Length: ' . strlen($json);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 86400);
        curl_exec($ch);
        $r = curl_getinfo($ch);
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