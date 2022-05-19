<?php

class Response
{
    private array $headers = [];
    private int $level = 0;
    private string $output;

    public function addHeader(string $header): void
    {
        $this->headers[] = $header;
    }

    public function addHeaderAppJson(): void
    {
        $this->addHeader('Content-Type: application/json');
    }

    public function setCode(int $code)
    {
        http_response_code($code);
    }

    public function getCode(int $code): int
    {
        return http_response_code($code);
    }

    public function redirect(string $url, int $status = 302): void
    {
        header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url), true, $status);
        exit();
    }

    public function setCompression(int $level)
    {
        $this->level = $level;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function setOutput(string $output)
    {
        $this->output = $output;
    }

    private function compress(string $data, int $level = 0): string
    {
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
            $encoding = 'gzip';
        }

        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
            $encoding = 'x-gzip';
        }

        if (!isset($encoding) || ($level < -1 || $level > 9)) {
            return $data;
        }

        if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
            return $data;
        }

        if (headers_sent()) {
            return $data;
        }

        if (connection_status()) {
            return $data;
        }

        $this->addHeader('Content-Encoding: ' . $encoding);

        return gzencode($data, (int)$level);
    }

    public function output()
    {
        if ($this->output) {
            $output = $this->level ? $this->compress($this->output, $this->level) : $this->output;

            if (!headers_sent()) {
                foreach ($this->headers as $header) {
                    header($header, true);
                }
            }

            echo $output;
        }
    }
}
