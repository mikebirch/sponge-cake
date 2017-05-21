<?php
namespace SpongeCake\Controller;

use SpongeCake\Controller\AppController;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;
use Cake\Network\Session;
use Cake\Network\Exception\NotFoundException;

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

        $content = $this->Contents->find('page', ['path' => $path]);
        list($breadcrumbs, $count_breadcrumbs) = $this->Contents->find('breadcrumbs', ['id' => $content->id]);

        $this->set(compact('content', 'breadcrumbs', 'count_breadcrumbs'));
        if($path == '/') {
            $this->set('bodyclass', 'home');
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
        if ($this->Contents->moveUp($content)) {
            $this->Flash->success('The page has been moved up.');
        } else {
            $this->Flash->error('The page could not be moved up. Please, try again.');
        }
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
        if ($this->Contents->moveDown($content)) {
            $this->Flash->success('The page has been moved down.');
        } else {
            $this->Flash->error('The page could not be moved down. Please, try again.');
        }
        return $this->redirect(['action' => 'admin_index']);
    }
}
