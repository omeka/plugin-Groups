<?php
require_once GROUPS_PLUGIN_DIR . '/forms/GroupsSearchForm.php';
head(array('title'=>'Browse Groups', 'bodyclass' => 'browse'));
?>


<div id='primary'>
<?php if(has_permission('Groups_Group', 'add')): ?>
<p><a href='<?php echo uri('/groups/add'); ?>'>Add a group</a></p>
<?php endif; ?>

<?php echo new GroupsSearchForm(); ?>
<?php echo tag_cloud($this->tags, 'browse'); ?>

<div id="pagination-top" class="pagination"><?php echo pagination_links(); ?></div>
<div style="clear:left;"></div>
<h2>Groups</h2>
<?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
<div class="hentry">
<?php $group = groups_get_current_group(); ?>
<h2><a href="<?php echo uri('groups/show/' . $group->id); ?>"><?php echo $group->title; ?></a></h2>
<div class='groups-description'><?php echo $group->description; ?></div>
<?php echo groups_tags_list_for_group($group); ?>
<p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
<p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>
<?php endwhile; ?>

</div>


<?php foot(); ?>