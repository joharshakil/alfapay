<?php


//Class Use for generate hash

class Alfapay_Redirection_AES_CBC_Encrypt  {

    protected $key;
    protected $iv;
    protected $data;
    protected $method;
    /**
     * Available OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
     *
     * @var type $options
     */
    protected $options = 0;
    /**
     *
     * @param type $data
     * @param type $key
     * @param type $blockSize
     * @param type $mode
     */
    function __construct($data = null, $key = null, $iv = null, $blockSize = null, $mode = 'CBC') {
        $this->setData($data);
        $this->setKey($key);
        $this->setIV($iv);
        $this->setMethode($blockSize, $mode);
    }
    /**
     *
     * @param type $data
     */
    public function setData($data) {
        $this->data = $data;
    }
    /**
     *
     * @param type $key
     */
    public function setKey($key) {
        $this->key = $key;
    }
    /**
     *
     * @param type $key
     */
    public function setIV($iv) {
        $this->iv = $iv;
    }
    /**
     * CBC 128 192 256
    CBC-HMAC-SHA1 128 256
    CBC-HMAC-SHA256 128 256
    CFB 128 192 256
    CFB1 128 192 256
    CFB8 128 192 256
    CTR 128 192 256
    ECB 128 192 256
    OFB 128 192 256
    XTS 128 256
     * @param type $blockSize
     * @param type $mode
     */
    public function setMethode($blockSize, $mode = 'CBC') {
        if($blockSize==192 && in_array('', array('CBC-HMAC-SHA1','CBC-HMAC-SHA256','XTS'))){
            $this->method=null;
            throw new Exception('Invlid block size and mode combination!');
        }
        $this->method = 'AES-' . $blockSize . '-' . $mode;
    }
    /**
     *
     * @return boolean
     */
    public function validateParams() {
        if ($this->data != null &&
            $this->method != null ) {
            return true;
        } else {
            return FALSE;
        }
    }
//it must be the same when you encrypt and decrypt
    protected function getIV() {
        if($this->iv){
            return $this->iv;
        }

        //return mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);
        return openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->method));
    }
    /**
     * @return type
     * @throws Exception
     */
    public function encrypt() {
        if ($this->validateParams()) {
            return trim(openssl_encrypt($this->data, $this->method, $this->key, $this->options,$this->getIV()));
        } else {
            throw new Exception('Invlid params!');
        }
    }
    /**
     *
     * @return type
     * @throws Exception
     */
    public function decrypt() {
        if ($this->validateParams()) {
            $ret=openssl_decrypt($this->data, $this->method, $this->key, $this->options,$this->getIV());

            return   trim($ret);
        } else {
            throw new Exception('Invlid params!');
        }
    }
}

//Alfapay parent class


class WC_AlfaPay_Payment_Gateway_Redirection extends WC_Payment_Gateway {


