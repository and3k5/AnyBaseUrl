<?php

namespace And3k5\AnyBaseUrl\Plugin;

use Magento\Framework\Url;

/**
 * Class UrlPlugin
 * @package And3k5\AnyBaseUrl\Plugin
 */
class UrlPlugin
{
    /**
     * @param Url      $subject
     * @param callable $proceed
     * @param array    $params
     *
     * @return string
     */
    public function aroundGetBaseUrl(Url $subject, callable $proceed, $params = [])
    {
        $result = $proceed($params);

        if (PHP_SAPI === "cli")
            return $result;

        $urlObj = parse_url($result);

        $urlObj["host"] = $_SERVER["HTTP_HOST"];
        $urlObj["port"] = $_SERVER["SERVER_PORT"];

        $result = $this->buildUrl($urlObj);

        return $result;
    }

    /**
     * This is my own implementation of the method http_build_url.
     * Some environments might not have the pecl_http installed therefore the method might be undefined.
     *
     * @param array $urlObj A query array returned from parse_url
     *
     * @return string Compiled url by the parts
     */
    protected function buildUrl($urlObj): string
    {
        $result = $urlObj["scheme"] . "://";
        if (!empty($urlObj["pass"]) || !empty($urlObj["user"])) {
            $result .= $urlObj["user"] . ":" . $urlObj["pass"] . "@";
        }
        $result .= $urlObj["host"];
        if (!($urlObj["scheme"] == "http" && $urlObj["port"] == 80 || $urlObj["scheme"] == "https" && $urlObj["port"] == 443)) {
            $result .= ":" . $urlObj["port"];
        }
        $result .= $urlObj["path"];

        if (!empty($urlObj["query"])) {
            $result .= "?" . $urlObj["query"];
        }
        if (!empty($urlObj["fragment"])) {
            $result .= "#" . $urlObj["fragment"];
        }
        return $result;
    }
}