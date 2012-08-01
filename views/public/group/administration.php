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
            <h3><a href="<?php echo uri('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title?></a></h3>

            <?php include('membership-admin.php'); ?>
                      
            
        </div>
    <?php endwhile; ?>
    <button>Submit</button>
</form>
<?php endif; ?>
</div>

<?php foot(); ?>