<?php
namespace SpongeCake\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SpongeCake\Model\Entity\Content;
use Cake\Event\Event;
use Cake\ORM\Entity;
//use Cake\Datasource\ConnectionManager;
use Cake\Cache\Cache;
use Cake\Log\Log;

/**
 * Contents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Menus
 * @property \Cake\ORM\Association\BelongsTo $ParentContents
 * @property \Cake\ORM\Association\HasMany $ChildContents
 */
class ContentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('contents');
        $this->displayField('title');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');
        $this->addBehavior('Tools.Slugged', ['label' => 'title', 'unique' => true, 'case' => 'low']);
        $this->belongsTo('ParentContents', [
            'className' => 'SpongeCake.Contents',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('ChildContents', [
            'className' => 'SpongeCake.Contents',
            'foreignKey' => 'parent_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->add('lft', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('lft');
            
        $validator
            ->add('rght', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('rght');
            
        $validator
            ->allowEmpty('description');
            
        $validator
            ->requirePresence('nav', 'create')
            ->notEmpty('nav');
            
        $validator
            ->requirePresence('title', 'create')
            ->notEmpty('title');
            
        $validator
            ->allowEmpty('sidebar');
            
        $validator
            ->requirePresence('body', 'create')
            ->notEmpty('body');
            
        $validator
            ->add('published', 'valid', ['rule' => 'boolean'])
            ->requirePresence('published', 'create')
            ->notEmpty('published');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentContents'));
        return $rules;
    }

    /**
     * deletes and rebuilds the cache of paths for records in content table
     * @return void
     */
    public function afterDelete()
    {
        Cache::delete('pagesByPath');
        $this->cachePages();
    }

    /**
     * Updates the paths for each record in the contents table
     * and updates the cache file of paths.
     * 
     * @param \Cake\Event\Event $event The afterSave event that was fired
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @return void
     */
    public function afterSave(Event $event, Entity $entity, $options)
    {
        if(!$entity->isNew()) {
            $this->_updatePath($entity->id, $entity);
            // update the path for child pages
            $children = $this
                ->find('children', ['for' => $entity->id])
                ->toArray();
            foreach($children as $child) {
                $this->_updatePath($child->id, $entity);           
            }
        } else {
            $this->_updatePath($entity->id, $entity);
        }
        Cache::delete('pagesByPath');
        $this->cachePages();
    }

    /**
     * Custom finder that finds all records in the contents table.
     * nav field values are prefixed to visually indicate relative depth in the tree.
     * @param \Cake\ORM\Query $query Query.
     * @param array $options options for the query.
     * @return array
     */
    public function findFullTreeList(Query $query, array $options)
    {
        $results = $this->find('all')->toArray();
        $stack = [];
        foreach ($results as $i => $result) {
            while ($stack && ($stack[count($stack) - 1] < $result->rght)) {
                array_pop($stack);
            }
            $results[$i]->nav = str_repeat('&nbsp;&nbsp;&nbsp;',count($stack)).$results[$i]->nav;
            $stack[] = $result->rght;
        }
        return $results;
    }

    /**
     * Finds all records in contents table and formats the results 
     * with the "path" field as the key.
     * Stores the result in a cache file.
     * 
     * @param  bool $format whether to return or not
     * @return array 
     */
    public function cachePages($format = null)
    {
       
        $query = $this->find('all')
            ->select(['id', 'path', 'public', 'published'])
            ->toArray();

        $pagesByPath = [];

        foreach($query as $page) {
            $pagesByPath[$page->path] = [
                'id' => $page->id, 
                'public' => $page->public,
                'published' => $page->published,
            ];
        }

        Cache::write('pagesByPath', $pagesByPath);

        if($format == true) {
            return $pagesByPath;
        }
    }

    /**
     * Updates the path field for record in the contents table to match the tree hierarchy
     * @param  int $id id of the record
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @return bool
     */
    protected function _updatePath($id, Entity $entity)
    {
        $path = '';
        $pages = $this->find('path', ['for' => $id]);
        foreach($pages as $page) {
            $path .= '/'.$page->slug;
        }
        $entity->set('path', $path);
        return $this->_saveNoCallbacks($entity, $id); // can’t use save() because there is no way to cancel afterSave callback, which results in an infinite loop.
        //$conn = ConnectionManager::get('default');
        //return $conn->query('UPDATE `contents` SET `path` = "'.$path.'" WHERE id='.$id); // use query to avoid afterSave loop
    }

    /**
     * saves a record without the afterSave callback firing
     * from https://github.com/cakephp/cakephp/issues/6006#issuecomment-106969296
     * 
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param  int $id id of the record
     * @return bool
     */
    protected function _saveNoCallbacks($entity, $id)
    {
        $dirtyFields = $entity->extract($this->schema()->columns(), true);
        $result = (boolean) $this->updateAll($dirtyFields, ['id' => $id]);
        $entity->clean();
        return $result ? $entity : false;
    }
}
