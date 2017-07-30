<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:47 PM
 */
use Charlotte\Exception\CException;

class IndexController extends Controller
{
    function index()
    {
        $cc = new CException();
        $this->assign('title', 'homepage');
        $this->assign('todo', 'welcome to charlotte!');
    }
}