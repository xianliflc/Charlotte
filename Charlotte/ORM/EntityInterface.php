<?php

namespace Charlotte\ORM;

interface EntityInterface {

    // /**
    //  * @param EntityInterface $en
    //  * @return bool
    //  */
    // public function compareTo(EntityInterface $en): int;

    /**
     * @return bool
     */
    public function isValid() : bool;

    public function use(array $data) : EntityInterface;

    public function save();
}