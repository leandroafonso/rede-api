<?php
/**
 * Created by PhpStorm.
 * User: leandro
 * Date: 07/09/17
 * Time: 16:54
 */
require "rede.php";
$rede = new Rede("DEVELOPMENT");
$sale = "9998";
echo "Decrementar este numero: ".$sale." para gerar nova transação, caso utilizar a chave padrao da documentação.";

$user_agent = $_SERVER['HTTP_USER_AGENT'];
$data=["capture"=> "false",
    "transaction" => $sale,
    "amount" => "500900",
    "installments"=> "0",
    "softDescriptor" => "Plano Detox",
    "subscription" => "false",
    "distributorAffiliation" => "0",
    "cardholderName" => "John Snow",
    "cardNumber" => "5448280000000007",
    "expirationMonth" => "1",
    "expirationYear" =>  "2019",
    "securityCode" => "132",
//    "threeDSecure" => [
//        "embedded" => "true",
//        "onFailure" => "decline",
//        "userAgent" => "$user_agent"
//    ],
//    "urls" => [
//        [
//            "kind" => "threeDSecureSuccess",
//            "url" => "https://domain.com/rede/3ds/success"
//        ],
//        [
//            "kind" => "threeDSecureFailure",
//            "url" => "https://domain.com/rede/3ds/fail"
//        ]
//    ]
];
echo "Approve: <br>".PHP_EOL;
$data = $rede->approve($data);
var_dump($data);

echo "<br><br> capture: <br>".PHP_EOL;
var_dump($rede->capture($data["tid"],"500900"));

echo "<br><br> getTransactionByTID: <br>".PHP_EOL;
var_dump($rede->getTransactionByTID($data["tid"]));

echo "<br><br> getTransactionBySale: <br>".PHP_EOL;
var_dump($rede->getTransactionBySale($sale));

echo "<br><br> Refunds: <br>".PHP_EOL;
var_dump($rede->refunds($data["tid"],"500900","http://domain.com/rede/callback-refunds.php"));
