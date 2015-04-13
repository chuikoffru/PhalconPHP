<?php

namespace Lib;

use \Phalcon\Mvc\User\Component;

class Iiko extends Component
{

    public $api_url = 'https://iiko.net:9900/api/0/';
    public $lifetime = 600;

    public function getConnectionIiko()
    {
        if (!$this->getMaxLifeTime()) {
            $this->getToken();
        }
    }

    public function citiesList()
    {
        return $this->curl_get($this->api_url . 'cities/c​itiesList', array(
            'access_token' => $this->getTokenKey(),
            'organization' => $this->getOrganizationId()
        ));
    }

    public function getOrderTypes()
    {
        return $this->curl_get($this->api_url . 'rmsSettings/getOrderTypes', array(
            'access_token' => $this->getTokenKey(),
            'organization' => $this->getOrganizationId()
        ));
    }

    public function createOrder($order)
    {

        $order['customer']['id']=$this->getGUID();//у покупателя должен быть guid, сгенерируем его
        $order['order']['id']=   $this->getGUID();// у заказа должен быть guid, сгенерируем его
        $order['order']['date'] = date('Y-m-d H:i:s');//у заказа должна быть дата в данном формате 2014-04-15 12:15:20

        return $this->curl_post($this->api_url . 'orders/add?access_token='.$this->getTokenKey(),json_encode($order),array(
            CURLOPT_HTTPHEADER=> array('Content-Type: application/json; charset=utf-8')
        ));
    }

    public function getNomenclature()
    {
        return $this->curl_get($this->api_url . 'nomenclature/'.$this->getOrganizationId(), array(
            'access_token' => $this->getTokenKey(),
            'organization_id' => $this->getOrganizationId()
        ));
    }

    public function getEmployees()
    {
        return $this->curl_get($this->api_url . 'rmsSettings/getEmployees', array(
            'access_token' => $this->getTokenKey(),
            'organization' => $this->getOrganizationId()
        ));
    }

    public function getOrganizationList()
    {
        return $this->curl_get($this->api_url . 'organization/list', array(
            'access_token' => $this->getTokenKey()
        ));
    }

    private function setOrganizationId($id)
    {
        $this->persistent->company = $id;
    }

    private function getOrganizationId()
    {
        return $this->persistent->company;
    }

    private function getMaxLifeTime()
    {
        return $this->getTokenTimeActive() > time();
    }

    private function getTokenKey()
    {
        return $this->persistent->token;
    }

    private function getTokenTime()
    {
        return $this->persistent->lifetime;
    }

    private function getTokenTimeActive()
    {
        return $this->persistent->lifetime + $this->lifetime;
    }

    private function setToken($token)
    {
        $this->persistent->token = trim($token,'"');
        $this->persistent->lifetime = time();
    }

    private function removeToken()
    {
        return $this->persistent->destroy();
    }

    private function getToken()
    {
        $token = $this->curl_get($this->api_url . 'auth/access_token', array(
            'user_id' => $this->config->iiko->login,
            'user_secret' => $this->config->iiko->password
        ));

        $this->setToken($token);

        $company = json_decode($this->getOrganizationList());
        $this->setOrganizationId($company[0]->id);

        $this->log->log('Получили токен - ' . $this->getTokenKey() . ' для ' . $this->getOrganizationId());
    }

    private function curl_post($url, $post = null, array $options = array()) {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_SSL_VERIFYHOST =>0,//unsafe, but the fastest solution for the error " SSL certificate problem, verify that the CA cert is OK"
            CURLOPT_SSL_VERIFYPEER=>0, //unsafe, but the fastest solution for the error " SSL certificate problem, verify that the CA cert is OK"
            CURLOPT_POSTFIELDS => $post
        );
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch)){
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    private function getGUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charId = md5(uniqid(rand(), true));
            $hyphen = chr(45);// "-"
            $uuid =substr($charId, 0, 8).$hyphen
                .substr($charId, 8, 4).$hyphen
                .substr($charId,12, 4).$hyphen
                .substr($charId,16, 4).$hyphen
                .substr($charId,20,12);
            return $uuid;
        }
    }

    private function curl_get($url, array $get = NULL, array $options = array())
    {
        $defaults = [
            CURLOPT_URL => $url . (strpos($url, '?') === FALSE ? '?' : '') . http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_DNS_USE_GLOBAL_CACHE => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSLVERSION => 3,
            CURL_SSLVERSION_TLSv1 => 1
        ];
        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);
        return $result;
    }

} 