<?php

/**
 * OAuth Access Request
 *
 * @package    Kohana/OAuth
 * @category   Request
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.0.7
 */

namespace OAuth;

class Request_Access extends Request
{

    protected $name = 'access';
    protected $required = array(
        'oauth_consumer_key' => TRUE,
        'oauth_token' => TRUE,
        'oauth_signature_method' => TRUE,
        'oauth_signature' => TRUE,
        'oauth_timestamp' => TRUE,
        'oauth_nonce' => TRUE,
        // 'oauth_verifier'         => TRUE,
        'oauth_version' => TRUE,
    );

    public function execute(array $options = NULL)
    {
        return Response::forge(parent::execute($options));
    }

}

// End Request_Access