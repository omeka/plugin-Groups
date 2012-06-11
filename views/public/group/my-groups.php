<?php
head(array());
?>


<div id='primary'>
<?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
<div>
<?php $group = groups_get_current_group(); ?>
<h2><a href="<?php echo uri('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title; ?></a></h2>
<?php echo groups_tags_for_group($group); ?>
<?php groups_member_count($group); ?>
<p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>
<?php endwhile; ?>



</div>


<?php foot(); ?>