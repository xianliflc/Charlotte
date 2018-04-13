<?php 

namespace Charlotte\ApiComponents\DocGenerator;

use Charlotte\CDoc\Doc;

class ApiDoc Extends Doc {

    public function render($dependencies, $template) {
        // TODO: Doc: render doc in frontend with templates
    }

    public function exportTo($format, $options) {
        // TODO: export doc to with certain format and store in designed folder
    }

    public function refine() {
        parent::refine();
        $new_data = array();
        foreach ($this->data as $group => $group_data) {

            $new_data[$group] = array('group'=>array(), 'endpoints' => array());

            foreach($group_data as $class) {
                $new_data[$group]['group'] = array_merge($new_data[$group]['group'], $class['class']['comment']['group']);
                foreach($class['methods'] as $method) {
                    if (array_key_exists('RequestUrl', $method['comment'])) {
                        foreach($method['comment']['RequestUrl'] as $url) {
                            $new_data[$group]['endpoints'][$url] = $method['comment'];
                            $new_data[$group]['endpoints'][$url]['RequestUrl'] = $url;
                        }
                        
                    }
                }
            }

        }
        $this->data = $new_data;
    }
}