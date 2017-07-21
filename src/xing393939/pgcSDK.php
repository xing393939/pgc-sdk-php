<?php namespace xing393939;

use xing393939\Unit;

class PgcSDK
{
    const OPEN_PLATFORM_HOST = 'http://svc.pptvyun.com/svc/v1/api';
    const PPC_HOST = 'http://ppc.pptvyun.com/fsvc/3/file';

    private $username;
    private $apiKey;
    private $errorMsg;

    public function __construct($username, $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    public function upload($filePath, $categoryId, $name, $summary)
    {
        $ppFeature = Unit::getPpFeature($filePath);

        //获取uptoken
        $url = self::OPEN_PLATFORM_HOST . '/token/uptoken';
        $params = array(
            'username' => $this->username,
            'ppfeature' => $ppFeature,
            'apitk' => md5($this->apiKey . $url),
        );
        $tokenRs = json_decode(Unit::send_get($url . '?' . http_build_query($params)), 1);

        //获取fid
        $url = self::OPEN_PLATFORM_HOST . '/channel/upload';
        $postData = array(
            'username' => $this->username,
            'apitk' => md5($this->apiKey . $url),
            'categoryid' => $categoryId,
            'name' => urlencode($name),
            'summary' => urlencode($summary),
            'coverimg' => '',
            'length' => filesize($filePath),
            'ppfeature' => $ppFeature,
        );
        $fidRs = json_decode(Unit::send_json_post($url, $postData), 1);

        //获取上传信息信息
        $url = self::PPC_HOST . "/{$fidRs['data']['fId']}/uploading?fromcp=private_cloud";
        $header = [
            "Authorization: {$tokenRs['data']}",
            'Accept: application/json',
        ];
        $uploadingRs = json_decode(Unit::send_get($url, $header), 1);

        //上传md5
        $md5 = strtoupper(md5_file($filePath));
        $url = self::PPC_HOST . "/{$fidRs['data']['fId']}/md5?fromcp=private_cloud&feature_pplive={$ppFeature}&md5={$md5}";
        $header = [
            "Authorization: {$tokenRs['data']}",
            'Accept: application/json',
        ];
        $md5Rs = json_decode(Unit::send_json_post($url, $postData, $header), 1);

        //uploadRange
        $rangeRs = $this->uploadRange($filePath, $ppFeature, $fidRs['data']['fId'], $tokenRs['data']);

        //完成
        return [$tokenRs, $fidRs, $uploadingRs, $md5Rs, $rangeRs];
    }

    private function uploadRange($filePath, $ppFeature, $fid, $token) {
        $url = self::PPC_HOST . "/{$fid}/action/uploadrange?fromcp=private_cloud&feature_pplive={$ppFeature}&segs=1&inner=false";
        $header = [
            "Authorization: {$token}",
            'Accept: application/json',
        ];
        $rangeRs = json_decode(Unit::send_get($url, $header), 1);
        if (!isset($rangeRs['err']) || $rangeRs['err'] != 0) {
            $this->errorMsg = 'PPC_HOST action/uploadrange api error';
            return false;
        }
        //分段没有传完继续传
        if ($rangeRs['data']['fileSize'] <= $rangeRs['data']['fileSize']) {
            if (empty($rangeRs['data']['ranges']) || empty($rangeRs['data']['ranges'][0])) {
                $this->errorMsg = 'PPC_HOST action/uploadrange api error';
                return false;
            }

            //传分片
            $range = $rangeRs['data']['ranges'][0];
            $fp = fopen($filePath, 'r');
            fseek($fp, $range['start']);
            $str = fread($fp, $range['end'] - $range['start']);
            fclose($fp);
            $uploadId = md5($str);
            $header = [
                "Authorization: {$token}",
                'Accept: */*',
                'Etag: ' . md5($str)
            ];
            $uploadStr = Unit::send_put_file($range['upload_url'], $str, $header);

            //传分片成功
            $md5 = strtoupper($uploadId);
            $url = self::PPC_HOST . "/{$fid}/action/uploaded?fromcp=private_cloud&range_md5={$md5}&bid={$range['bid']}&uploadid={$uploadId}";
            $header = [
                "Authorization: {$token}",
                'Accept: application/json',
            ];
            $uploadedRs = json_decode(Unit::send_json_post($url, array(), $header), 1);

            //$this->uploadRange($filePath, $ppFeature, $fid, $token);
        }
        return $uploadedRs;
    }
}