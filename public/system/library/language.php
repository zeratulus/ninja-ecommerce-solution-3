<?php

class Language
{
    private string $default = 'en-gb';
    private string $directory;
    public array $data = [];

    public function __construct(string $directory = '')
    {
        $this->directory = $directory;
    }

    /**
     * @param string $key
     * @return mixed|string
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? $key;
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function load(string $filename, string $key = ''): array
    {
        if (!$key) {
            $_ = array();

            $file = DIR_LANGUAGE . $this->default . '/' . $filename . '.php';

            if (is_file($file)) {
                require($file);
            }

            $file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';

            if (is_file($file)) {
                require($file);
            }

            $this->data = array_merge($this->data, $_);
        } else {
            // Put the language into a sub key
            $this->data[$key] = new Language($this->directory);
            $this->data[$key]->load($filename);
        }

        return $this->data;
    }

}