<?php

$css = "
tr.flagged td {background-color: pink}
";
queue_css_string($css);
queue_js_file('groups');
echo head(array('title' => 'Groups'));
?>

<div id='primary'>
<?php echo pagination_links(); ?>
    <table>
        <thead>
        <tr><th>Group</th><th>Visibility</th></tr>
        </thead>
        <tbody>
            <?php $key = 0; ?>
            <?php foreach(loop('group') as $group):?>

            <tr class="item <?php if(++$key%2==1) echo 'odd'; else echo 'even'; ?> <?php if($group->flagged ==1) echo 'flagged'  ?>">
            <?php if ($group->featured): ?>
            <td class="featured">
            <?php else: ?>
            <td>
            <?php endif; ?>
                <a href="<?php echo url('groups/group/show/id/' . $group->id); ?>"><?php echo metadata($group, 'title'); ?></a>
                <ul class="action-links group">
                    <li><a href="<?php echo url('groups/group/edit/id/' . $group->id); ?>">Edit</a></li>
                    <li><a href="<?php echo url('groups/group/delete-confirm/id/' . $group->id); ?>">Delete</a></li>
                    <?php if($group->flagged): ?>
                    <li class="flagged" style="color: rgb(78, 113, 129); cursor: pointer;" id="group-<?php echo $group->id; ?>">Unflag</li>
                    <?php else: ?>
                    <li class="flag" style="color: rgb(78, 113, 129); cursor: pointer;" id="group-<?php echo $group->id; ?>">Flag</li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo url('groups/group/feature/id/' . $group->id); ?>">Feature</a></li>
                </ul>
            </td>
            <td>
            <?php echo metadata($group, 'visibility'); ?> 
            </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php echo pagination_links(); ?>
</div>


<?php echo foot(); ?>