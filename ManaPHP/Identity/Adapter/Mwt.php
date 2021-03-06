<?php

namespace ManaPHP\Identity\Adapter;

use ManaPHP\Identity\ExpiredCredentialException;
use ManaPHP\Identity\InvalidCredentialException;
use ManaPHP\Identity\NotBeforeCredentialException;

/**
 * Class Mwt
 * @package ManaPHP\Identity\Adapter
 */
class Mwt extends Token
{
    /**
     * Mwt constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->_alg = isset($options['alg']) ? $options['alg'] : 'md5';
        $this->_key = isset($options['key']) ? (array)$options['key'] : [$this->crypt->getDerivedKey('mwt')];

        if (isset($options['ttl'])) {
            $this->_ttl = $options['ttl'];
        }
    }

    /**
     * @param array $claims
     *
     * @return string
     */
    public function encode($claims)
    {
        $claims['iat'] = time();
        $claims['exp'] = time() + $this->_ttl;

        $payload = $this->base64urlEncode(json_encode($claims, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $signature = $this->base64urlEncode(hash_hmac($this->_alg, $payload, $this->_key[0], true));

        return "$payload.$signature";
    }

    /**
     * @param string $token
     *
     * @return array
     */
    public function decode($token)
    {
        $parts = explode('.', $token, 5);
        if (count($parts) !== 2) {
            throw new InvalidCredentialException(['The MWT `:token` must have one dot', 'token' => $token]);
        }
        list($payload, $signature) = $parts;

        $success = false;
        /** @noinspection ForeachSourceInspection */
        foreach ($this->_key as $key) {
            if ($this->base64urlEncode(hash_hmac($this->_alg, $payload, $key, true)) === $signature) {
                $success = true;
                break;
            }
        }

        if (!$success) {
            throw new InvalidCredentialException(['signature is not corrected: :signature', 'signature' => $signature]);
        }

        $claims = json_decode($this->base64urlDecode($payload), true);
        if (!is_array($claims)) {
            throw new InvalidCredentialException('payload is not array.');
        }

        if (isset($claims['exp']) && time() > $claims['exp']) {
            throw new ExpiredCredentialException('token is expired.');
        }

        if (isset($claims['nbf']) && time() < $claims['nbf']) {
            throw new NotBeforeCredentialException('token is not active.');
        }

        return $claims;
    }

    /**
     * @param string $token
     *
     * @return array
     */
    public function decodeClaims($token)
    {
        $parts = explode('.', $token, 5);
        if (count($parts) !== 2) {
            throw new InvalidCredentialException(['The MWT `:token` must have one dot', 'token' => $token]);
        }
        list($payload,) = $parts;

        $claims = json_decode($this->base64urlDecode($payload), true);
        if (!is_array($claims)) {
            throw new InvalidCredentialException('payload is not array.');
        }

        return $claims;
    }
}