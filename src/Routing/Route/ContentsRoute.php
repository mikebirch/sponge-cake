<?php
namespace SpongeCake\Routing\Route;

use Cake\Routing\Route\Route;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class ContentsRoute extends Route {

    /**
     * Checks to see if the given URL matches a path in the contents table.
     *
     * If the route can be parsed an array of parameters will be returned; if not
     * false will be returned. 
     *
     * @param string $url The URL to attempt to parse.
     * @return array|bool Boolean false on failure, otherwise an array of parameters.
     */
    public function parse($url)
    {
        
        if($url != '/') {
            $url = rtrim($url,'/');
        }
        
        $params = parent::parse($url);
        if (!($params)) {
            return false;
        }

        $pagesByPath = Cache::read('pagesByPath');

        if ($pagesByPath === false) {
            $contents = TableRegistry::get('Contents', [
                'className' => 'SpongeCake\Model\Table\ContentsTable'
            ]);
            $pagesByPath = $contents->cachePages(true);
        }
        
        if (isset($pagesByPath[$url])) {
            $params['pass'] = [
                'path' => $url, 
                'public' => $pagesByPath[$url]['public'],
                'published' => $pagesByPath[$url]['published']
            ];
            return $params;
        }

        return false;

    }

}