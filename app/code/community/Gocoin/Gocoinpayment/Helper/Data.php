<?php

class Gocoin_Gocoinpayment_Helper_Data extends Mage_Payment_Helper_Data
{
    const CLIENT_ID      = 'payment/Gocoinpayment/client_id';
    const CLIENT_SECRET  = 'payment/Gocoinpayment/client_secret';
    const ACCESS_TOKEN   = 'payment/Gocoinpayment/access_token';
    
    function createClient() {
        $storeId = Mage::app()->getStore()->getId();
        
        $client_id = Mage::getStoreConfig(self::CLIENT_ID);
        $client_secret = Mage::getStoreConfig(self::CLIENT_SECRET);
        
        include Mage::getBaseDir('lib').'/gocoin/src/client.php';
        
        $client = new Client( array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => "user_read_write+merchant_read_write+invoice_read_write",
        ));
        
        $access_token = Mage::getStoreConfig(self::ACCESS_TOKEN);
        if ($access_token != '') {
            $client->setToken($access_token);
        }
        
        return $client;
    }

    function createInvoice($orderId, $price, $options = array()) {
        $client = $this->createClient();
        // data for invoice creation
        $my_data = array (
            "price_currency" => "BTC",
            "base_price" => $price,
            "base_price_currency" => "USD",//$options['currency'],
            "confirmations_required" => 6,
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

        $data_string = json_encode($my_data);

        $user = $client->api->user->self();
        if (!$user) {
            return array('error' => $client->getError());
        }
        // stick merchant id into params for invoice creation
        $invoice_params = array(
            'id' => $user->merchant_id,
            'data' => $data_string
        );
            
        if (!$invoice_params) {
            $response = new stdClass();
            $response->error = $client->getError();
            return $response;
        }

        $response = $client->api->invoices->create($invoice_params);
        return $response;
    }
    
    function getAccessToken() {
        $client = $this->createClient();
        $b_auth = $client->authorize_api();
        $result = array();
        if ($b_auth) {
            $result['success'] = true;
            $result['data'] = $client->getToken();
        } else {
            $result['success'] = false;
            $result['data'] = $client->getError();
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
        
        if (!$client) {
            $response = new stdClass();
            $response->error = $client->getError();
            return $response;
        }
        
        $response = $client->api->invoices->get($invoiceId);

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
}
