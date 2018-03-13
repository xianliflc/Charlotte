<h1> Charlotte </h1>

<h2> Get Started </h2>
<h4>Main Config </h4>
<p>Create ```shared.json``` under ```config/```, like the following:

```
{
  "env" : "dev",
  "environment" : {
      "force_validation" : false,
      "unregister_globals" : true
  },
  "container" : {
    "db" : {
      "host" : "http://localhost",
      "port" : "3306",
      "user" : "test_user",
      "password" : "password",
      "default_db" : "test_db",
      "driver" : "mysql"
    }
  }
}
```
If you don't want forced validation please keep ```'force_validation' : false```
</p>
<h4>Route</h4>

<p>
Create ```routes.json``` under ```config/```, like the following:
```
{
    "test" : {
        "path" : "/test",
        "package" : "Testing",
        "controller" : "Test",
        "action" : "test",
        "methods" : ["get"]
    }
}
```
</p>

<h4>Application</h4>

<p>
Create ```app/``` in root directory, and create ```Controllers/```, ```Containers/``` as required.

Create ```app/Controllers/Testing/TestController.php``` with the following content:

```
<?php

namespace app\Controllers\Testing;

use Charlotte\ApiComponents\Controller;

class TestController extends Controller {

    //const IGNORE_VALIDATION = true;

    public function testAction() {

        return array('asd'=>'cvdsa');
    }

    public function secondtestAction() {
        return $this->request->getAll('get');
    }
}

```

If you have force_validation on and you want skip the validation on a certain controller, you can add ```const IGNORE_VALIDATION = true;```


Now you can test this via ```http://localhost/test```, and see the result:
```
{"asd":"cvdsa"}
```
</p>

<h4>Service Container</h4>

<p> 
Create a service container like this:

```
$service_contaienr = ServiceContainer::getInstance();
```
and add a service into it before ```$core->run()```:

```
$service_contaienr->addService('test', new Service(new Defination('app\\Lib\\Service\\TestService', false, 123, 222)));
```

The TestService.php is under ```app/lib/Service/```, and its code:

```
<?php

namespace app\Lib\Service;

class TestService {

    private $a;
    private $b; 
    public $c;

    public function __construct(...$arr)
    {
        $this->a = $arr[0];
        $this->b = $arr[1];
         
    }

    public function add() {
        return $this->a + $this->b;
    }

}
```

</p>

<h2> Routes</h2>

<p> 

You can add the following to ```/config/routes.json```:

```
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

```
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

<h2>Service Container</h2>

The ```service container``` can contain all needed services across the app. You don't have to manage a lot of dependecies and services amually.

<h4>Initialization</h4>

<p>
Create a service container and add needed services before core is running

```
$service_contaienr = ServiceContainer::getInstance();

$service_contaienr->addService('test', new Service(new Defination('app\\Lib\\Service\\TestService', false, 123, 222)));
```

The TestService.php is under ```app/lib/Service/```, and its code:

```
<?php

namespace app\Lib\Service;

class TestService {

    private $a;
    private $b; 
    public $c;

    public function __construct(...$arr)
    {
        $this->a = $arr[0];
        $this->b = $arr[1];
    }

    public function add() {
        return $this->a + $this->b;
    }
}
```

now you can get the service by:

```
$this->services->getService('test')
```

you can call the function by:

```
$this->services->getService('test')->add()
```

you can set the property:

```
$this->services->getService('test')->c = 1000;
```

don't forget to add:

```
use Charlotte\Services\ServiceContainer;
use Charlotte\Services\Service;
use Charlotte\Core\Defination;
```

</p>
