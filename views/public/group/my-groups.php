<?php
$title = __('My memberships');
echo head(array('title'=>$title, 'bodyclass' => 'groups my-groups'));
?>

<?php 
echo $this->partial('groups-navigation.php');
?>

<h1><?php echo $title; ?></h1>
<?php echo flash(); ?>

<div id='primary'>
    <form method="post">
    <?php if(!(empty($groups) && empty($invitations))) :?>
        <button>Save changes</button>
    <?php endif; ?>
        <?php if(!empty($invitations)): ?>
        <h2>Invitations</h2>
        <?php foreach($invitations as $invitation):  ?>            
        <div class='groups-group'>    
            <h3><a href="<?php echo url('groups/group/show/id/' . $invitation->group_id); ?>"><?php echo $invitation->Group->title; ?></a></h3>
            <div class='group-options'>
                <p><?php echo $invitation->Sender->name; ?> has invited you to join this group. </p>
                <?php if($invitation->message): ?>
                <p class="invite-message">"<?php echo $invitation->message; ?>"</p>    
                <?php endif; ?>

                <?php if( is_allowed($invitation->group, 'join') ): ?>
                    <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' name="invitations[<?php echo $invitation->id ?>]" value='join' />                
                    <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Join</label>
                <?php else: ?>
                    <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' name="invitations[<?php echo $invitation->id ?>]" value='request' />                
                    <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Request Membership</label>
                <?php endif; ?>
                <input type='radio' class='groups-invitation-action invitation-<?php echo $invitation->id ?>' id='invitation-decline-<?php echo $invitation->id; ?>' name="invitations[<?php echo $invitation->id ?>]" value='decline' />                
                <label class='groups' for="invitations[<?php echo $invitation->id ?>]">Decline</label>
                <div class='groups-block-entities' id='groups-block-invitation-<?php echo $invitation->id; ?>'>
                    <input type='checkbox' name="blocks[<?php echo $invitation->id ?>][]" value='block-group' />                
                    <label class='groups' for="blocks[<?php echo $invitation->id ?>][]">Block invitations from this group.</label>
                    <input type='checkbox' name="blocks[<?php echo $invitation->id ?>][]" value='block-user' />                
                    <label class='groups' for="blocks[<?php echo $invitation->id ?>][]">Block invitations from this person.</label>                
                </div>
                                
            </div>
        </div>        
        <?php endforeach; ?>
        <?php endif; ?>
    <?php if(empty($groups)) : ?>
        <p>You are not a member of any groups. Why not <a href="<?php echo url('groups/browse'); ?>">browse for interesting groups</a>?</p>
    <?php else: ?>
        <h2>My Memberships</h2>
    <?php endif; ?>

    <?php foreach(loop('groups') as $group): ?>
        <div class='groups-group'>
        <?php $user_membership = $group->getMembership(array('user_id' => current_user()->id));?>
        <h3><a href="<?php echo url('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title?></a></h3>
  
        
        <div class='group-options'>
            <input type='hidden' name="groups[<?php echo $group->id ?>][submitted]" />
            <div class='group-status'>
                <?php include('role-admin.php'); ?>
            </div>
            <?php include('notifications-admin.php'); ?>
        </div>        
    </div>
    <?php endforeach; ?>

    <?php if(!(empty($groups) && empty($invitations))) :?>
        <button>Save changes</button>
    <?php endif; ?>
    </form>

</div>


<?php echo foot(); ?>