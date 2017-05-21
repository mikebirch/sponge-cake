<?php
namespace SpongeCake\View\Cell;

use Cake\View\Cell;
use Cake\Cache\Cache;

/**
 * SubNavCell cell
 */
class SubNavCell extends Cell
{

    /**
     * Default display method.
     *
     * @return void
     */
    public function display($here, $loggedIn, $spongecake, $insert = null)
    {
        $this->loadModel('SpongeCake.Contents');
        $content = $this->Contents->find('page', ['path' => $here]);
        $breadcrumbs = $this->Contents->find('breadcrumbs', ['id' => $content->id]);
        // children (all children, $content->child_contents only goes down one level)
        list($breadcrumbs, $count_breadcrumbs) = $breadcrumbs;
        $children = $this->Contents->find('allchildren', ['id' => $breadcrumbs[0]->id]);
        $menu = $this->_convertToMenu($children, $here, $loggedIn, $spongecake,$insert);

        $this->set(compact('menu', 'breadcrumbs', 'content', 'insert'));
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
                    $menu .= $this->_convertToMenu($child->children, $here, $loggedIn, $spongecake, $insert);
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