    function __construct() {

        // global ID
        $this->id = "alfapay_payment_gateways_redirection";
        // Show Title
        $this->method_title = __( "Alfapay payment gateway", 'alfapay-payment-gateway-redirection' );
        // Show Description
        $this->method_description = __( "Alfapay Payment Gateway Plug-in for WooCommerce", 'alfapay-payment-gateway-redirection' );
        // vertical tab title
        $this->title = __( "Alfapay Payment Gateway", 'alfapay-payment-gateway-redirection' );
        $this->default_icon = 'https://www.bankalfalah.com/wp-content/uploads/2017/03/favicon.png';
        $this->has_fields = true;
        // support default form with credit card
        $this->icon = $this->default_icon;
        // setting defines
        $this->init_form_fields();
        // load time variable setting
        $this->init_settings();
        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
            $this->$setting_key = $value;
        }
        // further check of SSL if you want
        add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );
        add_action('wp_enqueue_scripts', array( $this, 'add_static_alfapay_files'), 1);

        // Save settings
        if ( is_admin() ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    }

    // Here is the  End __construct()
    // administration fields for specific Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'    => __( 'Enable / Disable', 'alfapay-payment-gateway-redirection' ),
                'label'    => __( 'Enable this payment gateway', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'checkbox',
                'default'  => 'no',
            ),
            'title' => array(
                'title'    => __( 'Title', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'Payment title of checkout process.', 'alfapay-payment-gateway-redirection' ),
                'default'  => __( 'Credit card', 'alfapay-payment-gateway-redirection' ),
            ),
            'description' => array(
                'title'    => __( 'Description', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'textarea',
                'desc_tip'  => __( 'Payment title of checkout process.', 'alfapay-payment-gateway-redirection' ),
                'default'  => __( 'Successfully payment through credit card.', 'alfapay-payment-gateway-redirection' ),
                'css'    => 'max-width:450px;'
            ),
            'api_login' => array(
                'title'    => __( 'Merchant Hash Key', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Hash Login provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'key1' => array(
                'title'    => __( 'Key One', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Key One Login provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'key2' => array(
                'title'    => __( 'Key Two', 'alfapay-payment-gateway' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Key Two Login provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),

            'trans_key' => array(
                'title'    => __( 'Merchant Username', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Merchant Username Key provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'password_key' => array(
                'title'    => __( 'Merchant Password', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'password',
                'desc_tip'  => __( 'This is the Merchant Password Key provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'channel_key' => array(
                'title'    => __( 'Channel ID', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Channel ID provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'merchant_id' => array(
                'title'    => __( 'Merchant ID', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Merchant ID provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'store_id' => array(
                'title'    => __( 'Store ID', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Store ID provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),

            'smsotec' => array(
                'title'    => __( 'SMSOTEC', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the SMSOTEC provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'emailotec' => array(
                'title'    => __( 'EMAILOTEC', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the EMAILOTEC provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),
            'smsotp' => array(
                'title'    => __( 'SMSOTP', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the SMSOTP provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),

            'return_url' => array(
                'title'    => __( 'Return URL', 'alfapay-payment-gateway' ),
                'type'    => 'text',
                'desc_tip'  => __( 'This is the Return URL provided by Alfapay when you signed up for an account.', 'alfapay-payment-gateway-redirection' ),
            ),

            'environment' => array(
                'title'    => __( 'Alfapay Test Mode', 'alfapay-payment-gateway-redirection' ),
                'label'    => __( 'Enable Test Mode', 'alfapay-payment-gateway-redirection' ),
                'type'    => 'checkbox',
                'description' => __( 'This is the test mode of gateway.', 'alfapay-payment-gateway-redirection' ),
                'default'  => 'no',
            )
        );
    }

    //Add custom css and javascript

    function add_static_alfapay_files() {
        if(is_checkout()) {
            wp_register_script('alfapay-scripts', plugins_url('assets/js/alfapay_script.js', __FILE__ ), array('jQuery'), '1.0.0');
            wp_enqueue_script('alfapay-scripts');
            wp_register_style('safepay-style', plugins_url('assets/css/alfapay_style.css', __FILE__ ));
            wp_enqueue_style('safepay-style');
        }
    }

    //Call Api for response

    function callAPI($method, $url, $data){
        $curl = curl_init();

        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // EXECUTE:
        $result = curl_exec($curl);

        if(!$result){die("Connection Failure");}

        curl_close($curl);
        return $result;
    }


    // Response handled for payment gateway
    public function process_payment( $order_id )
    {
        global $woocommerce;
        $customer_order = new WC_Order($order_id);
        $payload = array(
            "HS_ChannelId" => $this->channel_key,
            "HS_MerchantId" => $this->merchant_id,
            "HS_StoreId" => $this->store_id,
            "HS_ReturnURL" => $this->return_url,
            "HS_MerchantHash" => $this->api_login,
            "HS_MerchantUsername" => $this->trans_key,
            "HS_MerchantPassword" => $this->password_key,
            "HS_TransactionReferenceNumber" => (string)$order_id,
        );
     
       
        $payload['key1'] = $this->key1;
        $payload['key2'] = $this->key2;
        $payload['return_url'] = $this->return_url;
        $payload['TransactionReferenceNumber'] = (string)$order_id;
        $payload['HS_IsRedirectionRequest'] = "1";
        $payload['TransactionAmount'] = $customer_order->order_total;
        $payload['TransactionTypeId']   = $_POST['payment_option'];
      
        setcookie('getcookieValues', serialize($payload), time()+3600, "/");
   
    
         $customer_order->add_order_note( __( 'Order placed and alfapay redirected methode.', 'alfapay-payment-gateway-redirection' ) );
        // update the status of the order should need be
       $customer_order->update_status( 'on-hold', __( 'Awaiting payment.', 'alfapay-payment-gateway-redirection' ) );
        // remember to empty the cart of the user
       WC()->cart->empty_cart();



        return array(
            'result' => 'success',
            'redirect' => 'https://homage.pk/checkout.php?s=1');
        exit();

    }

    //Options in checkout

    public function payment_fields(){
        ?>

        <select id="payment_option" name="payment_option" required aria-required="true">
            <option  value="1" disabled selected hidden> Alfa Payment Gateway </option>
            <option value="1"> Alfa Wallet </option>
            <option value="2"> Alfalah Account</option>
            <option value="3"> Credit Cart/Debit cards)  </option>
        </select>
        <?php

    }

// Validate fields
    public function validate_fields() {
        return true;
    }
    public function do_ssl_check() {
        if( $this->enabled == "yes" ) {
            if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
                echo "<div class=\"error1\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";
            }
        }
    }

}