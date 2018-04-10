# Documentation Generator

## API Documentation Generator

In config:

```json
{
  ...
  "doc": {
    "settings": {
      "allowed_files": "php|js|py", //files watched for documentation generation
      "resource_path": "app/Controllers", // where to get documentation from
      "package_prefix": "app\\Controllers", // what is the package for these files
      "export_path": "docs/api/", // where are the exported files stored
      "export_ext": "md", // what format is for the exported files
      "export_template": "default", 
      "template_path" : "default"
    },
    "mappings": {
      "deprecated" : "deprecated" // extra mapping or mappings you want to overwrite
    }
  }
  ...
}

```


```php

$doc = new ApiDoc();

// build documentation
$doc->useConfig($this->get('config'))->build();

// get the generated and parsed documentation
$parsed_doc = $doc->getData();

```

#### sample comment blocks

For class:

```php
/**
 * @author xian.li
 * @group(name="DocTest", description="DocTestController descriptions")
 */

```

For method:

```php
/**
 * @Description(api endpoint description)
 * @RequestMethod(post)
 * @RequestUrl(localhost:80/doc)
 * @RequestNotice(api notice)
 * @ResponseExample(type="success", datatype=array, value="{'firstname' : 'x', 'lastname'  : 'l')
 * @ResponseErrorExample(type="error", datatype=array, value="{'status_code' : 'x', 'error': {'message':'user not found'}}")
 * @RequestExample(datatype=array, value="{'username':'xl','password':'123456'}")
 * @RequestParam(name="username", type="string", description="User id")
 * @RequestParam(name="password", type="string", description="password")
 * @ResponseProperty(name="error", type="array", description="error details")
 * @ResponseException(class="SERVERDOWN", value="[asd,cd]")
 * @deprecated ("1.1")
 * @since ("1.0.0")
 * @property mixed $name
 * @example location [asda, asd]
*/

```