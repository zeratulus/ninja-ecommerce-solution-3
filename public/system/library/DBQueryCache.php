<?php

class DBQueryCache
{

    private array $queries = [];

    public function add(string $sql, $resutls): void
    {
        $this->queries[$sql] = $resutls;
    }

    public function has(string $sql): bool
    {
        return isset($this->queries[$sql]);
    }

    /**
     * @param string $sql
     * @return false|mixed
     */
    public function get(string $sql)
    {
        if ($this->has($sql))
            return $this->queries[$sql];

        return false;
    }

}