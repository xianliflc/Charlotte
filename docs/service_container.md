# Service Container

The ```service container``` can contain all needed services across the app. You don't have to manage a lot of dependecies and services amually.

## Initialization

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