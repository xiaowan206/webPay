<?php
/**
 * Created by PhpStorm.
 * User: mozheng
 * Date: 2019/12/31
 * Time: 下午3:38
 */

namespace WebPay\WebPay;


class Pay
{

    /**
     * @var string 获取私钥密码   一般情况都是111111
     */
    private $password;

    /**
     * @var string 公钥证书路径
     */
    private $publicKeyFile;

    /**
     * @var string 私钥证书路径
     */
    private $private_url;

    private $pay_url = 'http://api.jdxiongmao.com/api-api/gateway/trade';

    private $bill_url = 'http://api.jdxiongmao.com/api-api/gateway/bill';

    private $merId;

    private $noticeUrl;

    //01 消费交易  02撤销交易 03退款交易
    private $channelType;


    public function __construct($config = array())
    {
        $this->password = isset($config['password']) ? $config['password'] : '111111';
        $this->publicKeyFile = isset($config['publicKeyFile']) ? $config['publicKeyFile'] : '';
        $this->private_url = isset($config['private_url']) ? $config['private_url'] : '';
        $this->merId = isset($config['merId']) ? $config['merId'] : '';
        $this->noticeUrl = isset($config['noticeUrl']) ? $config['noticeUrl'] : '';
        $this->channelType = isset($config['channelType']) ? $config['channelType'] : '';

    }

    /**
     * @desc    字符集转换
     *
     * @param        $data
     * @param string $targetCharset
     *
     * @return string
     * @author  wz
     * @time    2019/12/31 下午3:40
     */
    function characet($data, $targetCharset = "UTF-8")
    {
        if (!empty($data)) {
            $fileType = 'UTF-8';
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }


        return $data;
    }


    /**
     * @desc   生成加密签名
     *
     * @param $data
     * @param $private_url
     *
     * @return string
     * @author wz
     * @time   2019/12/31 下午3:40
     */
    function sign($data)
    {
        $priKey = file_get_contents($this->private_url);
        openssl_pkcs12_read($priKey, $certs, $this->password);

        openssl_sign($data, $sign, $certs['pkey']);

        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * @desc    解密签名 判断回调来源
     *
     * @param $sign
     * @param $data
     *
     * @return int
     * @author  wz
     * @time    2019/12/31 下午3:41
     */
    public function unsign($sign, $data)
    {
        $key = file_get_contents($this->publicKeyFile);
        $unsignMsg = base64_decode($sign);//base64解码加密信息
        $res = openssl_verify($data, $unsignMsg, $key); //验证
        return $res;
    }

    /**
     * @desc    生成加密所需字符串
     *
     * @param $params
     *
     * @return string
     * @author  wz
     * @time    2019/12/31 下午3:41
     */
    function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            // 转换成目标字符集
            $v = $this->characet($v, 'UTF-8');

            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }

        unset ($k, $v);

        return $stringToBeSigned;
    }


    function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    /**
     * @desc
     *
     * @param $url
     * @param $data
     *
     * @return bool|mixed
     * @author wz
     * @time   2019/12/31 下午3:42
     */
    function post_curl($url, $data)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($data)) {
            $strPOST = $data;
        } else {
            $aPOST = array();
            foreach ($data as $key => $val) {
                $aPOST [] = $key . "=" . $val;
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus ["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    public function getPayUrl($params)
    {
        $params['merId'] = $this->merId;
        $params['noticeUrl'] = $this->noticeUrl;
        $params['channelType'] = $this->channelType;
        //这里写死
        $params['txnType'] = '01';
        $content = $this->getSignContent($params);
        $sign = $this->sign($content);
        $params['signature'] = urlencode($sign);
        $rst = $this->post_curl($this->pay_url, $params);
        parse_str($rst, $result);
        if (isset($result['payCode']))
            return $result['payCode'];
        return false;
    }

    public function uploadBill($params)
    {
        $params['merId'] = $this->merId;
        //这里写死
        $content = $this->getSignContent($params);
        $sign = $this->sign($content);
        $params['signature'] = urlencode($sign);
        $rst = $this->post_curl($this->bill_url, $params);
        parse_str($rst, $result);
        return $result;
    }

}