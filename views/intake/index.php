<?php
    // presentable errors
    $lang['ACCESS_INVALID_STUDY']	    = 'This is an invalid study!';
    $lang['ACCESS_NO_ACCESS_ALLOWED']	= 'Access is not allowed!';  /// dit gaat over de studie
    $lang['ACCESS_INVALID_FOLDER']	= 'This is an invalid folder for this study!';
    $lang['ACCESS_NO_DATAMANAGER']	= 'You have no datamanager rights on any study!';

    // Data processing situations
    $lang['SCAN_OK']		= 'Scanning finished successfully!';
    $lang['SCAN_NOK']		= 'Something went wrong during the scanning process!';
    $lang['LOCK_OK']		= 'The selected files were locked successfully!';
    $lang['LOCK_NOK']	    = 'Something went wrong during the locking process!';
    $lang['UNLOCK_OK']		= 'The selected files were unlocked successfully!';
    $lang['UNLOCK_NOK'] 	= 'Something went wrong during the unlocking process!';
    $lang['VAULT_OK']		= 'The files were transported to the vault successfully!';
    $lang['VAULT_NOK']	    = 'Something went wrong during transportation to the vault!';
?>

    <?php echo form_open('intake'); ?>
    <?php echo form_close(); ?>

    <div class="modal fade" id="dialog-ok" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content" >
                <div class="modal-header">
                    <h3 class="no-offset"></h3>
                </div>
                <div class="modal-body">
                    <span class="glyphicon glyphicon-info-sign"></span>
                    <div class="col-sm-10 pull-right">
                        <span class="item"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <span class="glyphicon glyphicon-ok"></span> OK
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php  $alertData = $this->session->userdata('alertOnPageReload');
        $this->session->unset_userdata('alertOnPageReload');
?>

<?php if (!$alertData): ?>
    <div class="row page-header" style="margin-top:0px;">
        <div class="col-sm-6">
                <h1>
                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                    <?php echo htmlentities($title); ?>
                </h1>
                <?php echo htmlentities($intakePath . ($studyFolder?'/'.$studyFolder:'')); ?>
        </div>
        <div class="col-sm-6">
            <div class="progress_indicator" style="display:none;">
                <h1 class="pull-right">
                    Scanning process in progress...
                </h1>
                <img class="h1 pull-right" src="<?php echo base_url($this->router->module); ?>/static/images/ajax-loader.gif" style="height:30px;">
            </div>
        </div>
    </div>
<?php endif; ?>

<?php // kept seperately as this are maybe not mutually exclusive and this might change in the future ?>

<?php if($alertData): ?>
    <div class="row">
        <div class="alert alert-<?php echo $alertData->alertType; ?>">
            <button type="button" class="close" data-hide="alert">&times;</button>
            <div class="info_text">
                <?php echo htmlentities($lang[$alertData->alertNr]);  ?>
                <?php if($alertData->alertSubNr): ?>
                    <br/>
                    Error code: <?php echo $alertData->alertSubNr; ?>
                <?php endif;  ?>
            </div>
        </div>

    </div>
<?php endif; ?>

