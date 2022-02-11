<?php
namespace SpongeCake\Routing\Route;

use Cake\Routing\Route\Route;
use Cake\Cache\Cache;
use Cake\ORM\Locator\LocatorAwareTrait;

class ContentsRoute extends Route {

    use LocatorAwareTrait;

    /**
     * Checks to see if the given URL matches a path in the contents table.
     *
     * If the route can be parsed an array of parameters will be returned; if not
     * false will be returned.
     *
     * @param string $url The URL to attempt to parse.
     * @return array|bool Boolean false on failure, otherwise an array of parameters.
     */
    public function parse($url, $method = '')
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
            $contents = $this->getTableLocator()->get('SpongeCake.Contents');
            $pagesByPath = $contents->cachePages(true);
        }

        if (isset($pagesByPath[$url])) {
            $params['path'] = $url;
            $params['public'] = $pagesByPath[$url]['public'];
            $params['published'] = $pagesByPath[$url]['published'];
            return $params;
        }

        return false;

    }

}
