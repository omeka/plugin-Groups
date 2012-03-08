<?php
head(array());
?>


<div id='primary'>
<?php if(has_permission('Groups_Group', 'add')): ?>
<p><a href='<?php echo uri('/groups/add'); ?>'>Add a group</a></p>
<?php endif; ?>


<?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
<div>
<?php $group = groups_get_current_group(); ?>
<h2><a href="<?php echo uri('groups/show/' . $group->id); ?>"><?php echo $group->title; ?></a></h2>
<div class='groups-description'><?php echo $group->description; ?></div>
<?php echo groups_tags_for_group($group); ?>
<p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
<p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>
<?php endwhile; ?>

</div>


<?php foot(); ?>