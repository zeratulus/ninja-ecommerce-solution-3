<?php

class Template
{
    private $adaptor;

    public function __construct(string $adaptor, $debugbar = null)
    {
        $class = 'Template\\' . $adaptor;

        if (class_exists($class)) {
            if (!is_null($debugbar)) {
                $this->adaptor = new $class($debugbar);
            } else {
                $this->adaptor = new $class();
            }
        } else {
            throw new \Exception('Error: Could not load template adaptor ' . $adaptor . '!');
        }
    }

    public function set(string $key, $value)
    {
        $this->adaptor->set($key, $value);
    }

    public function render(string $template, bool $cache = false): string
    {
        return $this->adaptor->render($template, $cache);
    }

}
