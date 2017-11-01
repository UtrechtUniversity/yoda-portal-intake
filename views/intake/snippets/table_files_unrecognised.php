<?php $totalErrorCount = count($dataErroneousFiles);
      $totalUnscannedCount =  $totalFileCount - $totalDatasetFiles - $totalErrorCount;
?>

<h2>
    Unrecognised<?php echo $totalErrorCount ?  '(' . $totalErrorCount . ') ':''; ?>
    and unscanned<?php echo ($totalUnscannedCount>0) ?  '(' . $totalUnscannedCount . ') ':'(-)'; ?>
    files
</h2>
<table id="datatable_unrecognised" class="row-border hover table table-striped" style="width:100%">
    <thead>
    <tr>
        <th>Name</th>
        <th>Date</th>
        <th>Pseudocode</th>
        <th>Experiment type</th>
        <th>Wave</th>
        <th>Version</th>
        <th>Status</th>
        <th>Created by</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($dataErroneousFiles as $row): ?>
        <tr data-filename="<?php echo htmlentities($row->name, ENT_QUOTES, 'UTF-8'); ?>"
            data-path="<?php echo htmlentities($row->path,ENT_QUOTES,'UTF-8'); ?>"
            data-error="<?php echo htmlentities($row->error, ENT_QUOTES) ?>"
            >
            <td style="max-width:200px;"><div class="ellipseDivText"><?php echo htmlentities($row->name,ENT_QUOTES, 'UTF-8'); ?></div></td>
            <td><?php echo date('Y-m-d',intval($row->date)); ?></td>
            <td><?php echo htmlentities($row->pseudocode) ?></td>
            <td><?php echo htmlentities($row->experiment_type) ?></td>
            <td><?php echo htmlentities($row->wave) ?></td>
            <td><?php echo htmlentities($row->version) ?></td>
            <td>Unrecognised</td>
            <td><?php echo htmlentities($row->creator) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
