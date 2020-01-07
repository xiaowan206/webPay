<?php


require_once "./src/WebPay/WebPay/Pay.php";
use \WebPay\WebPay\Pay;

$config = [
    //
    'publicKeyFile' => '/Users/mozheng/Downloads/test.pem',
    'private_url' => '/Users/mozheng/Downloads/131574410808744.p12',
    'noticeUrl' => 'http://small.jdxiongmao.com/market/Notify/notify',
    'merId' => '131574410808744',
    'channelType' => '101',
];

$pay = new Pay($config);
$params = array(
    'version' => '1.0',
    'encoding' => 'UTF-8',
    'signMethod' => '01',
    //01 消费交易  02撤销交易 03退款交易
    'txnType' => '01',
    'orderId' => time().rand(10000,99999),
    'txnTime' => date('YmdHis', time()),
    'currency' => 'rmb',
    'txnAmt' => "1",
    'payTimeout' => date('YmdHis', time() + 300),
);

$url = $pay->getPayUrl($params);
//这是支付地址 直接跳转就可以支付了

echo $url;


//下载对账单
$params = array(
    'version' => '1.0',
    'encoding' => 'UTF-8',
    'signMethod' => '01',
    //01 消费交易  02撤销交易 03退款交易
    'txnType' => '07',
    'billDate' => date('Ymd', time()),
);

$result = $pay->uploadBill($params);
var_dump($result);