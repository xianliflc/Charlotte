<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:10 PM
 */
class Model extends Sql
{
    protected $_model;
    protected $_table;

    function __construct()
    {
        // connect to db
        $this->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // get name of model
        $this->_model = get_class($this);
        $this->_model = rtrim($this->_model, 'Model');

        // name of table is same to name of class
        $this->_table = strtolower($this->_model);
    }

    function __destruct()
    {
    }
}