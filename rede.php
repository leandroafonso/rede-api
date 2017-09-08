<?php
/**
 * Created by PhpStorm.
 * User: leandro
 * Date: 07/09/17
 * Time: 16:35
 */

class Rede{

    private $authorization;
    private $environment;


    public function __construct($environment="DEVELOPMENT",$authorization=null){

        if($environment == "PRODUCTION"){
            $this->environment = "PRODUCTION";
            $this->authorization = "";
            if(isset($authorization)){
                $this->authorization = $authorization;
            }
        }
        else{
            $this->environment = "DEVELOPMENT";
            $this->authorization = "NTAwNzk1NTc6NDkxM2JiMjRhMDI4NDk1NGJlNzJjNDI1OGUyMjliODY=";
            if($authorization){
                $this->authorization = $authorization;
            }
        }


    }

    /**
     * @param $json
     * @param $type
     * @return null
     */
    private function request($json, $path = "", $type='POST'){
        $headers = [
            'Authorization: Basic '.$this->authorization,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        if($this->environment == "DEVELOPMENT"){
            $url = "https://api-hom.userede.com.br/erede/v1/".$path;
            curl_setopt($ch, CURLOPT_URL,$url);
        }
        else if($this->environment == "PRODUCTION"){
            $url="https://api.userede.com.br/erede/v1/".$path;
            curl_setopt($ch, CURLOPT_URL,$url);
        }

        if($type == "POST"){
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        elseif($type == "PUT"){
            curl_setopt($ch, CURLOPT_PUT, 1);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($json!=""){
            if($type=="POST"){
                curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
            }
            else{
                curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($json));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        $out = json_decode($server_output,true);
       
        return $out;
    }


    /**
     * @param $data
     * @return mixed|null|string
     */
    public function approve($data){

        $user_agent = $_SERVER["HTTP_USER_AGENT"];
        $ds3='';
        if(isset($data["threeDSecure"])){
            $ds3 = ','.PHP_EOL.'"threeDSecure": {
                "embedded": true,
                "onFailure": "decline",
                "userAgent": "'.$user_agent.'"
            },
            "urls": [
                {
                    "kind": "threeDSecureSuccess",
                    "url": "https://domain.com/rede/3ds/success"
                },
                {
                    "kind": "threeDSecureFailure",
                    "url": "https://domain.com/rede/3ds/failure"
                }
            ]';
        }

        $json ='{
                    "distributorAffiliation":50079557,
                    "capture": '.$data["capture"].',
                    "reference":"'.$data["transaction"].'",
                    "amount":'.$data["amount"].',
                    "installments":'.$data["installments"].',
                    "softDescriptor":"'.$data["softDescriptor"].'",
                    "subscription":'.$data["subscription"].',
                    "distributorAffiliation":'.$data["distributorAffiliation"].',
                    "cardholderName":"'.$data["cardholderName"].'",
                    "cardNumber":"'.$data["cardNumber"].'",
                    "expirationMonth":'.$data["expirationMonth"].',
                    "expirationYear":'.$data["expirationYear"].',
                    "securityCode":"'.$data["securityCode"].'"{{3DS}}
        }';

        $json=str_replace("{{3DS}}",$ds3,$json);

        $json = $this->request($json,"transactions", "POST");
        return $json;
    }

    /**
     * @param $tid
     * @param $amount
     * @return null
     */
    public function capture($tid, $amount){
        $json = '{"amount":'.$amount.'}';
        echo "<br>transactions/".$tid."<br>";
        return $this->request($json,"transactions/".$tid, "PUT");
    }

    /**
     * @param $tid
     * @param $amount
     * @param $callback
     * @return null
     */
    public function refunds($tid, $amount, $callback){
        $json = '{
            "amount": '.$amount.',
            "urls": [
                {
                    "kind": "callback",
                    "url": "'.$callback.'"
                }
            ]
        }';
        return $this->request($json,"transactions/".$tid."/refunds","POST");
    }

    /**
     * @param $tid
     * @param $refundId
     * @return null
     */
    public function getRefunds($tid, $refundId){
        return $this->request("","transactions/".$tid."/refunds/".$refundId,"GET");
    }

    /**
     * @param $tid
     * @return null
     */
    public function getTransactionByTID($tid){
        echo "<br> transactions/$tid<br>";
        return $this->request("","transactions/".$tid,"GET");
    }

    public function getTransactionBySale($code){
        echo "<br>transactions?reference=".$code."<br>";
        return $this->request("","transactions?reference=".$code,"GET");
    }


}