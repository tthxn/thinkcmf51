<?php

namespace baidu;

class Translate
{
    private $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate';
    private $app_id;//替换为您的APPID
    private $sec_key;//替换为您的密钥


    public function __construct($appId, $secKey)
    {
        $this->app_id = $appId;
        $this->sec_key = $secKey;
    }

    /**
     * @param $query 原文本
     * @param $from 原语种 设置为 auto 表示自动检测
     * @param $to 目标语种
     * @return bool|mixed|string
     */
    public function translate($query, $from, $to)
    {
        $args = array(
            'q' => $query,
            'appid' => $this->app_id,
            'salt' => rand(10000, 99999),
            'from' => $from,
            'to' => $to,

        );
        $args['sign'] = $this->buildSign($query, $this->app_id, $args['salt'], $this->sec_key);
        $ret = $this->call($this->url, $args);
        $ret = json_decode($ret, true);
        return $ret;
    }


    //加密
    public function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

    //发起网络请求
    public function call($url, $args = null, $method = "post", $testflag = 0, $timeout = 10, $headers = array())
    {
        $ret = false;
        $i = 0;
        while ($ret === false) {
            if ($i > 1)
                break;
            if ($i > 0) {
                sleep(1);
            }
            $ret = $this->callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    public function callOnce($url, $args = null, $method = "post", $withCookie = false, $timeout = 10, $headers = array())
    {
        $ch = curl_init();
        if ($method == "post") {
            $data = $this->convert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            $data = $this->convert($args);
            if ($data) {
                if (stripos($url, "?") > 0) {
                    $url .= "&$data";
                } else {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($withCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    public function convert(&$args)
    {
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                    }
                } else {
                    $data .= "$key=" . rawurlencode($val) . "&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }
}