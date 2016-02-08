<?php

/**
 * Default_Plugin_UrlTool Class:
 * A class to parse, validate, encode, and check url status.
 *
 * @version 1.1
 * @author Hossamzee (hossam_zee@yahoo.com).
 * @date 7 Aug 2012.
 */
class Default_Plugin_UrlTool {

    /**
     * Parses a url and gets the components of it.
     * 
     * @param string $url Url to be parsed.
     * @param string If there is an error, it then is filled in this variable (passed-by-reference).
     * @return mixed Array of components of the url if it is validated, or false.
     */
    public /* mixed */ function parseUrl($url, &$error = "") {

        /* Initialize the components array. */
        $components = array();

        /* Push url to components array. */
        $components["url"] = $url;

        /* Initialize variables. */
        $scheme = null;
        $ipversion = null;
        $authority = null;
        $hostRequest = null;
        $host = null;
        $port = null;
        $hostname = null;
        $request = null;
        $path = null;
        $querystring = null;
        $fragment = null;

        /* Get the scheme of the url. */
        if (preg_match("/^([A-Z][A-Z0-9\+\-\.]+):\/\//i", $url) > 0) {
            $colonDoubleSlashesPos = strpos($url, "://");
            $scheme = substr($url, 0, $colonDoubleSlashesPos);
            $hostRequest = substr($url, $colonDoubleSlashesPos + 3);
        } else {
            /* PREVIOUS: $scheme = null; */
            $hostRequest = $url;
        }

        /* Get the host and the request and split them apart. */
        $slashPos = strpos($hostRequest, '/');

        if ($slashPos !== false) {
            $host = substr($hostRequest, 0, $slashPos);
            $request = substr($hostRequest, $slashPos + 1);
        } else {
            $host = $hostRequest;
            $request = null;
        }

        /* Get authority from host. */
        $atPos = strpos($host, '@');

        if ($atPos !== false) {
            $authority = substr($host, 0, $atPos);
            $host = substr($host, $atPos + 1);
        } else {
            $authority = null;
        }

        /* If the ip-version (of the host) is IPv6. */
        if ($host{0} == '[') {
            $squareBracketColonPos = strpos($host, "]:");

            if ($squareBracketColonPos !== false) {
                $hostname = substr($host, 0, $squareBracketColonPos + 1);
                $port = substr($host, $squareBracketColonPos + 2);
            } else {
                $hostname = $host;
                $port = null;
            }

            /* Set the ip version to 6. */
            $ipversion = 6;
        }

        /* If the ip-version is IPv4. */ else {
            $colonPos = strpos($host, ':');

            if ($colonPos !== false) {
                $hostname = substr($host, 0, $colonPos);
                $port = substr($host, $colonPos + 1);
            } else {
                $hostname = $host;
                $port = null;
            }

            /* Set the ip version to be 4. */
            $ipversion = 4;
        }

        /* Strip dot from hostname. */
        if ($hostname{strlen($hostname) - 1} == '.') {
            $hostname = substr($hostname, 0, -1);
        }

        /* Set the path to be request, initially. */
        $path = $request;

        /* Get the fragment of the url. */
        $hashPos = strpos($path, '#');

        if ($hashPos !== false) {
            $fragment = substr($path, $hashPos + 1);
            $path = substr($path, 0, $hashPos);
        }

        /* Get the query string of the url. */
        $questionMarkPos = strpos($path, '?');

        if ($questionMarkPos !== false) {
            $querystring = substr($path, $questionMarkPos + 1);
            $path = substr($path, 0, $questionMarkPos);
        }

        /* Push results to components. */
        $components["scheme"] = $scheme;
        $components["ipversion"] = $ipversion;
        $components["authority"] = $authority;
        $components["port"] = $port;
        $components["hostname"] = $hostname;
        $components["request"] = $request;
        $components["path"] = $path;
        $components["querystring"] = $querystring;
        $components["fragment"] = $fragment;

        /* Validate the url components. */
        if ($this->validateUrlComponents($components, $error) === false) {
            /* If the url is not valid. */
            return false;
        } else {
            /* If the url is valid. */
            return $components;
        }
    }

