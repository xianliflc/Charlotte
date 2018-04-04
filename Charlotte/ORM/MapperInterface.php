<?php

namespace Charlotte\ORM;

interface MapperInterface {

    public function commit();

    public function persist();

    /**
     * @return mixed
     */
    public function clearCache();

    // public function find(...$params);

    /**
     * @param DalAdapter $adapter
     * @return mixed
     */
    public function useAdapter(DalAdapter $adapter);

    /**
     * @param string $db
     * @return mixed
     */
    public function useDatabase(string $db);

    /**
     * @param string $table
     * @return mixed
     */
    public function useTable(string $table);

}