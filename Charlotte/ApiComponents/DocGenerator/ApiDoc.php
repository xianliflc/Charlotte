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
                if (!array_key_exists('methods', $class)) {
                    continue;
                }
                foreach($class['methods'] as $method) {

                    if (!array_key_exists('RequestUrl', $method['comment']) && array_key_exists('RequestName', $method['comment']) ) {
                        $method['comment']['RequestUrl'] = $method['comment']['RequestName'];
                    }

                    if (array_key_exists('RequestUrl', $method['comment'])) {
                        foreach($method['comment']['RequestUrl'] as $url) {
                            $new_data[$group]['endpoints'][$url] = $method['comment'];
                            $new_data[$group]['endpoints'][$url]['RequestUrl'] = $url;
                        }

                        if (array_key_exists('RequestExample', $new_data[$group]['endpoints'][$url])) {
                            foreach($new_data[$group]['endpoints'][$url]['RequestExample'] as $key => $item) {
                                if (in_array($item['datatype'], ['json', 'array', 'object', 'xml'])){
                                    $new_data[$group]['endpoints'][$url]['RequestExample'][$key]['value'] = 
                                        str_replace("'", '"', $new_data[$group]['endpoints'][$url]['RequestExample'][$key]['value']);
                                } 
                                
                                if($item['datatype'] === 'xml') {
                                    $new_data[$group]['endpoints'][$url]['RequestExample'][$key]['value'] = $this->formatXMLforHTML($new_data[$group]['endpoints'][$url]['RequestExample'][$key]['value']);
                                }
                                
                            }
                        }


                        if (array_key_exists('ResponseExample', $new_data[$group]['endpoints'][$url])) {
                            foreach($new_data[$group]['endpoints'][$url]['ResponseExample'] as $key => $item) {
                                if (in_array($item['datatype'], ['json', 'array', 'object', 'xml'])){
                                    $new_data[$group]['endpoints'][$url]['ResponseExample'][$key]['value'] = 
                                        str_replace("'", '"', $new_data[$group]['endpoints'][$url]['ResponseExample'][$key]['value']);
                                }

                                if($item['datatype'] === 'xml') {
                                    $new_data[$group]['endpoints'][$url]['ResponseExample'][$key]['value'] = $this->formatXMLforHTML($new_data[$group]['endpoints'][$url]['ResponseExample'][$key]['value']);
                                }
                                
                            }
                        }

                        if (array_key_exists('ResponseErrorExample', $new_data[$group]['endpoints'][$url])) {
                            foreach($new_data[$group]['endpoints'][$url]['ResponseErrorExample'] as $key => $item) {
                                if (in_array($item['datatype'], ['json', 'array', 'object', 'xml'])){
                                    $new_data[$group]['endpoints'][$url]['ResponseErrorExample'][$key]['value'] = 
                                        str_replace("'", '"', $new_data[$group]['endpoints'][$url]['ResponseErrorExample'][$key]['value']);
                                }

                                if($item['datatype'] === 'xml') {
                                    $new_data[$group]['endpoints'][$url]['ResponseErrorExample'][$key]['value'] = $this->formatXMLforHTML($new_data[$group]['endpoints'][$url]['ResponseErrorExample'][$key]['value']);
                                }
                                
                            }
                        }

                        
                    }
                }
            }

        }
        $this->data = $new_data;
    }

    private function formatXMLforHTML(string $xml) {
        $xml = addslashes(htmlentities($xml));
        $xml = preg_replace('/(&gt;)([^\&lt;]*)(&lt;[^\/])/', '$1$2<br>$3', $xml);
        return $xml;
    }
}