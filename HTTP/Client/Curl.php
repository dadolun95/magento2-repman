<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\HTTP\Client;

/**
 * Class Curl
 * @package Dadolun\Repman\HTTP\Client
 */
class Curl extends \Magento\Framework\HTTP\Client\Curl implements \Magento\Framework\HTTP\ClientInterface
{
    /**
     * Make PUT request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @return void
     */
    public function put($uri)
    {
        $this->makeRequest("PUT", $uri);
    }

    /**
     * Make DELETE request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @return void
     */
    public function delete($uri)
    {
        $this->makeRequest("DELETE", $uri);
    }

    /**
     *  Make PATCH request
     *
     * @param string $uri uri relative to host, ex. "/index.php"
     * @param array $params
     */
    public function patch($uri, $params = [])
    {
        $this->curlOption(CURLOPT_POSTFIELDS, is_array($params) ? http_build_query($params) : $params);
        $this->makeRequest("PATCH", $uri);
    }
}
