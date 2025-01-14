<?php

/**
 * OAuth Token
 *
 * @package    Kohana/OAuth
 * @category   Token
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.0.7
 */

namespace OAuth;

abstract class Token
{

    /**
     * Create a new token object.
     *
     *     $token = Token::forge($name);
     *
     * @param   string  token type
     * @param   array   token options
     * @return  Token
     */
    public static function forge($name, array $options = NULL)
    {
        $class = '\\OAuth\\Token_' . \Inflector::classify($name);

        return new $class($options);
    }

    /**
     * @var  string  token type name: request, access
     */
    protected $name;

    /**
     * @var  string  token key
     */
    protected $access_token;

    /**
     * @var  string  token secret
     */
    protected $secret;

    /**
     * @var  string  uid
     */
    protected $uid;

    /**
     * Sets the token and secret values.
     *
     * @param   array   token options
     * @return  void
     */
    public function __construct(array $options = NULL)
    {
        if (!isset($options['access_token'])) {
            throw new \FuelException('Required option not passed: access_token');
        }

        if (!isset($options['secret'])) {
            throw new \FuelException('Required option not passed: secret');
        }

        $this->access_token = $options['access_token'];

        $this->secret = $options['secret'];

        // If we have a uid lets use it
        \Arr::get($options, 'uid') and $this->uid = $options['uid'];
    }

    /**
     * Return the value of any protected class variable.
     *
     *     // Get the token secret
     *     $secret = $token->secret;
     *
     * @param   string  variable name
     * @return  mixed
     */
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Return a boolean if the property is set
     *
     *     // Get the token secret
     *     if ($token->secret) exit('YAY SECRET');
     *
     * @param   string  variable name
     * @return  bool
     */
    public function __isset($key)
    {
        return isset($this->$key);
    }

    /**
     * Returns the token key.
     *
     * @return  string
     */
    public function __toString()
    {
        return (string) $this->access_token;
    }

}

// End Token
