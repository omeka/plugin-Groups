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
    <h1>Groups (<?php echo total_records('Group'); ?> total)</h1>
    <div class="groups">
    <?php foreach(loop('groups') as $group):  ?>
        <div class="group hentry">
        
        <h2><?php echo link_to($group, 'show', $group->title); ?></h2>
        <?php $visibility = metadata($group, 'visibility'); ?>
        <?php if($visibility !== "Open"): ?>
        <div class='groups-type <?php echo strtolower($visibility); ?>'>
            <span class="visibility" title="<?php echo $group->visibilityText(); ?>"><?php echo $visibility; ?></span>
            <span class="visibility-description"><?php echo $group->visibilityText(); ?></span>
        </div>
        <?php endif; ?>
        <?php if($group->flagged): ?>
        <div class='groups-type flagged'>Flagged</div>
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
        
        <p class="items"><span class="number"><?php echo metadata($group, 'items_count'); ?></span> items</p>
        <p class="members"><span class="number"><?php echo metadata($group, 'members_count'); ?></span> members</p>
        
        <?php echo $this->manageGroup($group); ?>
        
        </div>
    <?php endforeach; ?>
    </div>

<div class="tags">
    <h1>All Tags</h1>
    <?php echo tag_cloud($this->tags, 'groups/browse'); ?>
</div>

</div>
<?php echo foot(); ?>