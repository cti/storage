# Dependency Manager
This component implements dependency injection pattern.   
Manager can inject properties, methods, configure objects and resolve depenencies while calling methods.  

# Object configuration
Configuration param is optional, but it is very useful for configure instances.

```php
<?php

$configuration = new Di\Configuration(array(
    'ClassName' => array(
        'property' => 'value'
    )
));

$manager = new Di\Manager($configuration);

echo $manager->create('ClassName')->property; // value

```

You can merge configuration from different files and set properties directly.

```php
<?php

$configuration = new Di\Configuration();

// override one property is easy
$configuration->set('Database', 'hostname', '192.168.2.87');

// override multiple properties
$configuration->merge(array(
    'Database' => array(
        'username' => 'nekufa',
        'password' => 'secret',
        'hostname' => '192.168.2.91',
    )
));

// you can get full class configuration as array
$configuration->get('Database');

// or any property
$configuration->get('Database', 'username');

```
