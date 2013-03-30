<?php
$title = __('Group invitations');
echo head(array('title'=>'Group Invitations'));
?>
<?php 
echo $this->partial('groups-navigation.php');
?>

<h1><?php echo $title; ?></h1>
<?php echo flash(); ?>
<div id='primary'>
<?php if(empty($groups)) :?>
<p><?php echo __('You do not have permission to invite people to any of your groups.'); ?></p>
<?php else: ?>
<form method="post">
    <div>
        <label for='emails'><?php echo __('Email addresses of people to invite to join groups (comma-separated)'); ?></label>
        <input type='text' name='emails' />
    </div>
    <div>
        <label for='invite_groups[]'><?php echo __('Groups to invite the above people to'); ?></label>
        <?php foreach($groups as $group): ?>
            <input name='invite_groups[]' value='<?php echo $group->id; ?>' type='checkbox'/><?php echo $group->title; ?>
        
        <?php endforeach; ?>
    </div>
    
    <div>
        <label for='message'><?php echo __('Message'); ?></label>
        <textarea rows='6' cols='40' name='message'></textarea>
    </div>
    
    <button class='submit'><?php echo __('Submit'); ?></button>
</form>
<?php endif; ?>
</div>
<?php echo foot(); ?>