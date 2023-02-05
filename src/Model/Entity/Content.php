<?php
declare(strict_types=1);

namespace SpongeCake\Model\Entity;

use Cake\ORM\Entity;

/**
 * Content Entity.
 *
 * @property int $id
 * @property string|null $slug
 * @property string|null $path
 * @property int $parent_id
 * @property int $lft
 * @property int $rght
 * @property string $description
 * @property string $nav
 * @property string|null $title
 * @property string|null $body
 * @property string $sidebar
 * @property bool|null $published
 * @property bool|null $public
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class Content extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
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
