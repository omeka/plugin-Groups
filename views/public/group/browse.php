<?php
echo head(array('title'=>'Browse Groups', 'bodyclass' => 'groups browse'));

?>

<?php echo $this->partial('group-manage-nav.php'); ?>

<?php echo $this->groupSearchFilters(); ?>

<div id='primary'>

<h1>Groups</h1>
<div id="pagination-top" class="pagination"><?php echo pagination_links(); ?></div>
<div class="groups">
<?php foreach(loop('groups') as $group):  ?>
<div class="hentry">
<h2><?php echo link_to($group, 'show', $group->title); ?></h2>
<p class='groups-type'><?php echo metadata($group, 'visibility'); ?>
<div class="type-description"><?php echo $group->visibilityText(); ?></div>
</p>
<h3>Description</h3>
<div class='groups-description'><?php echo $group->description; ?></div>

<?php $group_tags = groups_tags_list_for_group($group); ?>

<?php if(!empty($group_tags)) :?>
<div class='groups-tags'>
<h3>Tags</h3>
<?php echo $group_tags; ?>
</div>
<?php endif;?>

<p id='groups-member-count'><span class="count"><?php echo metadata($group, 'members_count'); ?></span> members</p>
<p id='groups-item-count'><span class="count"><?php echo metadata($group, 'items_count'); ?></span> items</p>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="tags">
    <h1>All Tags</h1>
    <?php echo tag_cloud($this->tags, 'groups/browse'); ?>
</div>


<?php echo foot(); ?>