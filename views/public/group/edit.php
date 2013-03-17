<?php
echo head(array('title' => 'Edit Group'));
?>

<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>

<div id='primary'>
<?php echo $form; ?>
</div>


<?php echo foot(); ?>