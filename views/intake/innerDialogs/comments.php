<?php if ($saveSuccess) { ?>

<script type="text/javascript">

    // refill table: window.parent.servant.area.reloadLabGasRows();

    window.parent.$('.modal').modal('hide');
</script>

<?php } else { ?>

<form action="" method="post" >
    <div class="control-group">
        <div class="control-label"><strong>Add comment</strong></div>
        <input type="text" class="form-control" name="comment" value="" placeholder="Enter your comment here">

    </div>
    <div class="pull-right">
        <br/>
        <div class="btn-group ">
            <button type="button" class="btn btn-default pull-right" onclick="window.parent.$('.modal').modal('hide');">
                Cancel
            </button>
        </div>
        <div class="btn-group ">
            <input type="submit" class="btn btn-primary btn-default pull-right" value="Add comment" />
        </div>
    </div>

    <table class="table row-border hover table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Date</th>
                <th>Comment</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($comments as $comment): ?>
                <tr>
                    <td><?php echo htmlentities($comment->user) ?></td>
                    <td><?php echo htmlentities($comment->timestamp) ?></td>
                    <td><?php echo htmlentities($comment->comment) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</form>

<?php }
