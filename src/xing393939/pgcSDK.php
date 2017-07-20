<?php namespace xing393939;

use xing393939\Unit;

class PgcSDK
{
    const OPEN_PLATFORM_HOST = 'http://svc.pptvyun.com/svc/v1/api';
    const PPC_HOST = 'http://ppc.pptvyun.com/fsvc/3/file';

    private $username;
    private $apiKey;

    public function __construct($username, $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    public function upload($filePath, $suffix)
    {
        $ppFeature = $this->getPpFeature($filePath);

        //获取uptoken
        $url = self::OPEN_PLATFORM_HOST . '/token/uptoken';
        $params = array(
            'username' => $this->username,
            'ppfeature' => $ppFeature,
            'apitk' => md5($this->apiKey . $url),
        );
        $arr = json_decode(Unit::send_get($url . '?' . http_build_query($params)), 1);

        return $arr;

        //获取fid

        //取uploadRange

        //上传片段

        //完成

    }

    public function getUploadInfoByFilePath($filePath)
    {

    }

    public function getPpFeature($filePath)
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