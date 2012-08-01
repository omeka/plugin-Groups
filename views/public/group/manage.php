<?php
head(array());
?>


<div id='primary'>
<h1>Manage <?php echo $group->title; ?></h1>
<?php echo flash(); ?>
<a href="<?php echo record_uri($group, 'show'); ?>">Back</a>

<form method="post">
<div>
    <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />

    <h2>Membership and Role</h2>
    <?php include('role-admin.php'); ?>
</div>


<?php if(has_permission($group, 'administration')): ?>
    <h2>Administer Members</h2>    
    <?php include('membership-admin.php'); ?>

<?php endif;?>

<?php if(has_permission($group, 'invitations')): ?>
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



<?php foot(); ?>