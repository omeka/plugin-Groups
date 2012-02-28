<?php
head(array());
?>

<div id='primary'>
<h1>Groups</h1>
<?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
<div>
<?php $group = groups_get_current_group(); ?>
<h2><a href="<?php echo uri('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title; ?></a></h2>

</div>
<?php endwhile; ?>

</div>


<?php foot(); ?>