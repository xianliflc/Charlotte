<?php

namespace Charlotte\ApiComponents;

use Charlotte\Core\Controller as BaseController;
use Charlotte\Http\Response;

class Controller extends BaseController {

    const MINIMUM_PARAMS = -1;

    /**
     * Check the mandatory fields
     */
    const CHECKLIST = array(
    );

    const IGNORE_VALIDATION = false;


    /**
     * validate the input based on the checklist and minimum_params
     */
    protected function validate() {
        if (static::IGNORE_VALIDATION === true) {
            return true;
        }

        if (!isset($this->get('config')['environment']['force_validation']) || 
            (isset($this->get('config')['environment']['force_validation']) && $this->get('config')['environment']['force_validation'] === false)) {
            return true;
        }

        $params = $this->request->isMethod('POST') ? $this->request->getAll('post') : $this->request->getAll('get');

        if (static::MINIMUM_PARAMS < 0 || (static::MINIMUM_PARAMS > 0 && count(static::CHECKLIST) !== static::MINIMUM_PARAMS)) {
            $response = new Response($this->errorResponse('GeneralError', 'Internal Error'), 500);
            $response->process();
        }

        if (count($params) < static::MINIMUM_PARAMS) {
            $response = new Response($this->errorResponse('BadRequest', 'Invalid Input'), 400);
            $response->process();
        }

        foreach (static::CHECKLIST as $key => $value) {
            if (!isset($params[$key]) ||
                (isset($value['regex']) && !preg_match("/" . $value['regex'] . "/i", $params[$key])) ||
                (isset($value['func']) && !$value['func']($params[$key]))) {
                $response = new Response($this->errorResponse('BadRequest', 'Invalid Input'), 400);
                $response->process();
            }
        }
    }

}
