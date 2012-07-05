<?php
head(array());
?>
<?php include 'groups-manage-tabs.php' ; ?>

<div id='primary'>
<?php if(empty($groups)) :?>
<p>You do not have permission to administer any groups.</p>
<?php else: ?>
<form method="post">
    <?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>    
        <div class='groups-group'>
            <?php $group = groups_get_current_group(); ?>
            <h3><?php echo $group->title; ?></h3>
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
                    <?php if(has_permission($group, 'changeStatus')): ?>
                        <label for="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]">Status</label>
                        <input name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='owner' />Owner
                        <input <?php if($membership->is_admin) {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='admin' />Admin
                        <input <?php if(!$membership->is_admin) {echo "checked='checked'"; } ?> name="status[<?php echo $group->id; ?>][<?php echo $membership->id; ?>]" type='radio' value='member' />Member
                        <p class='explanation'>Users must accept being made an Admin or Owner before the change takes effect</p>
                    <?php endif;?>
                </div>
            </div>
            
            <?php endforeach; ?>
            
        </div>
    <?php endwhile; ?>
    <button>Submit</button>
</form>
<?php endif; ?>
</div>

<?php foot(); ?>