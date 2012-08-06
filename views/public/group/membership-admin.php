
<div>
<?php 

$memberships = groups_get_memberships($group, true);
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
        <div class='groups-admin-actions'>
    
            <h4><?php echo $membership->User->name; ?></h4>
            <div class='group-options'>
                
                <label for="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]">Membership</label>
                <?php if($membership->is_pending) : ?>
                    <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='approve' />Approve
                    <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='deny' />Deny
                <?php else: ?>
                    <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='checkbox' value='remove' />Remove
                <?php endif; ?>
                    <div class="groups-block-entities">
                        <input name="block[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='checkbox' value='block' />Block                    
                    </div>
                <?php if(has_permission($group, 'change-status')): ?>
                    <div class='groups-role-change <?php if($membership->is_pending) {echo "pending";}?>'>
                        <?php $role = $membership->role(); ?>
                        <label for="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]">Status</label>
                        <?php if(has_permission($group, 'make-owner')): ?>
                            <input <?php if($role == 'Owner') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='owner' />Owner
                        <?php endif; ?>
                        <input <?php if($role == 'Admin') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='admin' />Admin
                        <input <?php if($role == 'Member') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='member' />Member
                        <p class='explanation'>Users must accept being made an Admin or Owner before the change takes effect</p>
                        <?php if(groups_membership_requested_admin($membership, $group)): ?>
                            <p><?php echo $membership->User->name; ?> has requested being an admin</p>
                        <?php endif;?>
                    </div>
                <?php endif;?>
            </div>
        </div>
    
<?php endforeach; ?>


            
</div>