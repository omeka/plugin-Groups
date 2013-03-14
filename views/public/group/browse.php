<?php
echo head(array('title'=>'Browse Groups', 'bodyclass' => 'browse'));
?>


<div id='primary'>
<?php if(is_allowed('Groups_Group', 'add')): ?>
<p><a href='<?php echo url('/groups/add'); ?>'>Add a group</a></p>
<?php endif; ?>

<h1>All Tags</h1>
<?php echo tag_cloud($this->tags, 'browse'); ?>

<div id="pagination-top" class="pagination"><?php echo pagination_links(); ?></div>
<div style="clear:left;"></div>
<h1>Groups</h1>
<?php foreach(loop('groups') as $group):  ?>
<div class="hentry">
<h2><?php echo groups_link_to_group(); ?></h2>
<p class='groups-type'>Type: <?php echo groups_group('visibility'); ?>
<?php echo groups_group_visibility_text(); ?>
</p>
<h3>Description</h3>
<div class='groups-description'><?php echo groups_group('description'); ?></div>

<?php $group_tags = groups_tags_list_for_group($group); ?>

<?php if(!empty($group_tags)) :?>
<h3>Tags</h3>
<div class='groups-tags'>
<?php echo $group_tags; ?>
</div>
<?php endif;?>

<p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
<p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>
<?php endforeach; ?>

</div>


<?php echo foot(); ?>