<?php

namespace Charlotte\ORM;

interface MapperInterface {

    // public function commit();

    // public function persist();

    public function clearCache();

    // public function find(...$params);

    public function useConnection(\PDO $conn);

    public function useDatabase(string $db);

    public function useTable(string $table);
}