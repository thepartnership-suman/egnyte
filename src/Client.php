<?php

namespace Suman\Egnyte;

use GuzzleHttp\Client as GuzzleClient;

/**
 *  return the Egnyte bas url and Guzzle client to do operations
 */
class Client
{
    const EGNYTE_DOMAIN = 'egnyte.com';
    const EGNYTE_ENDPOINT = '/pubapi/v1';

    /**
     * @var string URL of Egnyte endpoint
     */
    public $base_url;

    /**
     * @var GuzzleClient Guzzle client with predefined auth header
     */
    public $guzzleClient;

    /**
     * @param string $domain Egnyte subdomain name provided by Egnyte
     * @param string $oauth_token OAUTH token for user
     */
    public function __construct(string $domain, string $oauth_token)
    {

        $this->base_url = 'https://' . $domain . '.' . self::EGNYTE_DOMAIN . self::EGNYTE_ENDPOINT;

        // set HTTP header with oAuth token
        $this->guzzleClient = new GuzzleClient(['headers' => ['Authorization' => 'Bearer ' . $oauth_token]]);

        return $this;
    }
}