<?php if (!isset($alertData->alertNr) || substr($alertData->alertNr,0,6)!='ACCESS'): ?>

    <div class="row" id="toprow">
        <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle btn-default" data-toggle="dropdown">
                <i class="fa fa-graduation-cap" aria-hidden="true"></i> Change study <span class="caret"></span>
            </button>
            <div class="dropdown-menu" style="width:300px;padding:5px;">
                Please select a study for your Yoda Intake Area:
                <br/>
                <br/>
                <table class="table table-striped hover" id="select-study">
                    <?php foreach($studies as $study): ?>
                        <tr data-study-url="<?php echo site_url().'intake/index/'.urlencode($study) ?>">
                            <td >
                                <i class="fa fa-graduation-cap" aria-hidden="true"></i>
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
        <?php if($selectableScanFolders): ?>
            <div class="btn-group">
                <button class="btn  btn-outline-secondary dropdown-toggle btn-default" data-toggle="dropdown">
                    <i class="fa fa-folder-open" aria-hidden="true"></i> Change folder <span class="caret"></span>
                </button>
                <div class="dropdown-menu" style="width:300px;padding:5px;">
                    Please select a folder:
                    <br/>
                    <br/>
                    <table class="table table-striped hover" id="select-study-folder">
                        <tr data-study-folder-url="<?php echo site_url().'intake/index/'.urlencode($studyID); ?>">
                            <td style="width:10px;">
                                <span class="glyphicon glyphicon-folder-open pull-left"></span>
                            </td>
                            <td colspan="2">
                                <strong>
                                    <?php echo htmlentities($studyID); ?>
                                </strong>
                            </td>
                            <td style="width:10px;">
                                <?php if(!$studyFolder): ?>
                                    <span class="glyphicon glyphicon-ok"></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php foreach($selectableScanFolders as $folder): ?>
                            <tr data-study-folder-url="<?php echo site_url().'intake/index/'.urlencode($studyID).'/' .str_replace('+','%20',urlencode($folder)); ?>">
                                <td>
                                </td>
                                <td style="width:10px;">
                                    <span class="glyphicon glyphicon-folder-open pull-left"></span>
                                </td>
                                <td>
                                    <span>
                                        <?php echo htmlentities($folder); ?>
                                    </span>
                                </td>
                                <td style="width:10px;">
                                    <?php if($folder==$studyFolder): ?>
                                        <span class="glyphicon glyphicon-ok"></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <button class="btn btn-small btn-default" id="btn-start-scan"><i class="fa fa-search-plus" aria-hidden="true"></i> Scan all files</button>
        </div>

        <?php if($permissions->manager): ?>
            <div class="btn-group">
                <button class="btn btn-default" id="btn-lock"><span class="glyphicon glyphicon-check"></span> Lock datasets</button>
            </div>
            <div class="btn-group">
                    <button class="btn btn-default" id="btn-unlock"><span class="glyphicon glyphicon-check"></span> Unlock datasets</button>
            </div>
            <div class="btn-group">
                <a href="<?php echo base_url('intake/reports'); ?>" class="btn btn-info"><span class="glyphicon glyphicon-signal"></span> Reports</a>
            </div>
        <?php endif; ?>

        <input type="hidden" id="studyID" value="<?php     echo htmlentities($studyID    , ENT_QUOTES) ?>">
        <input type="hidden" id="studyFolder" value="<?php echo htmlentities($studyFolder, ENT_QUOTES) ?>">
        <input type="hidden" id="collection" value="<?php  echo htmlentities($studyFolder, ENT_QUOTES) ?>">


    </div>

    <div class="row" id="viewwindow" style="overflow-y: scroll;overflow-x: hidden;">
        <?php if(!$permissions->manager): ?>
            <?php include "snippets/table_files_unrecognised.php"; ?>
        <?php endif; ?>

        <table id="datatable" class="row-border hover table table-striped" style="width:100%;">
            <thead>
                <tr>
                    <?php if($permissions->manager): ?>
                        <th class="th-invisible-order"></th>
                    <?php endif; ?>
                    <th align="center"><input type="checkbox" class="control-all-cbDataSets"></th>
                    <th></th>
                    <th>Status</th>
                    <th style="width:80px;">Date</th>
                    <th>Pseudocode</th>
                    <th>Experiment type</th>
                    <th>Wave</th>
                    <th>Version</th>
                    <th style="text-align: right;">Nr. of files</th>
                    <th style="text-align: right;">Nr. of errors/<br/>warnings</th>
                    <th style="text-align: right;">Nr. of <br/>comments</th>
                    <th>Created by</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $row=0;
                foreach ($dataSets as $data):?>
                    <?php $errors = $data->datasetErrors + $data->objectErrors; ?>
                    <tr class="detailrow"
                        data-fullpath="<?php echo htmlentities($data->path, ENT_QUOTES) ?>"
                        data-target="<?php echo htmlentities(strtoupper($data->datasetStatus), ENT_QUOTES) ?>"
                        data-row-id="<?php echo $row; $row++; ?>"
                        data-dataset-id="<?php echo htmlentities($data->dataset_id, ENT_QUOTES); ?>"
                        data-ref-path="<?php echo htmlentities($data->path, ENT_QUOTES) ?>"
                        data-error-count="<?php echo $errors; ?>">
                        <td data-target="<?php echo htmlentities(strtoupper($data->datasetStatus), ENT_QUOTES) ?>">
                            <?php echo htmlentities(strtoupper($data->datasetStatus)); ?>
                        </td>
                        <?php if($permissions->manager): ?>
                            <td align="center">
                                <input type="checkbox">
                            </td>
                        <?php endif; ?>
                        <td></td>
                        <td>
                            <div class="datasetstatus_<?php echo strtolower($data->datasetStatus); ?>" title="<?php echo strtoupper($data->datasetStatus); ?>"></div>
                        </td>
                        <td><?php echo date('Y-m-d',intval($data->datasetCreateDate)) ?></td>
                        <td><?php echo htmlentities($data->pseudocode) ?></td>
                        <td><?php echo htmlentities($data->expType) ?></td>
                        <td><?php echo htmlentities($data->wave) ?></td>
                        <td><?php echo htmlentities($data->version) ?></td>
                        <td style="text-align: right;"><?php echo $data->objects ?></td>
                        <td style="text-align: right;">
                            <?php
                                echo  $errors ? $errors : '-';
                                echo '/';
                                $warnings = $data->datasetWarnings + $data->objectWarnings;
                                echo  $warnings ? $warnings : '-';
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <?php echo $data->datasetComments ? $data->datasetComments:'-'; ?>
                        </td>
                        <td>
                            <?php echo htmlentities($data->datasetCreateName) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>


            <?php
             if(false) {
                $row=0;
                foreach ($dataSet as $dataset_id=>$data):?>
                    <?php if(isset($dataSetCreationDate[$dataset_id]) AND $dataSetCreationDate[$dataset_id]): ?>
                        <tr class="detailrow" data-fullpath="<?php echo htmlentities($data->fullpath, ENT_QUOTES) ?>" data-target="<?php echo htmlentities($data->user, ENT_QUOTES) ?>" data-row-id="<?php echo $row; $row++; ?>"
                                data-dataset-id="<?php echo htmlentities($dataset_id, ENT_QUOTES); ?>" data-ref-path="<?php echo htmlentities($data->reference_path, ENT_QUOTES) ?>" data-error-count="<?php echo ((isset($datasetAllLevelErrorCounts[$dataset_id]) AND $datasetAllLevelErrorCounts[$dataset_id]) ? $datasetAllLevelErrorCounts[$dataset_id] : '0' ) ?>">
                            <td data-target="<?php echo htmlentities($data->user, ENT_QUOTES) ?>">
                                <?php echo htmlentities($data->user) ?>
                            </td>
                            <?php if($permissions->manager): ?>
                                <td align="center">
                                    <input type="checkbox">
                                </td>
                            <?php endif; ?>
                            <td></td>
                            <td>
                                <div class="datasetstatus_<?php echo strtolower($data->status); ?>" title="<?php echo $data->status ?>"></div>
                            </td>
                            <td><?php echo date('Y-m-d',$dataSetCreationDate[$dataset_id]); ?></td>
                            <td><?php echo htmlentities($data->pseudocode) ?></td>
                            <td><?php echo htmlentities($data->experiment_type) ?></td>
                            <td><?php echo htmlentities($data->wave) ?></td>
                            <td><?php echo htmlentities($data->version) ?></td>
                            <td style="text-align: right;"><?php echo $dataSetFileCount[$dataset_id]; ?></td>
                            <td style="text-align: right;">
                                 <?php
                                   echo  ((isset($datasetAllLevelErrorCounts[$dataset_id]) AND $datasetAllLevelErrorCounts[$dataset_id]) ? $datasetAllLevelErrorCounts[$dataset_id] : '-' );
                                   echo '/';
                                   echo  ((isset($datasetAllLevelWarningCounts[$dataset_id]) AND $datasetAllLevelWarningCounts[$dataset_id]) ? $datasetAllLevelWarningCounts[$dataset_id] : '-' );
                                ?>
                             </td>
                            <td style="text-align: right;">
                                <?php
                                    $count = count($topLevelDistinctCountVals[$dataset_id]['comment']);
                                    echo $count ? $count:'-';
                                ?>
                            </td>
                            <td><?php echo htmlentities($dataSetCreator[$dataset_id]) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php
                }
            ?>
            </tbody>
        </table>

        <?php if($permissions->manager): ?>
            <?php include "snippets/table_files_unrecognised.php"; ?>
        <?php endif; ?>

    </div>

    <div id="select-generic-modal" class="modal fade"  tabindex="-2" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" >
                <div class="modal-header">
                    <button class="close" data-dismiss="modal">&times;</button>
                    <h3></h3>
                </div>
                <div class="modal-body">
                    <iframe class="select-generic-iframe" style="height:350px; width:99%;"frameborder="0"></iframe>
                    <div class="select-generic-content"></div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
