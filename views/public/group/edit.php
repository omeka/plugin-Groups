<?php
echo head(array('title' => 'Edit Group'));
?>

<?php echo $this->partial('groups-navigation.php'); ?>
<h1><?php echo metadata($group, 'title'); ?></h1>
<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>
<div id='primary'>
<?php echo $form; ?>
</div>


<?php echo foot(); ?>