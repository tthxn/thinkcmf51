<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 17:12
 */

namespace baidu;


class Text2audio
{
    private $demo_curl_verbose = false;
    private $url = 'http://tsn.baidu.com/text2audio';
    private $api_key;//替换为您的api_key
    private $sec_key;//替换为您的密钥
    public $g_has_error = true;

    public function __construct($apiKey, $secKey)
    {
        $this->api_key = $apiKey;
        $this->sec_key = $secKey;
    }

    /**
     *
     * @param $text
     * @param int $per #发音人选择, 0为普通女声，1为普通男生，3为情感合成-度逍遥，4为情感合成-度丫丫，默认为普通女声
     * @param int $spd #语速，取值0-15，默认为5中语速
     * @param int $pit #音调，取值0-15，默认为5中语调
     * @param int $vol #音量，取值0-9，默认为5中音量
     * @param int $aue #下载的文件格式, 3：mp3(default) 4： pcm-16k 5： pcm-8k 6. wav
     */

    public function go($text, $per = 0, $spd = 5, $pit = 5, $vol = 5, $aue = 3)
    {
        $get_token = $this->getToken();
        if ($get_token['code'] != 1) {
            return $get_token;
        }

        /** 拼接参数开始 **/
        $cuid = "123456PHP";
        // tex=$text&lan=zh&ctp=1&cuid=$cuid&tok=$token&per=$per&spd=$spd&pit=$pit&vol=$vol
        $params = array(
            'tex' => urlencode($text), // 为避免+等特殊字符没有编码，此处需要2次urlencode。
            'per' => $per,
            'spd' => $spd,
            'pit' => $pit,
            'vol' => $vol,
            'aue' => $aue,
            'cuid' => $cuid,
            'tok' => $get_token['data'],
            'lan' => 'zh', //固定参数
            'ctp' => 1, // 固定参数
        );
        $paramsStr = http_build_query($params);

        /** 拼接参数结束 **/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramsStr);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'read_header']);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            exit(2);
        }
        curl_close($ch);

        if ($this->g_has_error){
            return ['code'=>0,'msg'=>$data];
        }else{
            return ['code'=>1,'data'=>$data];
        }
    }


    function read_header($ch, $header)
    {
        global $g_has_error;

        $comps = explode(":", $header);
        // 正常返回的头部 Content-Type: audio/*
        // 有错误的如 Content-Type: application/json
        if (count($comps) >= 2) {
            if (strcasecmp(trim($comps[0]), "Content-Type") == 0) {
                if (strpos($comps[1], "audio/") > 0) {
                    $this->g_has_error = false;
                } else {
                    echo $header . " , has error \n";
                }
            }
        }
        return strlen($header);
    }


    public function getToken()
    {
        $auth_url = "https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=" . $this->api_key . "&client_secret=" . $this->sec_key;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名,0不验证
        curl_setopt($ch, CURLOPT_VERBOSE, $this->demo_curl_verbose);
        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);

        //echo "Token URL response is " . $res . "\n";
        $response = json_decode($res, true);

        if (!isset($response['access_token'])) {
            echo "ERROR TO OBTAIN TOKEN\n";
            exit(1);
        }
        if (!isset($response['scope'])) {
            echo "ERROR TO OBTAIN scopes\n";
            exit(2);
        }

        if (!in_array('audio_tts_post', explode(" ", $response['scope']))) {
            echo "DO NOT have tts permission\n";
            // 请至网页上应用内开通语音合成权限
            exit(3);
        }

        $token = $response['access_token'];
        return ['code' => 1, 'data' => $token, 'msg' => 'success'];
    }


}