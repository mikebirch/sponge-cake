<?php
namespace SpongeCake\Controller;

use SpongeCake\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;
use Cake\Network\Session;

/**
 * Contents Controller
 *
 * @property \SpongeCake\Model\Table\ContentsTable $Contents
 */
class ContentsController extends AppController
{

    /**
     * Initialization hook method.
     * 
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * If the action is "display", $this->request->params['pass']['public'] variable 
     * is checked to set $this->Auth->allow for "public" pages
     * 
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        if($this->request->params['action'] == 'display') {
            // allow users to view pages only if the page is "public" or the user is logged in
            if(($this->request->params['pass']['public'] == true) || $this->Auth->user()) {
                $this->Auth->allow(['display']);
            } 
        }
        parent::beforeFilter($event);
    }
    
    /**
     * Default isAuthorized method
     * @param array $user
     * @return bool True if allowed
     */
    public function isAuthorized($user = null)
    {
        $action = $this->request->params['action'];

        if(in_array($action, ['siblings'])) {           
            return true;
        }

        if (in_array($action, ['moveUp', 'moveDown'])) {
            return (bool)($user['role'] === 'admin');
        }

        if (in_array($action, ['add'])) {
            return (bool)($user['is_admin']);
        }

        return parent::isAuthorized($user);
    }

    /**
     * Index method
     *
     * @return void
     */

    public function adminIndex()
    {
        $contents = $this->Contents->find('fullTreeList');
        $this->set('contents', $contents);
        $this->set('_serialize', ['contents']);
    }

    /**
     * Display method
     *
     * @param string $path Content path.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When the record is not published and 
     * the user doesn't have the admin role
     */
    public function display($path)
    {
        if($this->request->params['pass']['published'] == false) {
            if($this->Auth->user('role') == 'admin') {
                $this->Flash->error(__('This page is not published and can only be viewed by administrators.'));
            } else {
                throw new NotFoundException();
            }   
        }
        $content = $this->Contents
            ->find('all', ['conditions' => ['Contents.path' => $path]])
            ->contain([
                    'ParentContents' => ['fields' => ['id', 'slug', 'path', 'parent_id', 'nav']],
                    'ChildContents' => ['fields' => ['id', 'slug', 'path', 'parent_id', 'nav', 'published', 'public'],'conditions' => ['published' => 1]]
                ])
            ->firstOrFail();

        $siblings = null;
        if($content->parent_id) {
            $siblings = $this->siblings($content->parent_id);
        }

        // breadcrumbs
        $breadcrumbs = $this->Contents->find('path', ['for' => $content->id]);
        $count_breadcrumbs = $breadcrumbs->count();
        $breadcrumbs = $breadcrumbs->toArray();

        // children (all children, $content->child_contents only goes down one level)
        $children = $this->Contents
            ->find('children', ['for' => $breadcrumbs[0]->id])
            ->find('threaded', [
                'fields' => ['id', 'slug', 'path', 'parent_id', 'nav', 'published', 'public'],
                'order' => 'lft ASC'
            ])
            ->toArray();

        $this->set(compact('content', 'siblings', 'breadcrumbs', 'count_breadcrumbs', 'children'));
        $this->set('_serialize', ['content']);
        if($path == '/') {
            $this->set('bodyclass', 'home');
        }
    }

    /**
     * Finds siblings for a record in the contents table
     * @param  int $id Content id
     * @return array 
     */
    public function siblings($id)
    {
        $siblings = $this->Contents
            ->find('children', ['for' => $id])
            ->find('threaded', [
                'fields' => ['id', 'slug', 'path', 'parent_id', 'nav', 'published', 'public'],
                'conditions' => ['Contents.published' => 1]
            ])
            ->toArray();
        if ($this->request->is('requested')) {
            $this->response->body($siblings);
            return $this->response;
        } else {
            return $siblings;
        }
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $content = $this->Contents->newEntity();
        if ($this->request->is('post')) {
            $content = $this->Contents->patchEntity($content, $this->request->data);
            if ($this->Contents->save($content)) {
                $this->Flash->success(__('The content has been saved.'));
                return $this->redirect(['action' => 'admin_index']);
            } else {
                $this->Flash->error(__('The content could not be saved. Please, try again.'));
            }
        }
        $parents = $this->Contents->find('treeList', ['spacer' => '_', 'valuePath' => 'nav']);
        $this->set(compact('content', 'parents'));
        $this->set('_serialize', ['content']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Content id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $content = $this->Contents->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $content = $this->Contents->patchEntity($content, $this->request->data);
            if ($this->Contents->save($content)) {
                $this->Flash->success(__('The content has been saved.'));
                return $this->redirect(['action' => 'admin_index']);
            } else {
                $this->Flash->error(__('The content could not be saved. Please, try again.'));
            }
        }
        $parents = $this->Contents->find('treeList', ['spacer' => '_', 'valuePath' => 'nav']);
        $this->set(compact('content', 'parents'));
        $this->set('_serialize', ['content']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Content id.
     * @return void Redirects to admin_index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $content = $this->Contents->get($id);
        if ($this->Contents->delete($content)) {
            $this->Flash->success(__('The content has been deleted.'));
        } else {
            $this->Flash->error(__('The content could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'admin_index']);
    }

    /**
     * Moves a record up the tree
     * @param  int $id Content id
     * @return void Redirects to admin_index 
     */
    public function moveUp($id)
    {
        $content = $this->Contents->get($id);
        $this->Contents->moveUp($content);
        return $this->redirect(['action' => 'admin_index']);
    }

    /**
     * Moves a record down the tree
     * @param  int $id Content id
     * @return void Redirects to admin_index 
     */
    public function moveDown($id)
    {
        $content = $this->Contents->get($id);
        $this->Contents->moveDown($content);
        return $this->redirect(['action' => 'admin_index']);
    }
}
