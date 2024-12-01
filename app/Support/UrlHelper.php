<?php

namespace App\Support;

/**
 * A support class made to help with URLs
 */
class UrlHelper
{
    /**
     * Validate if param is a valid URL;
     *
     * @param  string  $url
     * @return bool
     */
    public static function isValidUrl(string $url): bool
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Get the query parameters from a URL
     *
     * @param  string  $url
     * @return array
     */
    public static function getQueryParams(string $url): array
    {
        $parsedUrl = parse_url($url);
        if (! isset($parsedUrl['query'])) {
            return [];
        }

        parse_str($parsedUrl['query'], $queryParams);
        return $queryParams;
    }

    /**
     * Modifies the query strings in a given URL.
     * This is a method adapted from @imliam (github) https://gist.github.com/ImLiam/49c420ddb2db881afd59d77635d039f8
     *
     * @param  string  $url  URL to use parse. If none is supplied, the current URL of the page load will be used
     * @param  array  $queries  Indexed array of query parameters
     * @return string The updated query string
     */
    public static function mergeQueries(string $url, array $queries): string
    {
        // Split the URL down into an array with all the parts separated out
        $urlParsed = parse_url($url);

        // Merge the existing URL's query parameters with our new ones
        $urlParams = array_merge(static::getQueryParams($url), $queries);

        // Build a new query string from our updated array
        $stringQuery = http_build_query($urlParams);

        // Add the new query string back into our URL
        $urlParsed['query'] = $stringQuery;

        // Build the array back into a complete URL string
        $url = static::buildUrl($urlParsed);
        return rtrim($url, '?');
    }

    /**
     * Rebuilds the URL parameters into a string from the native parse_url() function.
     * This is a method adapted from @imliam (github) https://gist.github.com/ImLiam/49c420ddb2db881afd59d77635d039f8
     *
     * @param  array  $parts  The parts of a URL
     * @return string  The constructed URL
     */
    public static function buildUrl(array $parts): string
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }
}
