<?php
queue_js_file('groups');
echo head(array('title'=>'Browse Groups', 'bodyclass' => 'groups browse'));
?>

<?php echo $this->partial('group-manage-nav.php'); ?>

<?php echo $this->groupSearchFilters(); ?>
<div id='primary'>
    <?php if(get_option('groups_taggable')): ?>
    
    <h1>All Tags</h1>
    <?php echo tag_cloud($this->tags, 'groups/browse'); ?>
    <?php endif; ?>
    <div id="pagination-top" class="pagination"><?php echo pagination_links(); ?></div>
    <div style="clear:left;"></div>
    <h1>Groups</h1>
    
    <?php foreach(loop('groups') as $group):  ?>
        <div class="hentry">
        
        <h2><?php echo link_to($group, 'show', $group->title); ?></h2>
        
        <p class='groups-type'>Type: <?php echo metadata($group, 'visibility'); ?>
        <?php echo $group->visibilityText(); ?>
        </p>
        <?php if($user && $group->visibility == 'open' && !$group->hasMember($user) && !$group->hasPendingMember($user)): ?>
            <p class='groups-join-button groups-button' id='groups-id-<?php echo $group->id; ?>'>Join</p>
        <?php endif; ?>
        
        <h3>Description</h3>
        <div class='groups-description'><?php echo $group->description; ?></div>
        
        <?php if(get_option('groups_taggable')): ?>
            <?php $group_tags = groups_tags_list_for_group($group); ?>
        
            <?php if(!empty($group_tags)) :?>
            <h3>Tags</h3>
                <div class='groups-tags'>
                <?php echo $group_tags; ?>
                </div>
            <?php endif;?>
        <?php endif; ?>
        
        <p id='groups-member-count'>Members: <?php echo metadata($group, 'members_count'); ?></p>
        <p id='groups-item-count'>Items: <?php echo metadata($group, 'items_count'); ?></p>
        
        </div>
    <?php endforeach; ?>

<div class="tags">
    <h1>All Tags</h1>
    <?php echo tag_cloud($this->tags, 'groups/browse'); ?>
</div>

</div>
<?php echo foot(); ?>