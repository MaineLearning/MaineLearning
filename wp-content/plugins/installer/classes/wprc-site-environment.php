<?php
class WPRC_SiteEnvironment
{
    public static function checkSslProvidingByUrl($url)
    {
        $url = trim($url);
        if(preg_match('/^https:\/\/.*/', $url))
        {
            return false;
            //return self::checkSslProviding();
        }

        return true;
    }

    public static function checkSslProviding()
    {
        $version = curl_version();
        $ssl_supported = ($version['features'] & CURL_VERSION_SSL);

        return $ssl_supported;
    }

}
