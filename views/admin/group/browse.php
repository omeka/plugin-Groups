<?php
echo head(array('title' => 'Groups'));
?>

<div id='primary'>
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
                <a href="<?php echo url('groups/group/show/id/' . $group->id); ?>"><?php echo $group->title; ?></a>
                <ul class="action-links group">
                    <li><a href="<?php echo url('groups/group/edit/id/' . $group->id); ?>">Edit</a></li>
                </ul>
            </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<?php echo foot(); ?>