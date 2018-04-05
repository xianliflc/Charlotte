# Charlotte 

## Get Started
#### Quick Start
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

### Config Object

You can then initialize the config object by:

```php

$config= new Config('path/to/shared/config');


// if you have a config for specific env then you can do the following
$config= new Config('path/to/shared/config', 'path/to/specific/env/config/for/overriding');

// you can access to config very easily
$config->get('level1->level2->level3');

// or if you have a default value for absent value
$config->get('level1->level2->level3', 'default value');

// this will ignore anything after the first ->, and return value of node 'level1'
$config->get('level1->->level3', 'default value');

```


## Components and Features

> HTTP

> ORM

> ROUTES

> SERVICE CONTAINER

> Doc Generator