    /**
     * Validates url components.
     * 
     * @param array Components of the url (passed-by-reference).
     * @param string If there is an error, it then is filled in this variable (passed-by-reference).
     * @return bool True if the url components are valid, false otherwise.
     */
    private /* bool */ function validateUrlComponents(&$components = array(), &$error = "") {
        /* Validate the scheme of the url. */
        if ($components["scheme"] != null) {
            if (preg_match("/([A-Z][A-Z0-9\+\-\.]+)/i", $components["scheme"]) == 0) {
                /* If the scheme did not match the pattern. */
                $error = "The scheme did not match the pattern ({$components["scheme"]}).";
                return false;
            }
        } else {
            /* If the scheme is empty. */
            $components["scheme"] = "http";
        }

        /* Validate the port if there is any. */
        if ($components["port"] != null) {
            if (!is_numeric($components["port"])) {
                /* If the port is not a number. */
                $error = "The port is not a number ({$components["port"]}).";
                return false;
            }
        } else {
            //$components["port"] = getservbyname($components["scheme"], "tcp");
        }

        /* Validate the hostname. */
        if ($components["hostname"] == "") {
            /* If the hostname is empty (mandatory variable). */
            $error = "The hostname is empty (mandatory variable).";
            return false;
        }

        /* Validate the . */
        if ($components["authority"] != null && $components["authority"] != "") {
            preg_match("/((%[0-9A-F]{2})|([0-9A-Z|'~!$&*()_+=;:.,-]))*/i", $components["authority"], $authorityMatches);

            /* Check the difference between the two strings. */
            $authorityDiff = str_replace($authorityMatches[0], '', $components["authority"]);

            if ($authorityDiff != "") {
                $wrongSymbol = $authorityDiff{0};
                $error = "Wrong symbol used in authority ($wrongSymbol).";
                return false;
            }
        }

        if ($components["hostname"] !== 'localhost') {
            /* Split the domain parts. */
            $domain_parts = explode(".", $components["hostname"]);

            /* If the host name is like (.com, .net) */
            if ($domain_parts[0] == "" || $domain_parts[1] == "") {
                $error = "The hostname does not look like hostname.";
                return false;
            }
        }

        /* Validate that the hostname is ipv6. */
        if ($components["ipversion"] == 6) {
            $hostnameWithoutSquareBrackets = substr($components["hostname"], 1, -1);

            /* Validate the syntax of ip version future. */
            if (preg_match("/v[0-9A-F]+\.[A-Z0-9\-\.\_\~\!\$\&\'\(\)\*\+\,\;\=]+/i", $hostnameWithoutSquareBrackets)) {
                /* If the ipvfuture is value, e.g. . */
                $components["ipversion"] = "future";
                return true;
            }

            /* Validate the syntax of ipv6. */
            /* Source: http://crisp.tweakblogs.net/blog/2031 */
            if (preg_match("/^(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4})*|[a-f0-9]{1,4}(?::[a-f0-9]{1,4})*::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4})*)?
            |::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4})*)?)(?::\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})?$/ix", $hostnameWithoutSquareBrackets, $match) > 0) {
                /* If the ipv6 is valid, e.g. http://[fe80:0:0:0:202:b3ff:fe1e:8329]. */
                return true;
            } else {
                /* If the hostname is not valid as an ipv6. */
                $error = "The hostname is not valid as an ipv6 ({$hostnameWithoutSquareBrackets}).";
                return false;
            }
        }

        /* Validate that the hostname is ipv4. */
        if ($components["ipversion"] == 4) {
            if (strpos($components["hostname"], '.') !== false) {
                /* Validate that the hostname is an ip. */
                if (preg_match("/^\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b$/", $components["hostname"]) > 0) {
                    /* If the hostname is a valid ip address. */
                    return true;
                } else {
                    /* It might be a regular hostname. */
                    if (preg_match("/[\:\/\?\#\[\]\@\s]+/", $components["hostname"])) {
                        $error = "The hostname is not valid.";
                        return false;
                    } else {
                        /* If the hostname without TLD is valid. */
                        $components["ipversion"] = "reg-name";
                        return true;
                    }
                }
            } else {
                if ($components["hostname"] !== 'localhost') {
                    /* If the hostname did not contain a dot '.'. */
                    $error = "The hostname did not contain a dot ({$components["hostname"]}).";
                    return false;
                }
            }
        }
    }

    /**
     * Checks if the url exists or not (not-in-use).
     *
     * @param string Url to be checked.
     * @param float Time taken to response (passed-by-reference).
     * @return bool True if the url exists, false otherwise.
     */
    public /* bool */ function checkUrl($url, &$responseTime) {
        /* Set the request method to be head. */
        stream_context_set_default(array("http" => array("method" => "HEAD", "max_redirects" => 1)));

        /* Set start time. */
        $startTime = array_sum(explode(" ", microtime()));

        /* Send a head request. */
        $headers = get_headers($url);

        /* Set finish time. */
        $finishTime = array_sum(explode(" ", microtime()));

        /* Get HTTP response code. */
        preg_match("/HTTP\/\d\.\d (\d{3})/i", $headers[0], $responseArray);

        /* Set the response time. */
        $responseTime = $finishTime - $startTime;

        /* Return true, if the url is not 404, else, otherwise. */
        return ($responseArray[1] != 404);
    }

    /**
     * Encodes a normal domain name (Unicode/UTF-8) to Punycode (to-do).
     * @param string Domain name (UTF-8).
     * @return string Punycode of the domain.
     */
    public /* string */ function domainToPunycode($domain) {
        return "";
    }

    /**
     * Normalize URL to be in this format: scheme://[authority@]hostname[:port]/[request]
     * @param array URL components.
     * @return string Normalized URL.
     */
    public function normalizeUrl($urlComponents) {
        // Scheme
        $normalizedUrl = $urlComponents["scheme"] . "://";

        // Authority?
        $normalizedUrl .= ($urlComponents["authority"] != null && $urlComponents["authority"] != "") ? $urlComponents["authority"] . "@" : "";

        // Hostname
        $normalizedUrl .= $urlComponents["hostname"];

        // Port
        $normalizedUrl .= ($urlComponents["port"] != null && $urlComponents["port"] != "") ? ":" . $urlComponents["port"] : "";

        // Request
        $normalizedUrl .= "/" . $urlComponents["request"];

        return $normalizedUrl;
    }

}
