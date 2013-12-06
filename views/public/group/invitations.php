<?php
$title = __('Group invitations');
echo head(array('title'=>'Group Invitations', 'bodyclass' => 'groups invitations'));
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
    <div class="emails">
        <label for='emails'><?php echo __('Email addresses of people to invite to join groups (comma-separated)'); ?></label>
        <textarea rows='6' cols='40' type='text' name='emails'></textarea>
    </div>
    <div class="groups">
        <label for='invite_groups[]'><?php echo __('Groups to invite the above people to'); ?></label>
        <?php foreach($groups as $group): ?>
            <div class="group">
                <input name='invite_groups[]' value='<?php echo $group->id; ?>' type='checkbox'/><?php echo $group->title; ?>
            </div>
        
        <?php endforeach; ?>
    </div>
    
    <div class="message">
        <label for='message'><?php echo __('Message'); ?></label>
        <textarea rows='6' cols='40' name='message'></textarea>
    </div>
    
    <button class='submit'><?php echo __('Send group invitations'); ?></button>
</form>
<?php endif; ?>
</div>
<?php echo foot(); ?>