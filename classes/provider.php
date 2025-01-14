<?php

/**
 * OAuth Provider
 *
 * @package    Kohana/OAuth
 * @category   Provider
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.0.7
 */

namespace OAuth;

abstract class Provider
{
    public $request_url = null;
    public $access_url = null;
    public $authorize_url = null;
    public $account_info_url = null;
    
    public static $_autoset = array(
        'request_url',
        'access_url',
        'authorize_url',
        'account_info_url',
    );
    
    public $consumer = null;


    /**
     * Create a new provider.
     *
     *     // Load the Twitter provider
     *     $provider = Provider::forge('twitter');
     *
     * @param   string   provider name
     * @param   array    provider options
     * @return  Provider
     */
    public static function forge($name, array $options = NULL)
    {
        $class = '\\OAuth\\Provider_' . \Inflector::classify($name);
        return new $class($options);
    }

    /**
     * @var  string  provider name
     */
    public $name;

    /**
     * @var  string  signature type
     */
    protected $signature = 'HMAC-SHA1';

    /**
     * @var  string  uid key name
     */
    public $uid_key = 'uid';

    /**
     * @var  array  additional request parameters to be used for remote requests
     */
    protected $params = array();

    /**
     * @var  string  scope separator, most use "," but some like Google are spaces
     */
    public $scope_seperator = ',';

    /**
     * Overloads default class properties from the options.
     *
     * Any of the provider options can be set here:
     *
     * Type      | Option        | Description                                    | Default Value
     * ----------|---------------|------------------------------------------------|-----------------
     * mixed     | signature     | Signature method name or object                | provider default
     *
     * @param   array   provider options
     * @return  void
     */
    public function __construct(array $options = NULL)
    {
        if (isset($options['signature'])) {
            // Set the signature method name or object
            $this->signature = $options['signature'];
        }
        
        if (!is_object($this->signature)) {
            // Convert the signature name into an object
            $this->signature = Signature::forge($this->signature);
        }
        
        foreach (static::$_autoset as $attr) {
            if (isset($options[$attr])) {
                $this->{$attr} = $options[$attr];
            }
        }

        if (!$this->name) {
            // Attempt to guess the name from the class name
            $this->name = strtolower(substr(get_class($this), strlen('Provider_')));
        }
        
        foreach (static::$_autoset as $attr) {
            if (isset($options[$attr])) {
                $this->{$attr} = $options[$attr];
            }
        }
        
        $key = \Fuel\Core\Config::get($this->name.'.app_key');
        $secret = \Fuel\Core\Config::get($this->name.'.app_secret');
        
        if ($key === null || $secret === null) {
            throw new \Fuel\Core\Fuel_Exception('Config '.$this->name.'.php either doesn\'t exist or doesn\'t contain app_key & app_secret');
        }
        
        $this->consumer = Consumer::forge(array(
            'key' => $key,
            'secret' => $secret,
            'callback' => \Fuel\Core\Uri::current(),
        ));

        
    }

    /**
     * Return the value of any protected class variable.
     *
     *     // Get the provider signature
     *     $signature = $provider->signature;
     *
     * @param   string  variable name
     * @return  mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Returns the request token URL for the provider.
     *
     *     $url = $provider->url_request_token();
     *
     * @return  string
     */
    abstract public function url_request_token();

    /**
     * Returns the authorization URL for the provider.
     *
     *     $url = $provider->url_authorize();
     *
     * @return  string
     */
    abstract public function url_authorize();

    /**
     * Returns the access token endpoint for the provider.
     *
     *     $url = $provider->url_access_token();
     *
     * @return  string
     */
    abstract public function url_access_token();

    /**
     * Returns basic information about the user.
     *
     *     $url = $provider->get_user_info();
     *
     * @return  string
     */
    abstract public function get_user_info(Token $token);

    /**
     * Ask for a request token from the OAuth provider.
     *
     *     $token = $provider->request_token($consumer);
     *
     * @param   Consumer  consumer
     * @param   array           additional request parameters
     * @return  Token_Request
     * @uses    Request_Token
     */
    public function request_token(array $params = NULL)
    {
        // Create a new GET request for a request token with the required parameters
        $request_url = $this->request_url !== null ? $this->request_url : $this->url_request_token();

        $request = Request::forge('token', 'GET', $request_url, array(
                    'oauth_consumer_key' => $this->consumer->key,
                    'oauth_callback' => $this->consumer->callback,
                    'scope' => is_array($this->consumer->scope) ? implode($this->scope_seperator, $this->consumer->scope) : $this->consumer->scope,
                ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        // Sign the request using only the consumer, no token is available yet
        $request->sign($this->signature, $this->consumer);

        // Create a response from the request
        $response = $request->execute();

        // Store this token somewhere useful
        return Token::forge('request', array(
                    'access_token' => $response->param('oauth_token'),
                    'secret' => $response->param('oauth_token_secret'),
                ));
    }

    /**
     * Get the authorization URL for the request token.
     *
     *     Response::redirect($provider->authorize_url($token));
     *
     * @param   Token_Request  token
     * @param   array                additional request parameters
     * @return  string
     */
    public function authorize_url(Token_Request $token, array $params = NULL)
    {
        // Create a new GET request for a request token with the required parameters
        $authorize_url = $this->authorize_url !== null ? $this->authorize_url : $this->url_authorize();
        $request = Request::forge('authorize', 'GET', $authorize_url, array(
                    'oauth_token' => $token->access_token,
                ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        return $request->as_url();
    }

    /**
     * Exchange the request token for an access token.
     *
     *     $token = $provider->access_token($consumer, $token);
     *
     * @param   Consumer       consumer
     * @param   Token_Request  token
     * @param   array                additional request parameters
     * @return  Token_Access
     */
    public function access_token(Token_Request $token, array $params = NULL)
    {
        // Create a new GET request for a request token with the required parameters
        $access_url = $this->access_url !== null ? $this->access_url : $this->url_access_token();
        $request = Request::forge('access', 'GET', $access_url, array(
                    'oauth_consumer_key' => $this->consumer->key,
                    'oauth_token' => $token->access_token,
                    'oauth_verifier' => $token->verifier,
                ));

        if ($params) {
            // Load user parameters
            $request->params($params);
        }

        // Sign the request using only the consumer, no token is available yet
        $request->sign($this->signature, $this->consumer, $token);

        // Create a response from the request
        $response = $request->execute();

        // Store this token somewhere useful
        return Token::forge('access', array(
                    'access_token' => $response->param('oauth_token'),
                    'secret' => $response->param('oauth_token_secret'),
                    'uid' => $response->param($this->uid_key) ? : \Input::get_post($this->uid_key),
                ));
    }
    
}

// End Provider