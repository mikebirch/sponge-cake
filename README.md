# SpongeCake Plugin

SpongeCake is a CakePHP 4 plugin for managing content.

SpongeCake can be used with the [sponge-admin](https://github.com/mikebirch/sponge-admin) admin theme.

## Requirements

* CakePHP 4.4+
* PHP 7.4+

## Routes

Example routes.php

```
 $routes->scope('/', function (RouteBuilder $builder) {

        $builder->connect(
            '/admin',
            [
                'plugin' => 'SpongeCake',
                'controller' => 'Contents',
                'action' => 'adminIndex']);
        $builder->connect(
            '/pages/{action}/*',
            [
                'plugin' => 'SpongeCake',
                'controller' => 'Contents'
            ]);
        $builder->connect(
            '/*',
            [
                'plugin' => 'SpongeCake',
                'controller' => 'Contents',
                'action' => 'display'
            ],
            ['routeClass' => 'SpongeCake.ContentsRoute']);
         $builder->fallbacks();
    });
```
