<?php

/**
 * OAuth Resource Request
 *
 * @package    Kohana/OAuth
 * @category   Request
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @since      3.0.7
 */

namespace OAuth;

class Request_Resource extends Request
{

    protected $name = 'resource';
    // http://oauth.net/core/1.0/#rfc.section.7
    protected $required = array(
        'oauth_consumer_key' => TRUE,
        'oauth_token' => TRUE,
        'oauth_signature_method' => TRUE,
        'oauth_signature' => TRUE,
        'oauth_timestamp' => TRUE,
        'oauth_nonce' => TRUE,
        'oauth_version' => TRUE,
    );

}

// End Request_Resource