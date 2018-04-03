# HTTP

## Request

``` use Charlotte\Http\Request```
<p>
This class contains all you need from a HTTP request, it has properties such as :

```'get', 'post', 'server', 'cookies', 'env'```

It has methods such as: get, set, has, getAll, and more.
</p>


## Response

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


## Client

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

