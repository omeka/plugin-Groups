
<div>
<?php 
$memberships = $group->getMemberships();
foreach($memberships as $membership):
?>
    <?php 
        $current_user = current_user();                
        if($group->visibility != 'private' && (count($memberships) == 1)): ?>
            <div class='group-membership'>
            <p>There are no members in your group! Why not <a href="<?php echo url('groups/invitations'); ?>">invite some friends</a>?</p>
            
            </div>
        <?php endif; ?>
        <?php if($membership->user_id == $current_user->id) {continue;} ?>
        <div class='groups-admin-actions'>
    
            <h4><?php echo $membership->User->name; ?></h4>
            <div class='group-options'>
                <?php if(metadata($membership, 'role') == 'Owner'): ?>
                    <div class='group-options'>
                    <p><?php echo __('(Owner)'); ?></p>                    
                    </div>

                    <?php else: ?>
                    <label for="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]"><?php echo __('Change membership status:'); ?> </label>
                    <?php if($membership->is_pending) : ?>
                        <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='approve' />Approve
                        <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='deny' />Deny
                    <?php else: ?>
                        <input class='groups-membership-options' name="membership[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='checkbox' value='remove' />Remove
                    <?php endif; ?>
                        <div class="groups-block-entities">
                            <input name="block[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='checkbox' value='block' />Block                    
                        </div>
                    <?php if(is_allowed($group, 'change-status')): ?>
                        <div class='groups-role-change <?php if($membership->is_pending) {echo "pending";}?>'>
                            <?php $role = metadata($membership, 'role');  ?>
                            <label for="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]"><?php echo __('Group role:'); ?><span title='Users must accept being made an Admin or Owner before the change takes effect.'>*</span> </label>
                            <?php if(is_allowed($group, 'make-owner')): ?>
                                <input <?php if($role == 'Owner') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='owner' />Owner
                            <?php endif; ?>
                            <input <?php if($role == 'Admin') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='admin' />Admin
                            <input <?php if($role == 'Member') {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='member' />Member
                            <?php if(groups_membership_requested_admin($membership, $group)): ?>
                                <p><?php echo $membership->User->name; ?> has requested being an admin</p>
                            <?php endif;?>
                        </div>                    
                    <?php endif;?>
                <?php endif;?>
            </div>
        </div>
<?php endforeach; ?>
</div>