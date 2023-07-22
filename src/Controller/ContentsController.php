<?php
declare(strict_types=1);

namespace SpongeCake\Controller;

use SpongeCake\Controller\AppController;
use Cake\Core\Plugin;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Contents Controller
 *
 * @method \SpongeCake\Model\Entity\ContentsTable[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ContentsController extends AppController
{

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
    }

    /**
     * Index method
     *
     * @return void
     */

    public function adminIndex()
    {
        $query = $this->Contents->find('fullTreeList');
        $results = $query->toArray();
        $stack = [];
        foreach ($results as $i => $result) {
            while ($stack && ($stack[count($stack) - 1] < $result->rght)) {
                array_pop($stack);
            }
            $results[$i]->nav = str_repeat('&nbsp;&nbsp;&nbsp;',count($stack)).$results[$i]->nav;
            $stack[] = $result->rght;
        }
        $this->set('contents', $results);
    }

    /**
     * Display method
     *
     * @param string $path Content path.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found or
     * when the record is not published and the user doesn't have the admin role
     */
    public function display()
    {
        $query = $this->Contents->find('page', ['path' => $this->request->getParam('path')]);
        $content = $query->first();
        if ($content == null) {
            throw new RecordNotFoundException();
        }

        $user = $this->getRequest()->getAttribute('identity');
        $admin = false;
        if ($user) {
            $admin = $this->getRequest()->getAttribute('identity')->role == 'admin';
        }

        if($this->request->getParam('published') == false) {
            if($admin) {
                $this->Flash->warning(__('This page is not published and can only be viewed by administrators.'));
            } else {
                throw new RecordNotFoundException();
            }
        } elseif ($this->request->getParam('public') == false && $admin == false) {
            throw new RecordNotFoundException();
        }

        $breadcrumbs = $this->Contents->find('breadcrumbs', ['id' => $content->id]);
        $crumbs = $breadcrumbs->toArray();
        $count_breadcrumbs = $breadcrumbs->count();

        $this->set(compact('content', 'crumbs', 'count_breadcrumbs'));
        if($this->request->getParam('path') == '/') {
            $this->set('bodyclass', 'home');
        }

    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $content = $this->Contents->newEmptyEntity();
        if ($this->request->is('post')) {
            $content = $this->Contents->patchEntity($content, $this->request->getData());
            if ($this->Contents->save($content)) {
                $this->Flash->success(__('The content has been saved.'));
                return $this->redirect(['action' => 'admin_index']);
            } else {
                $this->Flash->error(__('The content could not be saved. Please, try again.'));
            }
        }
        $parents = $this->Contents->find('treeList', ['spacer' => '_', 'valuePath' => 'nav']);
        $this->set(compact('content', 'parents'));
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->enableAutoLayout(false);
        }
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
            $content = $this->Contents->patchEntity($content, $this->request->getData());
            if ($this->Contents->save($content)) {
                $this->Flash->success(__('The content has been saved.'));
                return $this->redirect(['action' => 'admin_index']);
            } else {
                $this->Flash->error(__('The content could not be saved. Please, try again.'));
            }
        }
        $parents = $this->Contents->find('treeList', ['spacer' => '_', 'valuePath' => 'nav']);
        $this->set(compact('content', 'parents'));
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->enableAutoLayout(false);
        }
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

    public function dashboard()
    {

    }
}
