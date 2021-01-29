<div class="well" style="padding: 10px;background:#F0E68C;">
    <h4>Dataset</h4>
    <table class="row-border hover table table-striped tbl-fullwidth table-outside-borders">
        <tr>
            <td>
                <strong>Path</strong>
            </td>
            <td>
                <?php echo htmlentities($datasetPath); ?>
            </td>
        </tr>
        <?php if($scannedByWhen AND is_array($scannedByWhen) AND count($scannedByWhen)==2) : ?>
            <tr>
                <td>
                    <strong>Scanned by</strong>
                </td>
                <td>
                    <?php echo htmlentities($scannedByWhen[0]) . ' (' . date('Y-m-d H:i:s', intval($scannedByWhen[1])) . ')'; ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if($datasetErrors OR $datasetWarnings):?>
        <h4>Errors and warnings</h4>
        <table class="row-border hover table table-striped tbl-fullwidth table-outside-borders ">
            <thead>
                <tr>
                    <th></th>
                    <th><strong>Description</strong></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($datasetErrors as $item): ?>
                <tr>
                    <td style="width:5px;"><i class="glyphicon glyphicon-remove-sign"></i></td>
                    <td>
                        <?php echo htmlentities($item); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach($datasetWarnings as $item): ?>
                <tr>
                    <td style="width:5px;"><i class="glyphicon glyphicon-info-sign"></i></td>
                    <td>
                        <?php echo htmlentities($item); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h4>Comments</h4>
    <table class="row-border hover table table-striped tbl-fullwidth table-outside-borders" data-dataset-id="<?php echo htmlentities($datasetID, ENT_QUOTES) ?>">
        <thead>
            <tr>
                <th><strong>User</strong></th>
                <th><strong>Date</strong></th>
                <th><strong>Comment</strong></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($datasetComments as $item): ?>
        <tr>
            <td style="width:50px;">
                <?php echo htmlentities($item['name']); ?>
            </td>
            <td style="width:135px;">
                <?php echo date('Y-m-d H:i:s',intval($item['time'])); ?>
            </td>
            <td style="margin-right:5px;max-width: 400px;">
                <?php echo htmlentities($item['comment']); ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td style="padding-top:10px;" colspan="3">
                <form action="" method="post" >
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="comments" placeholder="Enter your comment here..." style="width:960px;">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-sm btn-add-comment" type="button">Add comment</button>
                        </span>
                    </div>
                </form>
            </td>
        </tr>
        </tbody>
    </table>

    <br>

    <h4>File information</h4>
    <table id="tree<?php echo $tbl_id?>" class="row-border hover table table-striped tbl-fullwidth table-outside-borders ">
        <thead>
        <tr>
            <th style="width:500px;"><strong>Name</strong></th>
            <th><strong>Errors and warnings</strong></th>
        </tr>
        </thead>
        <tbody>
        <?php $row=0; ?>


        <?php foreach($pathItems as $nodeId=>$item): ?>
            <?php
                // exclude highest level when parent_id AND nodeId is empty.
                // That was added simply for collection toplevel counts (errors, warnings, comments)
                if(!($item->parent_id=='' AND $nodeId=='')){
                  $row++;
            ?>
                    <tr data-tt-id="<?php echo $nodeId; ?>" data-tt-parent-id="<?php echo $item->parent_id ?>"
                        style="vertical-align: top;"
                        >
                        <td valign="top"><?php echo htmlentities($item->name, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td valign="top">
                        <?php
                              // Compilation of errors/warnings
                              $error_messages = '';
                              foreach($item->errors as $error) {
                                  $error_messages .= '<br>' . $error;
                              }
                              $warning_messages = '';
                              foreach($item->warnings as $warning) {
                                  $warning_messages .= '<br>' . $warning;
                              }
                              // Presentation of errors / warnings
                              $newLine = '';
                              if ($error_messages) {
                                  echo "<strong>Error(s)</strong>".$error_messages;
                                  $newLine='<br/>';
                              }
                              if ($warning_messages) {
                                  echo $newLine;
                                  echo "<strong>Warning(s)</strong>".$warning_messages;
                              }
                        ?>
                        </td>
                    </tr>
            <?php } ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
