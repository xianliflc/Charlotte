# Charlotte 

## Get Started
#### Main Config
<p>

Create ```shared.json``` under ```config/```, like the following:

```json
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

#### Route

<p>

Create ```routes.json``` under ```config/```, like the following:
```json
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

#### Application

<p>

Create ```app/``` in root directory, and create ```Controllers/```, ```Containers/``` as required.

Create ```app/Controllers/Testing/TestController.php``` with the following content:

```php
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
```json
{"asd":"cvdsa"}
```
</p>

#### Service Container

<p> 
Create a service container like this:

```php
$service_contaienr = ServiceContainer::getInstance();
```
and add a service into it before ```$core->run()```:

```php
$service_contaienr->addService('test', new Service(new Defination('app\\Lib\\Service\\TestService', false, 123, 222)));
```

The TestService.php is under ```app/lib/Service/```, and its code:

```php
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

## Routes

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

## Service Container

The ```service container``` can contain all needed services across the app. You don't have to manage a lot of dependecies and services amually.

#### Initialization

<p>
Create a service container and add needed services before core is running

```php
$service_contaienr = ServiceContainer::getInstance();

$service_contaienr->addService('test', new Service(new Defination('app\\Lib\\Service\\TestService', false, 123, 222)));
```

The TestService.php is under ```app/lib/Service/```, and its code:

```php
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

```php
$this->services->getService('test')
```

you can call the function by:

```php
$this->services->getService('test')->add()
```

you can set the property:

```php
$this->services->getService('test')->c = 1000;
```

don't forget to add:

```php
use Charlotte\Services\ServiceContainer;
use Charlotte\Services\Service;
use Charlotte\Core\Defination;
```

</p>

## Config

#### Environment Variables

<p>

In ```shared.json```:

```json
  "env" : "dev" , // the environment
  "environment" : {
      "force_validation" : false, // whether you force input validation
      "unregister_globals" : true, // remove all registered globals
      "auto_response" : false // all response will be sent automatically
  }

```

``` force_validation ```
The default value is ```false```, if you set it to ```true```, then you have to overwrite the ```CHECKLIST``` and  ```MINIMUM_PARAMS``` in your controller which extends apiController

``` auto_response```

If you use ``` "auto_response" : false ```, then you have to do the following:

```php
    $response = $core->run($service_contaienr);
    $response->process();
```
 If you use ``` "auto_response" : true ``` which is the default value. You only need to do the following:

 ```php
    $response = $core->run($service_contaienr);
 ```

 ```unregister_globals```

 The default value is ```false```. If you set the value to ``` true```, then you can not get access to all ```$GLOBALS```

 </p>

### Different Environments

If you have different environmental variables for different environments, you may need to create a separate environment file. For example, if you have different confi for ```dev```, then you may want to create ```dev.json``` under ```config/environments/``` with different configs.

```json
{
    "environment" : {
        "auto_response" : false
    }
}
```


## Components

### HTTP
#### Request

``` use Charlotte\Http\Request```
<p>
This class contains all you need from a HTTP request, it has properties such as :

```'get', 'post', 'server', 'cookies', 'env'```

It has methods such as: get, set, has, getAll, and more.
</p>


#### Response<

``` use Charlotte\Http\Response```
<p>
This class builds the response based on the input, and sends the response back to client.

basic usage is like the following:
```php

$response = new Response($data, 200, 'html', '');
$response->sendResponseHeaders()->finalize();
```
You can use setters to set content type, cookies, headers and and more,

You can use send** methods to send content type, cookies, headers, and more
</p>