# SpongeCake plugin for CakePHP 3.3

*Note: This plugin is currently in pre-alpha development and is unsupported.*

SpongeCake is a plugin for managing content.

SpongeCake can be used with the [sponge-admin](https://github.com/mikebirch/sponge-admin) admin theme and the [cakephp-froala-upload](https://github.com/mikebirch/cakephp-froala-upload) plugin.

## Routes

Example routes.php

use Cake\Core\Plugin;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::defaultRouteClass(DashedRoute::class);

// load plugin routes first, because SpongeCake has a route for '/*' which needs to load last.
Plugin::routes();

Router::scope('/', function ($routes) {
    $routes->connect('/admin', ['controller' => 'Users', 'action' => 'dashboard']);
    // use this route for all admin routes for SpongeCake
    $routes->connect('/pages/:action/*', ['plugin' => 'SpongeCake', 'controller' => 'Contents']);
    $routes->connect('/*', ['plugin' => 'SpongeCake', 'controller' => 'Contents', 'action' => 'display'], ['routeClass' => 'SpongeCake.ContentsRoute']);
    $routes->fallbacks(DashedRoute::class);
});


