<?php foreach($blocked_users as $blocked):?>
<p>Unblock</p>

<p>
    <input type='checkbox' name="unblocks[<?php echo $group->id; ?>]" value="<?php echo $blocked->Blocked->id; ?>">
    <?php echo "{$blocked->Blocked->name} ({$blocked->Blocked->username})"; ?>
</p>
<?php endforeach; ?>