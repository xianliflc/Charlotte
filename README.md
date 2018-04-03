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

### Config Object

You can then initialize the config object by:

```php

$config= new Config(json_decode(file_get_contents('path/to/shared/config'), true));


// if you have a config for specific env then you can do the following
$config= new Config(json_decode(file_get_contents('path/to/shared/config'), true), json_decode(file_get_contents('path/to/specific/env/config/for/overriding'), true));

// you can access to config very easily
$config->get('level1->level2->level3');

// or if you have a default value for absent value
$config->get('level1->level2->level3', 'default value');

// this will ignore anything after the first ->, and return value of node 'level1'
$config->get('level1->->level3', 'default value');

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


#### Response

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


#### Client

This class is a default Curl client of this framework.

You can initial it by:

```php
$client = new Client();
```

And you can do send request and receive response
```php
$body = $client
    ->setHeaders(
        array(
            'Content-Type' => 'application/json',
            'Authorization' => '123456'
        )
    )
    ->sendPost(
        'http://localhost/test',
        json_encode(array(
            "input1" =>  "1",
            "input2" => "2",
    ))
    )
    ->getResponseBody();
$statusCode = $client->getResponseStatusCode();
$contentType = $client->getResponseInfo()['Content-Type'];
```

<p>
For different methods, you can use respective functions:

`GET`: `$client->sendGet()`

`POST`: `$client->sendPost()`

`PUT`: `$client->sendPut()`

`HEAD`: `$client->sendHead()`

`DELETE`: `$client->sendDelete()`

`PATCH`: `$client->sendPatch()`
</p>

### Database

#### Adapter

```php
// initialization
$dal = new DalAdapter($this->get('config')['container']['db']);

// set default database
$dal->useDataBase('database_name');

// run queries and get fetched results
$result = $dal->query('select * from table');
$description = $dal->query('describe users');

// set default table
$dal->useTable('table');

```


#### Query

```php

$user = 'test';
$password = '123';
$query = new Query();
$query->addQuery('select * from table')->addQuery('insert into table (user, password) values (?, ?)', array($user,$password));

$u = $query->getQuery(); // user
$p = $query->getBindings(); //password
$q = $query->prev()->getQuery(); // 'select * from table'

// clear and reset query
$query->reset();

// add bulk queries
$query->addQueries(array(
    ['select * from table'],
    ['insert into table (user, password) values (?, ?)', [$user,$password]]
));
```


#### Mapper

```php

$mapper = new Mapper();
$mapper->useAdapter(new DalAdapter($this->get('config')['container']['db']));
$mapper->useDatabase('database_name')->useTable('table');

// get the table structure and save to cache
$mapping = $mapper->getMappingAdvance('table');

// get all schemas save to adapter and return them
$dbs = $mapper->retrieveDataBases()->getDataBases();

// get all tables of the current database
$tables = $mapper->retrieveTables()->getTables();

// retrieve data from the table and save into cache
$mapper->retrieveData();

// cached records
var_dump($mapper->getCache()[Mapper::TABLE_RECORDS]);

```

#### Entity

```php

namespace app\Lib\ORM;

use Charlotte\ORM\AbstractEntity;

class User extends AbstractEntity {

    public $id;
    public $password;
    public $user;
    public $created_at;

    public function toString() {
        return array(
            'id' => $this->id, 
            'password' => $this->password, 'user' => $this->user
        );
    }
}
```

```php

$mapper = new Mapper();
$mapper->useAdapter(new DalAdapter($this->get('config')['container']['db']));
$mapper->useDatabase('database_name')->useTable('table')->retrieveData();

//get the entity where user === 'me'
$entity = $mapper->createEntityFromCache('app\\Lib\\ORM\\User', array('user'=> 'me'), '',true, false);

// check if this entity exists in current cache
$in_cache_records_before = $mapper->exists($entity, Mapper::TABLE_RECORDS);

// update the property
$entity->user = 'you';
// save the updated entity into cache
$entity->save();

// create a new entity
$entity2 =  new \app\Lib\ORM\User($mapper->getMappingAdvance());
// inject data into the new entity
$entity2->setMapper($mapper)->use(array(
    "id" => "123",
    "password" => "123",
    "user" => "everyone"
), false)->save(); // false indicate this entity does not exist

// entity does exist in cache for updates
$in_cache_updates = $mapper->exists($entity, Mapper::TABLE_COMMITS_UPDATES);

// the new entity does exist in cache for inserts
$in_cache_records_after = $mapper->exists($entity2, Mapper::TABLE_COMMITS_INSERTS);

// get all cached records
$records = $mapper->getCache(Mapper::TABLE_RECORDS);

// get all cached entities for updates
$updates->getCache(Mapper::TABLE_COMMITS_UPDATES);

// get all cached entities for inserts
$mapper->getCache(Mapper::TABLE_COMMITS_INSERTS);
```


#### Commit and Persist

```php

$mapper = new Mapper();
$mapper
        ->useAdapter(new DalAdapter($this->get('config')['container']['db']))
        ->useDatabase('database_name')
        ->useTable('table_name');

$entity =  new \app\Lib\ORM\User($mapper->getMappingAdvance());
$entity->setMapper($mapper)->use(array(
    "password" => "123",
    "user" => "test"
), false)->save();

$entity2->setMapper($mapper)->use(array(
    "password" => "456",
    "user" => "test2"
), false)->save();

$entity2->user='test3';
$entity2->save();

// totally 3 new records will be inserted / updated
$mapper->commit()->persist();

// get properties details
$properties = $entity2->parseProperties();

// get datatypes for all properties
$data_types = $entity2->parseDataTypes(),

// get all not null properties
$not_null_properties = $entity2->parseNotNullProperties()

```