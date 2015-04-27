<?php

namespace Semantria;

use Guzzle\Common\Event,
    Guzzle\Common\Collection,
    Guzzle\Http\Message\Request;

use Semantria\SemantriaAbstractClient;

class SemantriaAuthClient extends SemantriaAbstractClient
{
    public static $consumer_key;
    public static $application_name;

    private static $consumer_secret;
    private static $oAuthVersion = '1.0';

    private static $required = [
        'consumer_key',
        'consumer_secret',
        'application_name',
        'use_compression',
        'service_description',
    ];

    private static $keys = [
        'OAuthParameterPrefix'      => 'oauth_',
        'OAuthConsumerKeyKey'       => 'oauth_consumer_key',
        'OAuthVersionKey'           => 'oauth_version',
        'OAuthSignatureMethodKey'   => 'oauth_signature_method',
        'OAuthSignatureKey'         => 'oauth_signature',
        'OAuthTimestampKey'         => 'oauth_timestamp',
        'OAuthNonceKey'             => 'oauth_nonce',
    ];

    public static function factory($config = [])
    {
        $client = new self;
        $config = Collection::fromConfig($config, $client->getDefaultConfig(), static::$required);

        $client->configure($config);
        $client->setUserAgent(self::USER_AGENT, true);

        self::$consumer_key = $config->get('consumer_key');
        self::$consumer_secret = $config->get('consumer_secret');
        self::$application_name = $config->get('application_name');

        // add a listener to alter every requests and authenticate them through Semantria weird oAuth
        $client->getEventDispatcher()->addListener(
            'command.before_send',
            function (Event $event) use ($client) {
                $command = $event['command'];
                $request = $client->oAuthRequest($command->getRequest());
            }
        );

        return $client;
    }

    // Alter Guzzle Request to compute and add Semantria needed oAuth headers on the fly
    public function oAuthRequest(Request $request)
    {
        $nonce = uniqid('');
        $timestamp = time();

        $this->addQuery($request, $timestamp, $nonce);
        $authorization = $this->generateAuthHeader($request, $timestamp, $nonce);

        $request->setHeader('Authorization', $authorization);
        $request->setHeader('x-app-name', self::$application_name);

        return $request;
    }

    private function addQuery(Request $request, $timestamp, $nonce)
    {
        $query = $request->getQuery();
        $query->add(self::$keys['OAuthVersionKey'], self::$oAuthVersion);
        $query->add(self::$keys['OAuthTimestampKey'], $timestamp);
        $query->add(self::$keys['OAuthNonceKey'], $nonce);
        $query->add(self::$keys['OAuthSignatureMethodKey'], 'HMAC-SHA1');
        $query->add(self::$keys['OAuthConsumerKeyKey'], self::$consumer_key);
    }

    private function generateAuthHeader(Request $request, $timestamp, $nonce)
    {
        $url = $request->getUrl();
        $hash = $this->getSHA1(md5(self::$consumer_secret), $this->urlencode($url));

        $headers = ['OAuth realm' => ''];
        $headers[self::$keys['OAuthVersionKey']] = self::$oAuthVersion;
        $headers[self::$keys['OAuthTimestampKey']] = $timestamp;
        $headers[self::$keys['OAuthNonceKey']] = $nonce;
        $headers[self::$keys['OAuthSignatureMethodKey']] = 'HMAC-SHA1';
        $headers[self::$keys['OAuthConsumerKeyKey']] = self::$consumer_key;
        $headers[self::$keys['OAuthSignatureKey']] = $hash;

        $h = [];
        foreach ($headers as $name => $value) {
            $h[] = $name . '="' . $value . '"';
        }

        return implode(',', $h);
    }

    protected function urlencode($string)
    {
        if (false === $string) {
            return $string;
        }

        return str_replace('%7E', '~', rawurlencode($string));
    }

    protected function getSHA1($key, $query)
    {
        if (function_exists('hash_hmac')) {
            return $this->urlencode(base64_encode(hash_hmac('sha1', $query, $key, true)));
        }

        $blocksize  = 64;
        $hashfunc   = 'sha1';

        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }

        $key    = str_pad($key,$blocksize,chr(0x00));
        $ipad   = str_repeat(chr(0x36),$blocksize);
        $opad   = str_repeat(chr(0x5c),$blocksize);
        $hmac   = pack(
            'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$query
                            )
                        )
                    )
                );

        return $this->urlencode(base64_encode($hmac));
    }
}
