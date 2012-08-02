<?php
head(array());
?>
<?php include 'groups-manage-tabs.php' ; ?>
<div id='primary'>
    <form method="post">
    <?php if(!(empty($groups) && empty($invitations))) :?>
        <button>Submit</button>
    <?php endif; ?>
    <?php if(count($invitations) != 0): ?>
        <h2>Invitations</h2>
        <?php foreach($invitations as $invitation):  ?>            
        <div class='groups-group'>    
            <h3><a href="<?php echo uri('groups/group/show/id/' . $invitation->group_id); ?>"><?php echo $invitation->Group->title; ?></a></h3>
            <div class='group-options'>
                <p><?php echo $invitation->Sender->name; ?> has invited you to join this group: </p>
                <p><?php echo $invitation->message; ?></p>    
                <?php if( has_permission($invitation->Group, 'join') ): ?>
                    <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' name="invitations[<?php echo $invitation->id ?>]" value='join' />                
                    <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Join</label>
                <?php else: ?>
                    <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' name="invitations[<?php echo $invitation->id ?>]" value='request' />                
                    <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Request Membership</label>
                <?php endif; ?>
                <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' id='invitation-decline-<?php echo $invitation->id; ?>' name="invitations[<?php echo $invitation->id ?>]" value='decline' />                
                <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Decline</label>
                <div class='group-block-invitations' id='groups-block-invitation-<?php echo $invitation->id; ?>'>
                    <input type='checkbox' name="blocks[<?php echo $invitation->id ?>][]" value='block-group' />                
                    <label class='groups' for="blocks[<?php echo $invitation->id ?>][]">Block invitations from this group.</label>
                    <input type='checkbox' name="blocks[<?php echo $invitation->id ?>][]" value='block-user' />                
                    <label class='groups' for="blocks[<?php echo $invitation->id ?>][]">Block invitations from this person.</label>                
                </div>
                                
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
        <?php $user_membership = groups_get_membership(); ?>
        <h3><a href="<?php echo uri('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title?></a></h3>

        
        <div class='group-options'>
            <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />
            <div class='group-status'>
                <h4>Membership and Role</h4>
                <?php include('role-admin.php'); ?>
            </div>

            <?php include('notifications-admin.php'); ?>
        </div>        
    </div>
    <?php endwhile; ?>
    <?php if(!(empty($groups) && empty($invitations))) :?>
        <button>Submit</button>
    <?php endif; ?>
    </form>

</div>


<?php foot(); ?>