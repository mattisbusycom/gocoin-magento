<?php

require_once(Mage::getBaseDir('lib') . '/gocoin/src/GoCoin.php');

class Gocoin_Gocoinpayment_Helper_Data extends Mage_Payment_Helper_Data
{
    const CLIENT_ID      = 'payment/Gocoinpayment/client_id';
    const CLIENT_SECRET  = 'payment/Gocoinpayment/client_secret';
    const ACCESS_TOKEN   = 'payment/Gocoinpayment/access_token';
    
//    function createClient() {
//        $storeId = Mage::app()->getStore()->getId();
//        
//        $client_id = Mage::getStoreConfig(self::CLIENT_ID);
//        $client_secret = Mage::getStoreConfig(self::CLIENT_SECRET);
//        
//        include Mage::getBaseDir('lib').'/gocoin/src/client.php';
//        
//        $client = new Client( array(
//            'client_id' => $client_id,
//            'client_secret' => $client_secret,
//            'scope' => "user_read_write+merchant_read_write+invoice_read_write",
//        ));
//        
//        $access_token = Mage::getStoreConfig(self::ACCESS_TOKEN);
//        if ($access_token != '') {
//            $client->setToken($access_token);
//        }
//        
//        return $client;
//    }

    function createInvoice($orderId, $price, $options = array(),$coin_type='BTC') {
       //$client = $this->createClient();
        // data for invoice creation
        $my_data = array (
            "price_currency" => $coin_type,
            "base_price" => $price,
            "base_price_currency" => "USD",//$options['currency'],
            "notification_level" => "all",
            "callback_url" => $options['callback_url'],
            "redirect_url" => $options['redirect_url'] ,
            "order_id" => $orderId,
            "customer_name" => $options['customer_name'],
            "customer_address_1" => $options['customer_address_1'],
            "customer_address_2" => $options['customer_address_2'],
            "customer_city" => $options['customer_city'],
            "customer_region" => $options['customer_region'],
            "customer_postal_code" => $options['customer_postal_code'],
            "customer_country" => $options['customer_country'],
            "customer_phone" => $options['customer_phone'],
            "customer_email" => $options['customer_email'],
        );

        $client_id = Mage::getStoreConfig(self::CLIENT_ID);
        $client_secret = Mage::getStoreConfig(self::CLIENT_SECRET);
        $access_token = Mage::getStoreConfig(self::ACCESS_TOKEN);
        
        if( empty($access_token))
        {
            $obj = new stdClass();
            $obj->error = "GoCoin Payment Paramaters not Set. Please report this to Site Administrator.";
            return $obj;
        }
        
        $unique_id = $this->getGUID();
        $fingerprint = $this->getSignatureText($my_data, $unique_id);
        $my_data = array_merge($my_data, array('user_defined_8' => $fingerprint));
        
        try{
            $user = GoCoin::getUser($access_token);
            if($user)
            {
                $merchant_id = $user->merchant_id;
                if(!empty($merchant_id))
                {
                    $invoice = GoCoin::createInvoice($access_token, $merchant_id, $my_data);
                    return $invoice;
                }
            }
        }catch(Exception $e)
        {
            $response = new stdClass();
            $response->error = $e->getMessage();
            return $response;
        }
        
    }
    
    function getAccessToken($code) {
        $client_id = Mage::getStoreConfig(self::CLIENT_ID);
        $client_secret = Mage::getStoreConfig(self::CLIENT_SECRET);
        //$access_token = Mage::getStoreConfig(self::ACCESS_TOKEN);
        $result = array();
        try{
            $token= GoCoin::requestAccessToken($client_id, $client_secret, $code, null);
            $result['success'] = true;
            $result['data'] = $token;
        }catch(Exception $e)
        {
            $result['success'] = false;
            $result['data'] = $e->getMessage();
        }

        return $result;
    }

    /**
    * Get Invoice by id
    * 
    * @param mixed $invoiceId
    * @param mixed $client
    * 
    * @return Object $response
    */

    function getInvoice($invoiceId, $client) {
        $access_token = Mage::getStoreConfig(self::ACCESS_TOKEN);
        $response = GoCoin::getInvoice($token,$invoiceId);
        
//        
//        
//        if (!$client) {
//            $response = new stdClass();
//            $response->error = $client->getError();
//            return $response;
//        }
//        
//        $response = $client->api->invoices->get($invoiceId);

        return $response;    
    }

    /**
    * Get Post Data for callback
    * 
    * @param Client $client
    * 
    * @return Object $response
    */

    function getNotifyData() {
        //get webhook content
        $post_data = file_get_contents("php://input");
        if (!$post_data) {
            $response = new stdClass();
            $response->error = 'Post Data Error';
            return $response;
        }
        Mage::log($post_data);
        $response = json_decode($post_data);
        return $response;
    }
    
    function addInvoiceData($quoteId, $invoice) {
        //remove existing items for quoteId
        $collection = Mage::getModel('Gocoinpayment/ipn')->getCollection()
                                    ->AddFilter('order_id', $quoteId);
        if (count($collection) > 0) {
            foreach( $collection as $item) {
                $item->delete();
            }
        }
        //add invoice data to database
        return Mage::getModel('Gocoinpayment/ipn')->addInvoiceData($invoice, 'pending_payment');
    }
    
    function getSignatureText($data, $uniquekey)
    {
        $query_str= '';
        $include_params = array('price_currency','base_price','base_price_currency','order_id','customer_name','customer_city','customer_region','customer_postal_code','customer_country','customer_phone','customer_email');
        //$escape_params = array('callback_url','redirect_url','customer_address_1','customer_address_2','user_defined_1','user_defined_2','user_defined_3','user_defined_4','user_defined_5','user_defined_6','user_defined_7','user_defined_8');
        if(is_array($data))
        {
            ksort($data);
            $querystring = "";
            foreach($data as $k => $v)
            { 
                if(in_array($k, $include_params)){
                    $querystring = $querystring . $k . "=" . $v . "&"; 
                }
            }
        }
        else
        {
            if(isset($data->payload))
            {
                $payload_obj = $data->payload;
                $payload_arr = get_object_vars($payload_obj);
                ksort($payload_arr);
                $querystring = "";
                foreach($payload_arr as $k => $v)
                { 
                    if(in_array($k, $include_params)){
                        $querystring = $querystring . $k . "=" . $v . "&"; 
                    }
                }
            }
        }
        $query_str = substr($querystring, 0, strlen($querystring) - 1);
        $query_str = strtolower($query_str);
        $hash2 = hash_hmac("sha256", $query_str, $uniquekey, true);
        $hash2_encoded = base64_encode($hash2);
        return $hash2_encoded;
    }
    
    function getGUID(){
        if (function_exists('com_create_guid')){
            $guid = com_create_guid();
            $guid = str_replace("{", "", $guid);
            $guid = str_replace("}", "", $guid);
            return $guid;
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);// .chr(125) //"}"
            return $uuid;
        }
    }
}
