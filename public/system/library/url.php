<?php

class Url
{
    private string $url;
    private string $ssl;
    private array $rewrite = [];

    public function __construct(string $url, string $ssl = '')
    {
        $this->url = $url;
        $this->ssl = $ssl;
    }

    /**
     * @param Object $rewrite
     */
    public function addRewrite(object $rewrite)
    {
        $this->rewrite[] = $rewrite;
    }

    public function link(string $route, $args = '', bool $secure = true, bool $isSitemap = false): string
    {
        if ($this->ssl && $secure) {
            $url = $this->ssl . 'index.php?route=' . $route;
        } else {
            $url = $this->url . 'index.php?route=' . $route;
        }

        if ($args) {
            if (is_array($args)) {
                $url .= '&amp;' . http_build_query($args);
            } else {
                $url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
            }
        }

        foreach ($this->rewrite as $rewrite) {
            $url = $rewrite->rewrite($url);
        }

        if ($isSitemap) {
            $url = str_replace('&', '&amp;', $url);
        }

        return html_entity_decode($url);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSsl(): string
    {
        return $this->ssl;
    }

}