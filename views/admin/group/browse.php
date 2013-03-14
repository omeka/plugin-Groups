<?php
echo head(array('title' => 'Groups'));
?>

<div id='primary'>
<?php foreach(loop('group') as $group):?>
<div>
<?php $group = groups_get_current_group(); ?>
<h2><a href="<?php echo url('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title; ?></a></h2>

</div>
<?php endforeach; ?>

</div>


<?php echo foot(); ?>