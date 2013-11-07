<?php
echo head(array('title'=> 'Manage ' . $group->title, 'bodyclass' => 'groups manage'));
?>

<?php echo $this->partial('groups-navigation.php'); ?>
<h1><?php echo metadata($group, 'title'); ?></h1>
<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>
<div id='primary'>

    <form method="post">
        <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />
        <h2><?php echo __('Membership and Role'); ?></h2>
        <div>
            <?php include('role-admin.php'); ?>
        </div>
    
        <?php if(is_allowed($group, 'administration')): ?>
            <h2>Administer Members</h2>    
            <?php include('membership-admin.php'); ?>
        <?php endif;?>
        
        <?php if(is_allowed($group, 'unblock')): ?>
            <?php if(!empty($blocked_users)):?>    
                <h2>Blocked Users</h2>    
            <?php endif; ?>
            <?php include('group-blocks-admin.php'); ?>
        <?php endif;?>
        
        <h2>Notifications</h2>
        <?php include('notifications-admin.php'); ?>
        <button>Save settings</button>
    </form>
</div>

<?php echo foot(); ?>