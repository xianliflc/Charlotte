# Routes

<p> 

You can add the following to ```/config/routes.json```:

```json
    "servicetest" : {
        "path" : "/test/service",
        "package" : "Testing\\ServiceTesting",
        "controller" : "ServiceTest",
        "action" : "test",
        "methods": ["post", "get"],
        "ignore_validation" : true
    }
```

```'path'``` is the url this action is reflecting.

```'package'``` is the namespace of the controller

```'controller'``` is the controller name without 'Controller'

```methods``` is the array of allowed methods in this action

In its route, you need to specify the ```action``` which is the prefix of the method in the controller you want to run, in this case it is ```test``` refering to ```testAction```


Then you can create ```ServiceTesting.php``` under ```app/Controllers/Testing/ServiceTesting/``` with the following code:

```php
<?php

namespace app\Controllers\Testing\ServiceTesting;

use Charlotte\ApiComponents\Controller;

class ServiceTestController extends Controller
{

    public function testAction() {
        $this->services->getService('test')->c = 555;

        return array(
            'message' => 'service test controller',
            'method' => $this->request->isMethod('GET'),
            'sum'   => ($this->services->getService('test'))->add(),
            'is_init'  => $this->services->getService('test')->isInitialized(),
            'test_getter' => $this->services->getService('test')->c
        );
    }
}
```
</p>
