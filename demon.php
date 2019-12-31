<?php


require_once "./vendor/autoload.php";
use \WebPay\WebPay\Pay;

$config = [
    'publicKeyFile' => '/Users/xxx/Downloads/test.pem',
    'private_url' => '/Users/xxxx/Downloads/131574410808744.p12',
    'noticeUrl' => 'http://xxxx.xxx.com/market/Notify/notify',
    'merId' => '14108xx08744',
    'channelType' => '101',
];

$pay = new Pay($config);
$params = array(
    'version' => '1.0',
    'encoding' => 'UTF-8',
    'signMethod' => '01',
    //01 消费交易  02撤销交易 03退款交易
    'txnType' => '01',
    'orderId' => time() . rand(10000, 99999),
    'txnTime' => date('YmdHis', time()),
    'currency' => 'rmb',
    'txnAmt' => "1",
    'payTimeout' => date('YmdHis', time() + 300),
);
//发起支付 获取支付地址
$url = $pay->getPayUrl($params);
//这是支付地址 直接跳转就可以支付了
echo $url;

//订单回调
$content = $_POST;
$tool = new Pay();
$sign = $content['signature'];
unset($content['signature']);
$str = $tool->getSignContent($content);
$result = $tool->unsign($sign, $str);
if (!$result) {
    return;
}
//执行业务代码