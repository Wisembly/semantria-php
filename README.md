# semantria-php

PHP SDK for Semantria API (http://semantria.com).

This SDK is NOT official, heavily inspired by [intercom-php](https://github.com/intercom/intercom-php)
and by [official Semantria PHP SDK](https://semantria.com/support/developer/docs/sdks)

We developped and maintain this library for our own usage and released it open
source (see LICENCE.md) if it could help.
Semantria and its API belongs to Semantria.

## Basic usage

Remember to include the Composer autoloader in your application:

```php
<?php
require_once 'vendor/autoload.php';

// Application code...
```

Configure your access credentials when creating a client:

```php
<?php
use Semantria\SemantriaAuthClient;

$semantria = SemantriaAuthClient::factory(array(
    'consumer_key'      => 'my-consumer-key',
    'consumer_secret'   => 'my-consumer-secret',
    'application_name'  => 'my-app',
    'use_compression'   => false
));

$semantria->addDocument([
    'id'    => 'foo',
    'text'  => 'This method queues document onto the server for analysis. Queued document analyzes individually and will have its own set of results. If unique configuration ID provided, Semantria uses settings of that configuration during analysis, in opposite the primary configuration uses. Document IDs are unique in scope of configuration. If the same ID appears twice, Semantria overrides existing document with the new Data.'
]);

$semantria->getDocument(['document_id' => 'foo']);
```

## Testing

Run `bin/phpunit`


## Resources

Resources this API supports:

| Uri                                       | Methods   |
| ----------------------------------------- | --------- |
| https://api.semantria.com/configurations  | GET       |
| https://api.semantria.com/documents       | GET POST  |

## Licence

See LICENCE file.
