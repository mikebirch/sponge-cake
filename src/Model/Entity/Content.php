<?php
namespace SpongeCake\Model\Entity;

use Cake\ORM\Entity;

/**
 * Content Entity.
 */
class Content extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'slug' => true,
        'path' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'description' => true,
        'nav' => true,
        'title' => true,
        'body' => true,
        'sidebar' => true,
        'published' => true,
        'public' => true,
        'parent_content' => true,
        'child_contents' => true,
    ];
}
