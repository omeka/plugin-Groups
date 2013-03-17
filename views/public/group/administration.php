<?php
$title = __('Administer groups');
echo head(array('title'=>$title));
?>
<?php 
echo $this->partial('groups-navigation.php');
?>
<h1><?php echo $title; ?></h1>
<div id='primary'>
<?php if(empty($groups)) :?>
<p>You do not have permission to administer any groups.</p>
<?php else: ?>
<form method="post">
    <?php foreach(loop('groups') as $group):  ?>    
        <div class='groups-group'>
            <h3><?php echo link_to($group, 'show', $group->title); ?></h3>
            <?php include('membership-admin.php'); ?>
            <?php $blocked_users = $group->getBlockedUsers(); ?>
            <?php if(!empty($blocked_users)): ?>
            <h4>Blocked Users</h4>
            <?php include('group-blocks-admin.php');?>
            <?php endif;?>
        </div>
        
    <?php endforeach; ?>
    <button>Submit</button>
</form>
<?php endif; ?>
</div>

<?php echo foot(); ?>