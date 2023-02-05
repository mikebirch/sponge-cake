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
     * null will be returned.
     *
     * @param string $url The URL to attempt to parse.
     * @param string $method
     * @return array|null null on failure, otherwise an array of parameters.
     */
    public function parse(string $url, string $method = ''): ?array
    {

        if($url != '/') {
            $url = rtrim($url,'/');
        }

        $params = parent::parse($url, $method);
        if (!($params)) {
            return null;
        }

        $pagesByPath = Cache::read('pagesByPath');

        if ($pagesByPath === false) {
            $contents = $this->getTableLocator()->get('SpongeCake.Contents');
            $pagesByPath = $contents->cachePages(true);
        }
        $contents = $this->getTableLocator()->get('SpongeCake.Contents');
            $pagesByPath = $contents->cachePages(true);

        if (isset($pagesByPath[$url])) {
            $params['path'] = $url;
            $params['public'] = $pagesByPath[$url]['public'];
            $params['published'] = $pagesByPath[$url]['published'];
            return $params;
        }

        return null;
    }
}
