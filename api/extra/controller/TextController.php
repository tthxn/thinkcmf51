<?php

namespace api\extra\controller;


use baidu\Text2audio;
use baidu\Translate;

class TextController
{
    //注意：这里不用继承RestBaseController，RestBaseController需要用户登录

    /**
     * 语言翻译
     *
     * 注意事项：
     *  1、请将单次请求长度控制在 6000 bytes以内。（汉字约为2000个）
     *  2、源语言语种不确定时可设置为 auto，目标语言语种不可设置为 auto。
     *
     * @url http://api.fanyi.baidu.com/api/trans/product/apidoc 百度翻译开发者平台
     * @result 翻译返回结果
     * {
     *      from: "zh",
     *      to: "en",
     *      trans_result: [
     *          {
     *              src: "2019年3月5日上午九点，第十三届全国人民代表大会第二次会议在人民大会堂开幕，国务院总理李克强向大会作政府工作报告。",
     *              dst: "At 9 a.m. on March 5, 2019, the second session of the 13th National People's Congress opened in the Great Hall of the People. Premier Li Keqiang of the State Council made a report on the work of the government to the General Assembly."
     *          }
     *      ]
     * }
     *
     */
    public function textToTranslate()
    {
        $content = '2019年3月5日上午九点，第十三届全国人民代表大会第二次会议在人民大会堂开幕，国务院总理李克强向大会作政府工作报告。';

        //这里需要进行字数检测、敏感词检测……


        /*
         * //获取百度翻译相关配置信息
        $app_config = cmf_get_option('app_config');
        if (empty($app_config['baidu_translate_app_id']) || empty($app_config['baidu_translate_app_secret'])){
            exit("暂未完成翻译配置");
            //$this->error(lang("暂未完成翻译配置"));
        }

        //进行翻译操作
        $trans = new Translate($app_config['baidu_translate_app_id'],$app_config['baidu_translate_app_secret']);*/

        $trans = new Translate('XXXXXXXXXXXXXXX', 'XXXXXXXXXXXXXXXXXXXXXX');
        //'auto'为自己检测原语种，'en'为目标语种
        $res = $trans->translate($content, 'auto', 'en');

        echo json_encode($res);
        die();

    }


    /**
     * 文本转化为语音
     */
    public function textToAudio()
    {
        $content = '2019年3月5日上午九点，第十三届全国人民代表大会第二次会议在人民大会堂开幕，国务院总理李克强向大会作政府工作报告。';

        //这里需要进行字数检测、敏感词检测……

        /*
         * //获取百度语音相关配置信息
        $app_config = cmf_get_option('app_config');
        if (empty($app_config['baidu_translate_app_id']) || empty($app_config['baidu_translate_app_secret'])){
            exit("暂未完成翻译配置");
            //$this->error(lang("暂未完成翻译配置"));
        }

        //进行操作
        $audio = new Text2audio($app_config['baidu_translate_app_id'],$app_config['baidu_translate_app_secret']);*/

        $audio = new Text2audio('XXXXXX', 'XXXXXXXXXXXXXXXXXX');
        $res = $audio->go($content);//如果返回成功，那么直接返回MP3文件格式码

        if ($res['code'] == 0) {
            exit('服务器内部错误');
        }

        //将获取到的内容保存下来
        $filePath = CMF_ROOT . 'public/upload/mp3/'. date('Ymd') .'/';
        if (!is_dir($filePath)) {
            $result = mkdir($filePath, 0777, true);
            if (!$result) {
                exit('文件创建失败');
            }
        }
        $fileName = md5(uniqid()) . md5(uniqid()) . '.mp3';
        $filePutResult = file_put_contents($filePath . $fileName, $res['data']);//注意：file_put_contents方法不可以存储数组

        echo $filePutResult;//返回的是文件大小
    }
}