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