<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:47 PM
 */
class ItemController extends Controller implements lib\Test
{
    // homepage, test db query
    public function index()
    {
        $items = (new ItemModel)->selectAll();
        $this->assign('title', 'search');
        $this->assign('items', $items);
    }

    // test db 'create'
    public function add()
    {
        $data['item_name'] = $_POST['value'];
        $count = (new ItemModel)->add($data);
        $this->assign('title', 'add');
        $this->assign('count', $count);
    }

    // test db 'read'
    public function view($id = null)
    {
        $item = (new ItemModel)->select($id);
        $this->assign('title', 'read' . $item['item_name']);
        $this->assign('item', $item);
    }

    // test db 'update'
    public function update()
    {
        $data = array('id' => $_POST['id'], 'item_name' => $_POST['value']);
        $count = (new ItemModel)->update($data['id'], $data);
        $this->assign('title', 'update');
        $this->assign('count', $count);
    }

    // test db 'delete'
    public function delete($id = null)
    {
        $count = (new ItemModel)->delete($id);
        $this->assign('title', 'delete');
        $this->assign('count', $count);
    }
    public function aaa(){}
}