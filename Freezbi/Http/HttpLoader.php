<?php
namespace Freezbi\Http;

class HttpLoader
{
    public static $UserAgents = array(
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
        "Opera/9.20 (Windows NT 6.0; U; en)",
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.50",
        "Mozilla/4.0 (compatible; MSIE 6.0; MSIE 5.5; Windows NT 5.1) Opera 7.02 [en]",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; fr; rv:1.7) Gecko/20040624 Firefox/0.9",
        "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48"
    );


    public static function getRandomUserAgent()
    {
        $random = rand(0, count(self::$UserAgents) - 1);
        return self::$UserAgents[$random];
    }

    public static function getRandomUserAgentInGivenList($userAgentsList)
    {
        $random = rand(0, count($userAgentsList) - 1);
        return $userAgentsList[$random];
    }


    public static function get($url, $randomAgents = true, $postData = null)
    {
        if ($url == null || $url == 'null' || $url == '') return '';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2); //only 2 redirects

        if ($postData != null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }

        //  curl_setopt($ch,CURLOPT_HEADER, false);
        if (is_array($randomAgents)) {
            curl_setopt($ch, CURLOPT_USERAGENT, self::getRandomUserAgentInGivenList($randomAgents));
        } elseif ($randomAgents) {
            curl_setopt($ch, CURLOPT_USERAGENT, self::getRandomUserAgent());
        }

        $output = curl_exec($ch);

        if ($output === false) {
            throw new HttpGetException(sprintf('Error %s: %s', curl_errno($ch), curl_error($ch)));
        }
        
        curl_close($ch);

        return $output;
    }
}
