<?php
$title = __('Create a group');
echo head(array('title'=>$title));
?>

<?php 
echo $this->partial('groups-navigation.php');
?>
<h1><?php echo $title; ?></h1>
<?php echo flash(); ?>

<div id='primary'>
<?php echo $form; ?>
</div>


<?php echo foot(); ?>