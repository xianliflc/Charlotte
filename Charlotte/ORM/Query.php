<?php
namespace Charlotte\ORM;

class Query {

    // current query
    private $query;

    // cached queries
    private $cache;

    // the pointer of the cache
    private $pointer;

    public function __construct(string $query = '')
    {
        if ($query !== '') {
            $this->query = $query;
            $this->pointer = 0;
        } else {
            $this->pointer = -1;
        }

        $this->query = $query;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function addQuery(string $query = '') {
        if ($query !== '') {
            $this->query = $query;
            $this->cache();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function loadQuery() {
        if ($this->size() > 0 && $this->pointer < $this->size() - 1) {
            $this->query = $this->cache[$this->pointer];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
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
            $this->cache[] = $this->query;
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
            $this->query = $this->cache[$this->pointer];
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
            $this->query = $this->cache[$this->pointer];
        }

        return $this;
    }


}