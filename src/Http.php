<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Http;

/**
 * Provides functions related to redirects and HTTP status codes.
 */
class Http
{

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
  
    /**
     * Encode a query.
     * @param string[] $query Query array.
     * @param bool $associative If set to false the input
     * <code>array('value1', 'value2', 'value3')</code> will result in the output
     * string "value1&value2&value3", and any keys will be ignored. If set to true
     * (the default) the above array will result in the output
     * "0=value1&1=value2&2=value3" to match the format of PHP's global
     * {@see $_GET}-array.
     * @return string Query string without leading '?'.
     */
    public static function encodeQuery(array $query, $associative = true)
    {
        $queryString = array();
        foreach ($query as $key => $value) {
            if ($associative) {
                if ($key === '') {
                    continue;
                }
                if ($value === '') {
                    $queryString[] = urlencode($key);
                } else {
                    $queryString[] = urlencode($key) . '=' . urlencode($value);
                }
            } else {
                if ($value === '') {
                    continue;
                }
                $queryString[] = urlencode($value);
            }
        }
        return implode('&', $queryString);
    }
  
    /**
     * Decode a query string.
     * @param string $query Query string with or without leading '?'.
     * @param bool $associative If set to false the function expects the query
     * string to be of the form "value1&value2&value3" resulting in the output
     * <code>array('value1', 'value2', 'value3')</code> (any keys will be
     * ignored). If set to true (the default) the above string will result in the
     * output: <code>array('value1' => '', 'value2' => '', 'value3' => '')</code>
     * to match the format of PHP's global {@see $_GET}-array.
     * @return string[] Query array.
     */
    public static function decodeQuery($query, $associative = true)
    {
        if ($query == '' or $query == '?') {
            return array();
        }
        if ($query[0] == '?') {
            $query = substr($query, 1);
        }
        $queryString = explode('&', $query);
        $query = array();
        foreach ($queryString as $string) {
            if (strpos($string, '=') !== false) {
                list($key, $value) = explode('=', $string, 2);
                if ($key === '') {
                    continue;
                }
                if ($associative) {
                    $query[urldecode($key)] = urldecode($value);
                } else {
                    $query[] = urldecode($value);
                }
            } elseif ($string === '') {
                continue;
            } elseif ($associative) {
                $query[urldecode($string)] = '';
            } else {
                $query[] = urldecode($string);
            }
        }
        return $query;
    }

    /**
     * Format a date for use in HTTP headers.
     * @param int $timestamp Time stamp.
     * @return string Formatted date.
     */
    public static function date($timestamp)
    {
        return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
    }
}
