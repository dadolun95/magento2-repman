<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Model;

use Dadolun\Repman\HTTP\Client\Curl;
use Magento\Framework\HTTP\ResponseFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class RestApiClient
 * @package Dadolun\Repman\Model
 */
class RestApiClient
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * RestApiClient constructor.
     * @param Curl $curl
     * @param ResponseFactory $responseFactory
     * @param Json $json
     */
    public function __construct(
        Curl $curl,
        ResponseFactory $responseFactory,
        Json $json
    )
    {
        $this->curl = $curl;
        $this->responseFactory = $responseFactory;
        $this->json = $json;
    }

    /**
     * @param string $key
     */
    public function authorizeClient($key) {
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("X-API-TOKEN", $key);
    }

    /**
     * @param null $page
     * @return string
     */
    public function getOrganization($page = null) {
        try {
            $uri = "https://app.repman.io/api/organization";
            if ($page) {
                $uri .= "?page=" . $page;
            }
            $this->curl->get($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $name
     * @return string
     */
    public function createOrganization($name) {
        try {
            $uri = "https://app.repman.io/api/organization";
            $params = [
                "name" => $name
            ];
            $this->curl->post($uri, $this->json->serialize($params));
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param null $page
     * @return string
     */
    public function getOrganizationPackages($organization, $page = null) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package";
            if ($page) {
                $uri .= "?page=" . $page;
            }
            $this->curl->get($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $url
     * @param $type
     * @param int $keepLastReleases
     * @return string
     */
    public function createOrganizationPackage($organization, $url, $type, $keepLastReleases = 0) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package";
            $params = [
                "repository" => $url,
                "type" => $type,
                "keepLastReleases" => $keepLastReleases
            ];
            $this->curl->post($uri, $this->json->serialize($params));
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $package
     * @param null $page
     * @return string
     */
    public function getOrganizationPackage($organization, $package, $page = null) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package/" . urlencode($package);
            if ($page) {
                $uri .= "?page=" . $page;
            }
            $this->curl->get($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $package
     * @return string
     */
    public function synchronizeOrganizationPackage($organization, $package) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package/" . urlencode($package);
            $this->curl->put($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $package
     * @return string
     */
    public function deleteOrganizationPackage($organization, $package) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package/" . urlencode($package);
            $this->curl->delete($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $package
     * @param $url
     * @param int $keepLastReleases
     * @param bool $enableSecurityScan
     * @return string
     */
    public function synchronizeAndUpdateOrganizationPackage($organization, $package, $url, $keepLastReleases = 0, $enableSecurityScan = false) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/package/" . urlencode($package);
            $params = [
                "url" => $url,
                "keepLastReleases" => $keepLastReleases,
                "enableSecurityScan" => $enableSecurityScan
            ];
            $this->curl->patch($uri, $this->json->serialize($params));
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param null $page
     * @return string
     */
    public function getOrganizationTokens($organization, $page = null) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/token";
            if ($page) {
                $uri .= "?page=" . $page;
            }
            $this->curl->get($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $name
     * @return mixed
     */
    public function createOrganizationToken($organization, $name) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/token";
            $params = [
                "name" => $name
            ];
            $this->curl->post($uri, $this->json->serialize($params));
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $token
     * @return string
     */
    public function regenerateOrganizationToken($organization, $token) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/token/" . urlencode($token);
            $this->curl->put($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

    /**
     * @param $organization
     * @param $token
     * @return string
     */
    public function deleteOrganizationToken($organization, $token) {
        try {
            $uri = "https://app.repman.io/api/organization/" . urlencode($organization) . "/token/" . urlencode($token);
            $this->curl->delete($uri);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $result = "{\"Error\": \"An Error Occurred calling repman API\"}";
        }
        return $result;
    }

}
