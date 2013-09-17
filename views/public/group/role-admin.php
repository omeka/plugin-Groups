<?php $adminConfirm = groups_role_confirm($group, $user_membership, 'is_admin'); ?>
<?php $ownerConfirm = groups_role_confirm($group, $user_membership, 'is_owner'); ?>
<?php $role = metadata($user_membership, 'role'); ?>
<?php $role_preceder = ($role == 'Owner' || $role == 'Admin') ? "an" : "a"; ?>

<?php if($adminConfirm || $ownerConfirm ): ?>
    <label class='groups' for="groups[<?php echo $group->id ?>][role]"><?php echo __('Group role:')?> </label>
    <p>You are <?php echo $role_preceder; ?> <span class="role"><?php echo strtolower($role); ?></span> of this group.</p>
    <?php if($adminConfirm) :?>
        <p>An administrator of this group has asked you to become an administrator. Check here to accept.</p>
        <input type='radio' value='is_admin' name="groups[<?php echo $group->id ?>][admin]" />Accept
        <input type='radio' value='decline' name="groups[<?php echo $group->id ?>][admin]" />Decline                                                        
    <?php endif; ?>
    <?php if( $ownerConfirm ) :?>
        <p>The owner of this group would like to transfer ownership to you. Check here to accept.</p>
        <input type='radio' value='is_owner' name="groups[<?php echo $group->id ?>][owner]" />Accept 
        <input type='radio' value='decline' name="groups[<?php echo $group->id ?>][owner]" />Decline          
    <?php endif; ?>

<?php else: ?>
    <p>I am <?php echo $role_preceder; ?> <span class="role"><?php echo strtolower($role); ?></span> of this group.</p>
    <?php if($role == 'Admin'): ?>
        <input type='checkbox' value='decline' name="groups[<?php echo $group->id ?>][admin]" />Stop being an admin
    <?php endif; ?>
<?php endif; ?>            

<?php if($role == 'Owner'): ?>
    <?php $memberships = $group->getMemberships(); ?>
    <?php if(count($memberships) > 1): ?>
        <p>You are the owner of this group. You can transfer ownership below.</p>
    <?php endif;?>
<?php else: ?>
    <input type='checkbox' value='quit' name="groups[<?php echo $group->id ?>][status]" />Leave
    <?php if(!$adminConfirm && $role !='Admin'): ?>  
        <input type='checkbox' value='make_admin' name="groups[<?php echo $group->id ?>][status]" />Request admin privileges
    <?php endif; ?>  
<?php endif; ?>