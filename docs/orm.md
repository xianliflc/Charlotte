# Database and ORM

## Adapter

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


## Query

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

## Entity

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


## Commit and Persist

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