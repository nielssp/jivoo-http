<?php
// Jivoo HTTP 
// Copyright (c) 2016 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * CSRF-token handling.
 */
class Token
{
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $token;

    /**
     * Construct token.
     *
     * @param string|null $token Token string or null to generate one randomly.
     * @param string $name Token name.
     */
    public function __construct($token = null, $name = 'request_token')
    {
        if (! isset($token)) {
            $token = self::generate();
        }
        $this->token = $token;
        $this->name = $name;
    }
    
    /**
     * @return string Token name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Whether or not the current request is has a valid token.
     *
     * @param \Psr\Http\Message\ServerRequestInterface Request.
     * @param string $key Optional key to test for existence in POST-data.
     * @return boolean True if valid, false otherwise.
     */
    public function hasValidData(\Psr\Http\Message\ServerRequestInterface $request, $key = null)
    {
        if (! in_array($request->getMethod(), ['POST', 'PATCH', 'PUT', 'DELETE'])) {
            return false;
        }
        if (! $this->check($request)) {
            return false;
        }
        $data = $request->getParsedBody();
        return ! isset($key) or isset($data[$key]);
    }
    
    /**
     * Check the request token of a request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request.
     * @return boolean True if valid, false otherwise.
     */
    public function check(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        if (! is_array($data)) {
            return false;
        }
        if (! isset($data[$this->name])) {
            return false;
        }
        return $this->token === $data[$this->name];
    }
    
    /**
     * Get HTML for hidden form input containing the current token.
     *
     * @return string HTML code for a hidden input.
     */
    public function toHtml()
    {
        return '<input type="hidden" name="' . $this->name . '" value="' . $this->token . '" />';
    }
    
    /**
     * @return string Current token as a string.
     */
    public function __toString()
    {
        return $this->token;
    }
    
    /**
     * Create a token from an {@see \ArrayAccess} object (e.g. a
     * {@see \Jivoo\Store\Document} or {@see Cookie\CookiePool}). Updates the
     * object when a new token is generated.
     *
     * @param \ArrayAccess $array Token container.
     * @param string $name Token name in container.
     * @return self New token.
     */
    public static function create(\ArrayAccess $array, $name = 'request_token')
    {
        if (! isset($array[$name])) {
            $array[$name] = self::generate();
        }
        return new self(strval($array[$name]), $name);
    }
    
    /**
     * Generate a random token string.
     *
     * @return string Token string.
     */
    public static function generate()
    {
        return \Jivoo\Binary::base64Encode(\Jivoo\Random::bytes(32), true);
    }
}
