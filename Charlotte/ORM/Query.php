<?php
namespace Charlotte\ORM;

class Query {

    // current query
    private $query;

    private $bindings;

    // cached queries
    private $cache;

    // the pointer of the cache
    private $pointer;

    public function __construct(string $query = '', array $bindings = array())
    {
        $this->reset($query);
    }

    public function reset(string $query = '', array $bindings = array()) {
        $this->cache = array();
        if ($query !== '') {
            $this->query = $query;
            $this->bindings = $bindings;
            $this->pointer = 0;
        } else {
            $this->pointer = -1;
        }

        // $this->query = $query;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function addQuery(string $query = '', array $bindings = array()) {
        if ($query !== '') {
            $this->query = $query;
            $this->bindings = $bindings;
            $this->cache();
        }
        return $this;
    }

    /**
     * @param array $queries
     * @return $this
     */
    public function addQueries(array $queries = array()) {
        foreach($queries as $value) {
            $this->addQuery($value[0], isset($value[1])? $value[1] : array());
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function loadQuery() {
        if ($this->size() > 0 && $this->pointer < $this->size() - 1) {
            $this->query = $this->cache[$this->pointer][0];
            $this->bindings = $this->cache[$this->pointer][1];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    public function getBindings() {
        return $this->bindings;
    }

    /**
     * @return int
     */
    public function size() {
        return count($this->cache);
    }

    /**
     * @return $this
     */
    public function cache() {
        if ($this->query !== '') {
            $this->cache[] = array($this->query, $this->bindings);
            $this->resetPointer();
        }
        return $this;
    }

    /**
     *
     */
    public function resetPointer() {
        if ($this->size() > 0 ) {
            $this->pointer = $this->size() - 1;
        } else {
            $this->pointer = -1;
        }
    }

    /**
     * @return $this
     */
    public function prev() {
        if ($this->pointer > 0 ) {
            $this->pointer--;
        }
        if ($this->size() > 0 && $this->pointer < $this->size() - 1) {
            $this->query = $this->cache[$this->pointer][0];
            $this->bindings = $this->cache[$this->pointer][1];
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function next() {
        if ($this->pointer < $this->size() - 1 ) {
            $this->pointer++;
        }
        if ($this->size() > 0 && $this->pointer < $this->size() - 1) {
            $this->query = $this->cache[$this->pointer][0];
            $this->bindings = $this->cache[$this->pointer][1];
        }

        return $this;
    }


}