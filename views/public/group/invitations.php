<?php
head(array());
?>
<?php include 'groups-manage-tabs.php' ; ?>


<div id='primary'>
<?php if(empty($groups)) :?>
<p>You do not have permission to invite people to any of your groups.</p>
<?php else: ?>
<form method="post">
<div>
    <label for='emails'>Email addresses of people to invite to join groups (comma-separated)</label>
    <input type='text' name='emails' />
</div>
<div>
    <label for='invite_groups[]'>Groups to invite the above people to</label>
    <?php foreach($groups as $group): ?>
        <input name='invite_groups[]' value='<?php echo $group->id; ?>' type='checkbox'/><?php echo $group->title; ?>
    
    <?php endforeach; ?>
</div>

<div>
    <label for='message'>Message</label>
    <textarea rows='6' cols='40' name='message'></textarea>
</div>

<button class='submit'>Submit</button>
</form>
<?php endif; ?>
</div>





<?php foot(); ?>