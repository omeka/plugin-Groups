<?php
require_once GROUPS_PLUGIN_DIR . '/forms/GroupsSearchForm.php';
head(array('title'=>'Browse Groups', 'bodyclass' => 'browse'));
?>


<div id='primary'>
<?php if(has_permission('Groups_Group', 'add')): ?>
<p><a href='<?php echo uri('/groups/add'); ?>'>Add a group</a></p>
<?php endif; ?>

<?php echo new GroupsSearchForm(); ?>
<h1>All Tags</h1>
<?php echo tag_cloud($this->tags, 'browse'); ?>

<div id="pagination-top" class="pagination"><?php echo pagination_links(); ?></div>
<div style="clear:left;"></div>
<h1>Groups</h1>
<?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
<div class="hentry">
<h2><?php echo groups_link_to_group(); ?></h2>
<p class='groups-type'>Type: <?php echo groups_group('visibility'); ?>
<?php echo groups_group_visibility_text(); ?>
</p>
<h3>Description</h3>
<div class='groups-description'><?php echo groups_group('description'); ?></div>

<?php $group_tags = groups_tags_list_for_group($group); ?>

<?php if(!empty($group_tags)) :?>
<h3>Tags</h3>
<div class='groups-tags'>
<?php echo $group_tags; ?>
</div>
<?php endif;?>

<p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
<p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>
<?php endwhile; ?>

</div>


<?php foot(); ?>