<?php if($menu) : ?>
<div class="menu-block">
<?php if($breadcrumbs[0]->path == $this->request->getAttribute("here") && isset($content->child_contents)) : ?>
<h2 class="menu-heading"><?= h($breadcrumbs[0]->nav) ?></h2>
<?php elseif(isset($content->parent_content) || $insert) : ?>
<h2 class="menu-heading"><?= $this->Html->link(h($breadcrumbs[0]->nav), $breadcrumbs[0]->path) ?></h2>
<?php endif ?>
<?php  echo $menu; ?>
</div>
<?php endif ?>
