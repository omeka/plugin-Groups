<?php
head(array());
?>


<div id='primary'>
<h1>Manage <?php echo $group->title; ?></h1>
<?php echo flash(); ?>
<a href="<?php echo record_uri($group, 'show'); ?>">Back</a>

<form method="post">


<div >
    <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />

    <h2>Membership and Role</h2>
    <?php if($user_membership->is_owner): ?>
        <p>You are the owner of this group. You can transfer ownership in the <a href="<?php echo uri('groups/administration'); ?>">administration page</a>.</p>
    
    <?php else: ?>
        <label class='groups' for="groups[<?php echo $group->id ?>][quit]">Membership</label>
        <input type='checkbox' name="groups[<?php echo $group->id ?>][quit]" />Leave<br/>                
    <?php endif; ?>
                    

    <?php $adminConfirm = groups_role_confirm($group, $user_membership, 'is_admin'); ?>
    <?php $ownerConfirm = groups_role_confirm($group, $user_membership, 'is_owner'); ?>
    <?php if($adminConfirm || $ownerConfirm ): ?>
        <label class='groups' for="groups[<?php echo $group->id ?>][role]">Role</label>
        <?php if($adminConfirm) :?>
            <input type='checkbox' value='is_admin' name="groups[<?php echo $group->id ?>][role]" />Admin
                <p>An administrator of this group has asked you to become an administrator. Check here to accept.</p>                                        
        <?php endif; ?>
        <?php if( $ownerConfirm ) :?>
            <input type='checkbox' value='is_owner' name="groups[<?php echo $group->id ?>][role]" />Owner
            <p>The owner of this group would like to transfer ownership to you. Check here to accept.</p>
        <?php endif; ?>

    <?php else: ?>
        <p>Role: <?php echo $user_membership->role(); ?></p>
    <?php endif; ?>            

</div>


<?php if(has_permission($group, 'administration')): ?>
<h2>Administer Members</h2>
    <div>
            <?php $memberships = groups_get_memberships($group);             
                  foreach($memberships as $membership):
            ?>
            <?php 
                $current_user = current_user();
                
                if(count($memberships) == 1): ?>
                <div class='group-membership'>
                <p>There are no members in your group! Why not <a href="<?php echo uri('groups/invitations'); ?>">invite some friends</a>?</p>
                
                </div>
                <?php endif; ?>
                <?php if($membership->user_id == $current_user->id) {continue;} ?>
            <div class='group-membership'>

                <h4><?php echo $membership->User->name; ?></h4>
                <div class='group-options'>
                    
                    <label for="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]">Membership</label>
                    <?php if($membership->is_pending) : ?>
                        <input name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='approve' />Approve
                        <input name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='deny' />Deny
                    <?php else: ?>
                        <input name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='checkbox' value='remove' />Remove
                    <?php endif; ?><br/>
                    <?php if(has_permission($group, 'change-status')): ?>
                        <?php $role = $membership->role(); ?>
                        <label for="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]">Status</label>
                        <?php if(has_permission($group, 'make-owner')): ?>
                            <input <?php if($role == 'Owner') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='owner' />Owner
                        <?php endif; ?>
                        <input <?php if($role == 'Admin') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='admin' />Admin
                        <input <?php if($role == 'Member') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='member' />Member
                        <p class='explanation'>Users must accept being made an Admin or Owner before the change takes effect</p>
                    <?php endif;?>
                </div>
            </div>
            
            <?php endforeach; ?>

            <?php endif;?>
            
    </div>
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
    <div class='group-notifications'>
        <h3>Send email notifications to me when:</h3>        
        <input <?php if($user_membership->notify_member_joined) {echo "checked='checked'"; }?> type='checkbox' name="groups[<?php echo $group->id ?>][notify_member_joined]" />
        <label class='groups' for="groups[<?php echo $group->id ?>][notify_member_joined]">New Members Join</label>

        <input <?php if($user_membership->notify_member_left) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_member_left]" />
        <label class='groups' for="groups[<?php echo $group->id ?>][notify_member_left]">A Member Leaves</label>

    
        <input  <?php if($user_membership->notify_item_new) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_item_new]" />                
        <label class='groups' for="groups[<?php echo $group->id ?>][notify_item_new]">New Items Are Added</label>
        
    
        <input  <?php if($user_membership->notify_item_deleted) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_item_deleted]" />                
        <label class='groups' for="groups[<?php echo $group->id ?>][notify_item_deleted]">Items Are Deleted</label>                                                
    </div>
    <button>Submit</button>
    </form>
</div>



<?php foot(); ?>