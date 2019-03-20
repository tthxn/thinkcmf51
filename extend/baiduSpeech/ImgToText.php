<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/20
 * Time: 11:04
 */

namespace baiduSpeech;
include_once 'speech/AipOcr.php';

class ImgToText
{
    private $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate';
    private $app_id = '15797289';//替换为您的APPID
    private $api_key = 'f53WfhCrnMHRMx6UjOLLYsX0';//替换为您的密钥
    private $sec_key = 'rGRir4ggc6W4MCXKlKIRA0rOUfSaCLsQ';//替换为您的密钥
    private $client;


    public function __construct($appId, $apiKey,$secKey)
    {
        $this->app_id = $appId;
        $this->api_key = $apiKey;
        $this->sec_key = $secKey;
        $this->client = new \AipOcr($this->app_id, $this->api_key, $this->sec_key);
    }

    public function index($image){

        // 调用通用文字识别, 图片参数为本地图片
        return $this->client->basicGeneral($image);
    }


    /**
     * 身份证识别，
     * @url http://ai.baidu.com/docs#/OCR-PHP-SDK/top 百度身份证识别
     */
    public function IdCard($image,$idCardSide){

        // 调用身份证识别
        $this->client->idcard($image, $idCardSide);

        // 如果有可选参数
        $options = array();
        $options["detect_direction"] = "true";
        $options["detect_risk"] = "false";

        // 带参数调用身份证识别
        return $this->client->idcard($image, $idCardSide, $options);
    }
}