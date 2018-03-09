<?php


// 引入配置文件
require './wechat.cfg.php';

class Wechat
{

    // 封装类成员
    // private  私有
    // public  公共
    // protected  受保护
    // 构造方法
    public function __construct()
    {
        //实列时会触发，进行相关参数的初始化操作
        $this->token = TOKEN;
        // 模板
        $this->textTpl = "<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%s</CreateTime>
        <MsgType><![CDATA[%s]]></MsgType>
        <Content><![CDATA[%s]]></Content>
        <FuncFlag>0</FuncFlag>
        </xml>";
    }

    // 类相关方法实现
    // 调用校验
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    // 消息管理
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        // file_put_contents('./debug.txt', $postStr);
        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
              the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            // 通过接收不同的数据类型，进行不同的方法处理
            switch ($postObj->MsgType) {
                // 调用文本处理方法
                case 'text':
                    $this->doText($postObj);
                    break;
                // 调用图片处理方法
                case 'image':
                    $this->doImage($postObj);
                    break;
                // 调用语音处理方法
                case 'voice':
                    $this->doVoice($postObj);
                    break;
                // 调用位置处理方法
                case 'location':
                    $this->doLocation($postObj);
                    break;
                default:
                    break;
            }
        }
    }

    // 检查签名
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    // 文本消息处理
    private function doText($postObj)
    {
        $keyword = trim($postObj->Content);
        if (!empty($keyword)) {
            // 通过用户传输的不同的文本值，进行不同的回复
            $contentStr = "Welcome to wechat world!";
            if ($keyword === '你是谁') {
                $contentStr = '我是PHP学院的小秘书,小象';
            }
            // 接入自动回复机器人
            $content = file_get_contents('http://api.qingyunke.com/api.php?key=free&appid=0&msg='.$keyword);
            // 转json取数据
            $contentStr = json_decode($content)->content;
            // 替换换行
            $contentStr = str_replace('{br}', "\r", $contentStr);
            $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
            echo $resultStr;
        }
    }
    // 图片消息处理
    private function doImage($postObj)
    {
        // 获取图片地址
        $PicUrl = $postObj->PicUrl;
        // 以文本消息回复
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $PicUrl);
        // file_put_contents('./return.txt',$resultStr);
        echo $resultStr;
    }
    // 语音消息处理
    private function doVoice($postObj)
    {
        $contentStr = '您发送的语音已经接收到,MediaId:'.$postObj->MediaId;
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
        // file_put_contents('./return.txt',$resultStr);
        echo $resultStr;
    }
    // 位置消息处理
    private function doLocation($postObj)
    {
        // 返回用户经纬度信息
        $locationX = $postObj->Location_X;
        $locationY = $postObj->Location_Y;
        $contentStr = '您所在位置:经度'.$locationY.' 纬度'.$locationX;
        $resultStr = sprintf($this->textTpl,$postObj->FromUserName,$postObj->ToUserName,time(),'text',$contentStr);
        file_put_contents('location.txt',$resultStr);
        echo $resultStr;
    }
    // 封装请求方法
    // curl 四步 支持http和HTTPS协议 支持get和post请求
    private function request($url,$https=true)
    {
        // 1.初始化
        $ch =curl_init($url);
        // 2.配置请求参数
        curl_setopt(ch, option, value);
        if ($https === true) {
            curl_setopt($ch, option, value);
        }
    }
}
