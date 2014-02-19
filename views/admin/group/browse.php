<?php
echo head(array('title' => 'Groups'));
?>

<div id='primary'>
<?php echo pagination_links(); ?>
    <table>
        <thead>
        <tr><th>Group</th></tr>
        </thead>
        <tbody>
            <?php $key = 0; ?>
            <?php foreach(loop('group') as $group):?>

            <tr class="item <?php if(++$key%2==1) echo 'odd'; else echo 'even'; ?>">
            <?php if ($group->featured): ?>
            <td class="featured">
            <?php else: ?>
            <td>
            <?php endif; ?>
                <a href="<?php echo url('groups/group/show/id/' . $group->id); ?>"><?php echo metadata($group, 'title'); ?></a>
                ( <?php echo metadata($group, 'visibility'); ?> )
                <ul class="action-links group">
                    <li><a href="<?php echo url('groups/group/edit/id/' . $group->id); ?>">Edit</a></li>
                </ul>
            </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php echo pagination_links(); ?>
</div>


<?php echo foot(); ?>