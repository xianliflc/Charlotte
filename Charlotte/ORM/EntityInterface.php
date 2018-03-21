<?php

namespace Charlotte\ORM;

interface EntityInterface {

    public function isEqualTo(EntityInterface $en): bool;

    public function copy(): EntityInterface;

    public function save(): void;
}