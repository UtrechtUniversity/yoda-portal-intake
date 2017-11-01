<?php
// quick trick!! // @todo; in translation file!!
    $translate['totalDatasets']='Total datasets';
    $translate['totalFiles']='Total files';
    $translate['totalFileSize']='Total file size';
    $translate['totalFileSizeMonthGrowth']='File size growth in a month';
    $translate['datasetsMonthGrowth']='Datasets growth in a month';
    $translate['distinctPseudoCodes']='Pseudocodes';

    // presentable errors
    $lang['ACCESS_INVALID_STUDY']	    = 'This is an invalid study!';
    $lang['ACCESS_NO_ACCESS_ALLOWED']	= 'Access is not allowed for this study!';
    $lang['ACCESS_INVALID_FOLDER']	= 'This is an invalid folder for this study!';
    $lang['ACCESS_NO_DATAMANAGER']	= 'You have no datamanager rights on any study!';

?>
    <?php if($error != '' ): ?>
        <div class="row">
            <div class="alert alert-danger">
                <button type="button" class="close" data-hide="alert">&times;</button>
                <div class="info_text">
                    <?php echo htmlentities($lang[$error]);  ?>
                        <br/>
                        <br/>
                        <?php if($this->studies): ?>
                            Following studies are accessible for you:
                            <ul>
                                <?php foreach($this->studies as $study): ?>
                                    <li>
                                        <a href="<?php echo site_url("intake"); ?>/reports/index/<?php echo $study; ?>">Go to study <?php echo $study; ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            Click <a href="<?php echo base_url() ?>intake">here</a> to go to an area that is accessible for you.
                        <?php endif; ?>
                </div>
            </div>
        </div>

    <?php else: ?>

    <div class="row page-header">
        <div class="col-sm-6">
            <h1>
                <span class="glyphicon glyphicon-folder-open"></span>
                <?php echo htmlentities($title); ?>
            </h1>
            <?php echo htmlentities($intakePath . ($studyFolder?'/'.$studyFolder:'')); ?>
        </div>
    </div>

    <div class="row" id="toprow">
        <div class="btn-group">
            <button class="btn  btn-small dropdown-toggle btn-default" data-toggle="dropdown">
                <span class="glyphicon glyphicon-education"></span> Change study <span class="caret"></span>
            </button>
            <div class="dropdown-menu" style="width:300px;padding:5px;">
                Please select a study for your Yoda Intake Area:
                <br/>
                <br/>
                <table class="table table-striped hover" id="select-study">
                    <?php foreach($studies as $study): ?>
                        <tr data-study-url="<?php echo site_url().'intake/reports/index/'.urlencode($study) ?>" style="cursor:pointer;">
                            <td >
                                <span class="glyphicon glyphicon-education pull-left"></span>
                            </td>
                            <td>
                                    <span>
                                        <?php echo htmlentities($study) ?>
                                    </span>
                            </td>
                            <td style="width:10px;">
                                <?php if($study==$studyID): ?>
                                    <span class="glyphicon glyphicon-ok"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="btn-group">
            <a class="btn btn-small btn-default" href="<?php echo site_url().'intake/export/download/'.urlencode($studyID) ?>" target="_blank">
                <span class="glyphicon glyphicon-export"></span> Export data</span>
            </a>
        </div>
        <div class="btn-group">
            <a href="<?php echo base_url('intake/intake'); ?>" class="btn btn-info"><span class="glyphicon glyphicon-<?php echo $moduleGlyph; ?>"></span> Intake</a>
        </div>

    </div>
    <div class="row">
        <div class="col-xs-6">
            <h1>Raw</h1>
            <table width="100%" class="table-striped">
                <thead>
                <tr>
                    <td><strong>Type</strong></td>
                    <td><strong>Wave</strong></td>
                    <td><strong>Count</strong></td>
                </tr>
                </thead>
                <tbody>
                <?php
                    $counter = 0;
                    foreach($datasetTypeCounts as $type=>$data){
                        foreach($data as $wave=>$info){
                            foreach($info as $version=>$count){
                                if($version=='raw') {
                                    $counter++;
                                    echo "<tr>";
                                        echo "<td>" . $type . "</td>";
                                        echo "<td>" . $wave . "</td>";
                                        echo "<td>" . $count . "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                    }
                ?>
                </tbody>
                <?php if(!$counter) { echo "<i>No data found.</i>"; } ?>
            </table>
        </div>
        <div class="col-xs-6">
            <h1>Processed</h1>
            <table width="100%" class="table-striped">
                <thead>
                <tr>
                    <td><strong>Type</strong></td>
                    <td><strong>Version</strong></td>
                    <td><strong>Wave</strong></td>
                    <td><strong>Count</strong></td>
                </tr>
                </thead>
                <tbody>
                <?php
                    $counter = 0;
                    foreach($datasetTypeCounts as $type=>$data){
                        foreach($data as $wave=>$info){
                            foreach($info as $version=>$count){
                                if($version!='raw') {
                                    $counter++;
                                    echo "<tr>";
                                        echo "<td>" . $type . "</td>";
                                        echo "<td>" . $version . "</td>";
                                        echo "<td>" . $wave . "</td>";
                                        echo "<td>" . $count . "</td>";
                                    echo "</tr>";
                                }
                            }
                        }
                    }
                ?>
                </tbody>
            </table>
            <?php if(!$counter) { echo "<i>No data found.</i>"; } ?>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-4">
            <h1>Raw</h1>
            <table class="table-striped">
                <tbody>
                <?php foreach($aggregatedInfo as $version=>$rowdata): ?>
                    <?php if(strtolower($version)=='raw'): ?>
                        <?php foreach($rowdata as $descr=>$data): ?>
                            <tr>
                                <td style="width:250px;"><?php echo htmlentities($translate[$descr]); ?></td>
                                <td align="right"><?php echo htmlentities($data); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-4">
            <h1>Processed</h1>
            <table class="table-striped">
                <tbody>
                <?php foreach($aggregatedInfo as $version=>$rowdata): ?>
                    <?php if($version=='notRaw'): ?>
                        <?php foreach($rowdata as $descr=>$data): ?>
                            <tr>
                                <td style="width:250px;"><?php echo htmlentities($translate[$descr]); ?></td>
                                <td align="right"><?php echo htmlentities($data); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-4">
            <h1>Total</h1>
            <table class="table-striped">
                <tbody>
                <?php foreach($aggregatedInfo as $version=>$rowdata): ?>
                    <?php if($version=='total'): ?>
                        <?php foreach($rowdata as $descr=>$data): ?>
                            <tr>
                                <td style="width:250px;"><?php echo htmlentities($translate[$descr]); ?></td>
                                <td align="right"><?php echo htmlentities($data); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>