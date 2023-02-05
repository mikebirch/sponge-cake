<?php
namespace SpongeCake\View\Cell;

use Cake\View\Cell;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * SubNavCell cell
 */
class SubNavCell extends Cell
{
    use LocatorAwareTrait;

    /**
     * Default display method.
     *
     * @return void
     */
    public function display($here, $loggedIn, $spongecake, $insert = null)
    {
        $this->Contents = $this->fetchTable('SpongeCake.Contents');
        $query = $this->Contents->find('page', ['path' => $here]);
        $content = $query->firstOrFail();

        $breadcrumbs = $this->Contents->find('breadcrumbs', ['id' => $content->id]);

        $crumbs = $breadcrumbs->toArray();
        $count_breadcrumbs = $breadcrumbs->count();

        $query = $this->Contents->find('allchildren', ['id' => $crumbs[0]->id]);
        $children = $query->toArray();

        $menu = $this->_convertToMenu($children, $here, $loggedIn, $spongecake, $insert);

        $this->set(compact('menu', 'crumbs', 'count_breadcrumbs', 'content', 'insert'));
    }

    /**
     * Method for creating a nav menu - from http://stackoverflow.com/a/23095077/354196
     * @param  array $children      all pages in a branch of tree from top level
     * @param  string $here         the current request uri
     * @param  boolean $loggedIn    User logged in or not
     * @return string               Nav menu as an HTML list
     */
    private function _convertToMenu($children, $here, $loggedIn, $spongecake, $insert) {
        $menu = "<ul class='menu'>";
        $count = 0;
        //debug($children); die();
        foreach ($children as $child) {
            //debug($child->public); die();
            if($loggedIn || !empty($child->public)) {
                if (!empty($child['children'])) {
                    if($child->path == $here) {
                        $menu .= '<li class="active sub-nav-item">';
                        $menu .= $child->nav;
                    } else {
                        $menu .= '<li class="sub-nav-item">';
                        $menu .= "<a href=".$child->path.">" . $child->nav . "</a>";
                    }
                    $menu .= $this->_convertToMenu($children, $here, $loggedIn, $spongecake, $insert);
                    $menu .= "</li>";
                } else {
                    if($child->path == $here) {
                        $menu .= '<li class="active sub-nav-item">';
                        $menu .= $child->nav;
                    } else {
                        $menu .= '<li class="sub-nav-item">';
                        $menu .= "<a href=".$child->path.">" . $child->nav . "</a>";
                    }
                    $menu .= "</li>";
                }
                $count ++;
            }
        }
        $menu .= "</ul>";
        if($count > 0) {
            return $menu;
        } else {
            return false;
        }
    }
}
