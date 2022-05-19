<?php

namespace Db;

class Results {

    public $query;

    // Need for back OpenCart capability
    public $num_rows = false;
    public $row = false;
    public $rows = false;

    //TODO: add db type (mpdo, mssql, mysql, mysqli, postgree) for method checking in __destruct() and getRows()

    public function __construct($query)
    {
        $this->query = $query;

        if ($query !== false) {
            $this->num_rows = $this->getRowsCount();
            $this->row = $this->getRow();
            $this->rows = $this->getRows();
        }
    }

    public function __destruct()
    {
        if ($this->query instanceof \mysqli_result) {
//            if ($this->query->lengths != NULL)
//                $this->query->close();
//        } elseif () {
//
        }
    }

    public function getRowsCount(): int
    {
        if ($this->query instanceof \mysqli_result && $this->query->lengths != NULL) {
            return $this->query->num_rows;
        } else {
            return false;
        }
    }

    public function getRows(): array
    {
        $data = array();

        if ($this->query instanceof \mysqli_result && $this->query->lengths != NULL) {
            while ($row = $this->query->fetch_assoc()) {
                $data[] = $row;
            }
//        } elseif () {
//
        }

        return $data;
    }

    public function getRow(): array
    {
        return isset($this->rows[0]) ? $this->rows[0] : array();
    }

}