<?php namespace xing393939;

class Unit
{
    public static function send_post($url, array $post_data = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 75);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function send_json_post($url, $post_data = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 75);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data))
        );
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function send_get($url)
    {
        $header = array('Expect:');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 75);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function send_put_file($url, $fileName)
    {
        $fp = fopen($fileName, 'r');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($fileName));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        ob_start();
        $r = curl_exec($ch);
        curl_close($ch);
        $r = ob_get_contents();
        ob_clean();
        preg_match('/Etag: (\w+)/', $r, $match);
        $uploadId = isset($match[1]) ? $match[1] : '';
        return $uploadId;
    }

    public static function ll_dir($dir)
    {
        $dir_handle = opendir($dir);
        $array = array();
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                $array[] = array(
                    'file' => "{$dir}/{$file}",
                    'fileatime' => fileatime("{$dir}/{$file}"),
                );
            }
        }
        closedir($dir_handle);
        return $array;
    }

    public static function big_file_array($ppFeature)
    {
        $dir = UPLOAD_DIR . "/{$ppFeature}";
        if (!is_dir($dir)) {
            return array();
        }
        $dir_handle = opendir($dir);
        $array = array();
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                $arr = explode('_', $file);
                $array[$arr[0]] = ["{$dir}/{$file}", $arr[0], $arr[1]];
            }
        }
        closedir($dir_handle);
        ksort($array);
        return $array;
    }

    public static function big_file_read($fileArray, $start, $length)
    {
        $return = '';
        foreach ($fileArray as $fileArr) {
            if ($start >= $fileArr[1] && $start <= $fileArr[2]) {
                $fp = fopen($fileArr[0], 'r');
                fseek($fp, $start - $fileArr[1]);
                $tmp = fread($fp, $length);
                fclose($fp);
                if ($tmp) {
                    $return .= $tmp;
                    $start += strlen($tmp);
                    $length -= strlen($tmp);
                    if ($length <= 0) break;
                }
            }
        }
        return $return;
    }

    public static function api_cache($ppFeature, $key, $value = null)
    {
        $dir = UPLOAD_DIR . "/cache";
        if (!is_dir($dir)) @mkdir($dir);
        $dir .= "/{$ppFeature}";
        if (!is_dir($dir)) @mkdir($dir);
        if (null === $key) {
            delDirAndFile($dir);
            return true;
        }
        $cacheFile = "{$dir}/{$key}";
        if (null !== $value) {
            file_put_contents($cacheFile, serialize($value));
            return true;
        } else {
            $value = @file_get_contents($cacheFile);
            return unserialize($value);
        }
        return false;
    }

    public static function big_file_md5($fileArray)
    {
        $ctx = hash_init('md5');
        foreach ($fileArray as $fileArr) {
            hash_update_file($ctx, $fileArr[0]);
        }
        return hash_final($ctx);
    }

    public static function big_file_cid($fileArray, $file_size)
    {
        $xunlei_cid = '';
        $target = '';
        if ($file_size < 61440) {
            $xunlei_cid = big_file_read($fileArray, 0, $file_size);
        } else {
            $target .= big_file_read($fileArray, 0, 20480);
            $target .= big_file_read($fileArray, floor($file_size / 3), 20480);
            $target .= big_file_read($fileArray, floor($file_size - 20480), 20480);
            $xunlei_cid = "{$file_size}_" . sha1($target);
        }
        return $xunlei_cid;
    }

    public static function big_file_gcid($fileArray, $file_size)
    {
        $xunlei_gcid = '';
        $target = '';
        $p_size = 0x40000;
        while ($file_size / $p_size > 0x200)
            $p_size = $p_size << 1;
        $data = big_file_read($fileArray, 0, $p_size);
        $i = 1;
        while ($data) {
            $target .= sha1($data, TRUE);
            $data = big_file_read($fileArray, $p_size * $i, $p_size);
            $i++;
        }
        $xunlei_gcid = "{$file_size}_" . sha1($target);
        return $xunlei_gcid;
    }

    public static function delDirAndFile($dirName)
    {
        if (is_dir($dirName)) {
            $handle = @opendir("$dirName");
            while ($item = @readdir($handle)) {
                if ($item != "." && $item != "..") {
                    delDirAndFile("$dirName/$item");
                }
            }
            @closedir($handle);
            @rmdir($dirName);
        } else {
            @unlink($dirName);
        }
    }
}