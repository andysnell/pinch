<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Uri;

/**
 * Struct for a URL parsed by the built-in parse_url() function
 * without the extra stuff required to implement the PSR-7 UriInterface
 * Example Usage:
 *    $parsed = new ParsedUrlStruct(...\parse_url($url) ?: []);
 */
class ParsedUrl implements \Stringable
{
    public function __construct(
        public string|null $scheme = null,
        public string|null $host = null,
        public int|null $port = null,
        public string|null $user = null,
        public string|null $pass = null,
        public string|null $path = null,
        public string|null $query = null,
        public string|null $fragment = null,
    ) {
    }

    public function __toString(): string
    {
        $out = '';
        if ($this->scheme) {
            $out .= $this->scheme . '://';
        }

        if ($this->user) {
            $out .= $this->user;
            if ($this->pass) {
                $out .= ':' . $this->pass;
            }
            $out .= '@';
        }

        if ($this->host) {
            $out .= $this->host;

            if ($this->port) {
                $out .= ':' . $this->port;
            }
        }

        if ($this->path) {
            $out .= $this->path;
        }

        if ($this->query) {
            $out .= '?' . $this->query;
        }

        if ($this->fragment) {
            $out .= '#' . $this->fragment;
        }

        return $out;
    }
}
