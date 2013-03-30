<?php
echo head(array('title'=> 'Manage ' . $group->title));
?>

<?php echo $this->partial('groups-navigation.php'); ?>
<h1><?php echo metadata($group, 'title'); ?></h1>
<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>
<div id='primary'>

<form method="post">
    <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />
    <h2>Membership and Role</h2>
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

<?php if(is_allowed($group, 'invitations')): ?>
    <h2>Invite Others</h2>
    <div>
        <div>
            <label for='emails'>Email addresses of people to invite to join groups (comma-separated)</label>
            <input type='text' name='emails' />
        </div>
        <div>
            <input name='invite_groups[]' value='<?php echo $group->id; ?>' type='hidden'/>
        </div>
        
        <div>
            <label for='message'>Message</label>
            <textarea rows='6' cols='40' name='message'></textarea>
        </div>
    </div>    
<?php endif; ?>

    <h2>Notifications</h2>
    <?php include('notifications-admin.php'); ?>
    <button>Submit</button>
    </form>
</div>



<?php echo foot(); ?>