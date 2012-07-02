<?php
head(array());
?>
<?php include 'groups-manage-tabs.php' ; ?>
<div id='primary'>
    <form method="post">
    <?php if(count($invitations) != 0): ?>
        <h2>Invitations</h2>
        <?php foreach($invitations as $invitation):  ?>            
        <div class='groups-group'>    
            <h3><a href="<?php echo uri('groups/group/show/id/' . $invitation->group_id); ?>"><?php echo $invitation->Group->title; ?></a></h3>
            <div class='group-options'>
                <p><?php echo $invitation->Sender->name; ?> has invited you to join this group: </p>
                <p><?php echo $invitation->message; ?></p>    
                <?php if( has_permission($invitation->Group, 'join') ): ?>
                <input type='checkbox' name="invitations[<?php echo $invitation->id ?>][join]" />                
                <label class='groups' for="invitations[<?php echo $invitation->id ?>][join]">Join</label>
                <?php else: ?>
                <input type='checkbox' name="invitations[<?php echo $invitation->id ?>][request]" />                
                <label class='groups' for="invitations[<?php echo $invitation->id ?>][join]">Request Membership</label>
                <?php endif; ?>
            </div>
        </div>        
        <?php endforeach; ?>
    <?php endif; ?>
    <h2>My Groups</h2>
    <?php if(empty($groups)) : ?>
    <p>You are not a member of any groups. Why not <a href="<?php echo uri('groups/browse'); ?>">browse for interesting groups</a>?</p>
    <?php endif; ?>
    <?php while(loop_records('groups', $groups, 'groups_set_current_group')):  ?>
    
    <div class='groups-group'>
        <?php $group = groups_get_current_group(); ?>
        <?php $membership = groups_get_membership(); ?>
        <h3><?php echo $group->title; ?></h3>

        
        <div class='group-options'>
            <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />
            <div class='group-status'>
                <h4>Membership and Role</h4>
                <?php if($membership->is_owner): ?>
                    <p>You are the owner of this group. You can transfer ownership in the <a href="<?php echo uri('groups/administration'); ?>">administration page</a>.</p>
                
                <?php else: ?>
                    <label class='groups' for="groups[<?php echo $group->id ?>][quit]">Membership</label>
                    <input type='checkbox' name="groups[<?php echo $group->id ?>][quit]" />Leave<br/>                
                <?php endif; ?>
                                

                <?php $adminConfirm = groups_role_confirm($group, $membership, 'is_admin'); ?>
                <?php $ownerConfirm = groups_role_confirm($group, $membership, 'is_owner'); ?>
                <?php if($adminConfirm || $ownerConfirm || $membership->is_admin ): ?>
                    <label class='groups' for="groups[<?php echo $group->id ?>][role]">Role</label>
                    <?php if( $adminConfirm || ($membership->is_admin === 1) ) :?>
                        <input checked='checked' type='checkbox' value='is_admin' name="groups[<?php echo $group->id ?>][role]" />Admin
                        <?php if($adminConfirm): ?>
                            <p>An administrator of this group has asked you to become an administrator. Check here to accept.</p>
                        <?php endif; ?>                                        
                    <?php endif; ?>
                    <?php if( $ownerConfirm ) :?>
                        <input checked='checked' type='checkbox' value='is_admin' name="groups[<?php echo $group->id ?>][role]" />Owner
                        <p>The owner of this group would like to transfer ownership to you. Check here to accept.</p>
                    <?php endif; ?>

                <?php endif; ?>            
            </div>

            <div class='group-notifications'>
                <h4>Send email notifications to me when:</h4>
                <input <?php if($membership->notify_member_joined) {echo "checked='checked'"; }?> type='checkbox' name="groups[<?php echo $group->id ?>][notify_member_joined]" />
                <label class='groups' for="groups[<?php echo $group->id ?>][notify_member_joined]">New Members Join</label>

                <input <?php if($membership->notify_member_left) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_member_left]" />
                <label class='groups' for="groups[<?php echo $group->id ?>][notify_member_left]">A Member Leaves</label>

            
                <input  <?php if($membership->notify_item_new) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_item_new]" />                
                <label class='groups' for="groups[<?php echo $group->id ?>][notify_item_new]">New Items Are Added</label>
                
            
                <input  <?php if($membership->notify_item_deleted) {echo "checked='checked'"; }?>  type='checkbox' name="groups[<?php echo $group->id ?>][notify_item_deleted]" />                
                <label class='groups' for="groups[<?php echo $group->id ?>][notify_item_deleted]">Items Are Deleted</label>                                                
            </div>
        </div>        
    </div>
    <?php endwhile; ?>
    <?php if(!(empty($groups) && empty($invitations))) :?>
    <button>Submit</button>
    <?php endif; ?>
    </form>

</div>


<?php foot(); ?>