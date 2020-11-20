<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Intake extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Set current menu item
        $this->data['currentMenuItem'] = 'intake';

        // initially no rights for any study
        $this->permissions = (object)array(
            'assistant' => FALSE,
            'manager'   => FALSE,
        );

        $this->load->config('config');
        $this->load->model('yodaprods');
        $this->load->model('user');
        $this->load->model('dataset');

        $this->studies = $this->yodaprods->getStudies($this->rodsuser->getRodsAccount());

        $this->load->helper('yoda_intake');
    }

    /*
     * @param string $studyID
     * @param string $studyFolder
     *
     * */
    public function index($studyID='', $studyFolder='')
    {
        // studyID handling from session info
        if(!$studyID){
            if($tempID = $this->session->userdata('studyID') AND $tempID){
                $studyID = $tempID;
            }
        }

        // kill value in session and only add studyIDs that the user actually has rights to
        $this->session->unset_userdata('studyID');

        $errorAlreadySet = false; // to be able to have some order/priority in
        $rightsForStudy = true;

        if(!$this->user->validateStudy($this->studies, $studyID)){
            showErrorOnPermissionExceptionByValidUser($this, 'ACCESS_INVALID_STUDY', 'intake/intake/index');
            $errorAlreadySet = true;
            $rightsForStudy = false;
        }

        // get study dependant rights for current user.
        $this->permissions = $this->user->getIntakeStudyPermissions($studyID);

        if(!($this->permissions->assistant OR $this->permissions->manager)){
            // No access rights for this particular module
            if (!$errorAlreadySet) {
                showErrorOnPermissionExceptionByValidUser($this, 'ACCESS_NO_ACCESS_ALLOWED', 'intake/intake/index'); // Dit gaat over no access voor deze study
            }
            $rightsForStudy = false;
        }

        // study is validated. Put in session.
        if ($studyID AND $rightsForStudy) {
            $this->session->set_userdata('studyID', $studyID);
        }

        $this->data['permissions']=$this->permissions;
        $this->data['studies']=$this->studies;
        $this->data['studyID']=$studyID;

        // study dependant intake path.
        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $rodsAccount = $this->rodsuser->getRodsAccount();
        $dir = new ProdsDir($rodsAccount, $this->intake_path);

        $validFolders = array();
        foreach($dir->getChildDirs() as $folder){
            $validFolders[]=$folder->getName();
        }

        #$this->data['selectableScanFolders'] = $validFolders;  // folders that can be checked for scanning

        $studyFolder = urldecode($studyFolder);

        if($studyFolder AND !in_array($studyFolder,$validFolders)){
            // invalid folder for this study
            //echo '<br>invalid folder in valid study';
            showErrorOnPermissionExceptionByValidUser($this, 'ACCESS_INVALID_FOLDER', 'intake/intake/index');
        }

        if ($studyFolder) { // change the actual folder when person selected a different point of reference.
            $dir = new ProdsDir($this->rodsuser->getRodsAccount(), $this->intake_path . '/' . $studyFolder);
        }

        $dataSets = array();
        $this->dataset->getIntakeDataSets($this->intake_path . ($studyFolder ? '/' . $studyFolder : ''), $dataSets);

        // get the total of dataset files
        $totalDatasetFiles = 0;
        foreach ($dataSets as $set) {
            $totalDatasetFiles += $set->objects;
        }

        $dataErroneousFiles = array();
        $this->dataset->getErroneousIntakeFiles($this->intake_path . ($studyFolder ? '/' . $studyFolder : ''), $dataErroneousFiles);

        $totalFileCount = $this->dataset->getIntakeFileCount($this->intake_path . ($studyFolder ? '/' . $studyFolder : ''));

        $viewParams = array(
            'styleIncludes' => array(
                'css/jquery.dataTables.css',
                'css/datatables.bootstrap.min.css',
                'css/intake.css',
                'css/treetable/jquery.treetable.css',
                'css/treetable/jquery.treetable.theme.default.css',
                'lib/font-awesome/css/font-awesome.css',
            ),
            'scriptIncludes' => array(
                'scripts/jquery.dataTables.min.js',
                'scripts/dataTables.bootstrap.js',
                'scripts/dataTables.bootstrapPagination-3.js',
                $this->permissions->manager ? 'scripts/datatables/intake_overview.js' : 'scripts/datatables/intake_overview_assistant.js',
                'scripts/datatables/plugin_sort_on_image.js',
                'scripts/controllers/intake.js',
                'scripts/treetable/jquery.treetable.js'
            ),
            'activeModule'   => 'intake',
            'permissions' => $this->permissions,
            'studies' => $this->studies,
            'intakePath' => $this->intake_path,
            'selectableScanFolders' => $validFolders,
            'dir' => $dir,
            'dataSets' => $dataSets,
            'totalDatasetFiles' => $totalDatasetFiles,
            'dataErroneousFiles' => $dataErroneousFiles,
            'totalFileCount' => $totalFileCount,
            'studyID' => $studyID,
            'studyFolder' => $studyFolder,
            'title' => 'Study ' . $studyID . ($studyFolder ? '/' . $studyFolder : '')
        );
        loadView('/intake/index', $viewParams);
    }

    /*  function lockDatasets()

        mark a selection of datasets as locked.

         Accessed via ajax via POSTS
                studyID - required
                datasets - required array()

         Returns json representation of the result
    */
    public function lockDatasets()
    {
        $hasError=false;
        $this->output->enable_profiler(FALSE);

        // return a json representation of the result
        $this->output->set_content_type('application/json');

        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $datasets = $this->input->post('datasets'); // array of folders
        $studyID = $this->input->post('studyID');

        // input validation
        if(!$studyID OR !is_array($datasets)){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid input',
                'hasError' => TRUE
            )));
            return;
        }

        // Only datamanager is allowed to do this
        $errorMessage='';
        if(!$this->user->validateIntakeStudyPermissions($studyID, $permissionsAllowed=array($this->user->ROLE_Manager), $errorMessage)){
            $this->output->set_output(json_encode(array(
                'result' => $errorMessage,
                'hasError' => TRUE
            )));
            return;
        }

        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $this->session->set_userdata('alertOnPageReload', pageLoadAlert('success','LOCK_OK'));

        $result=0;
        // per collection find latest lock/freeze-status. Possibly the presented data is outdated.
        foreach($datasets as $datasetId){
            //$output .= ','.$collection;
            if($result = $this->yodaprods->datasetLock($this->rodsuser->getRodsAccount(), $this->intake_path, $datasetId)){
                $this->session->set_userdata('alertOnPageReload', pageLoadAlert('success','LOCK_OK', $result));
                $hasError=TRUE;
                break;
            }
        }

        $this->output->set_output(json_encode(array(
            'result' => $result,
            'hasError' => $hasError
        )));
    }

    /*  function unlockDatasets()

        remove the lock-mark for a selection of datasets

         Accessed via ajax via POSTS
                studyID - required
                datasets - required array()

         Returns json representation of the result
    */
    public function unlockDatasets()
    {
        $hasError=false;
        $this->output->enable_profiler(FALSE);

        // return a json representation of the result
        $this->output->set_content_type('application/json');

        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $datasets = $this->input->post('datasets'); // array of folders
        $studyID = $this->input->post('studyID');

        // input validation
        if(!$studyID OR !is_array($datasets)){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid input',
                'hasError' => TRUE
            )));
            return;
        }

        // Only datamanager is allowed to do this
        $errorMessage='';
        if(!$this->user->validateIntakeStudyPermissions($studyID, $permissionsAllowed=array($this->user->ROLE_Manager), $errorMessage)){
            $this->output->set_output(json_encode(array(
                'result' => $errorMessage,
                'hasError' => TRUE
            )));
            return;
        }

        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $this->session->set_userdata('alertOnPageReload', pageLoadAlert('success','UNLOCK_OK'));

        $result=0;
        // per collection find latest lock/freeze-status. Possibly the presented data is outdated.
        foreach($datasets as $datasetId){
            //$output .= ','.$collection;
            if($result = $this->yodaprods->datasetUnlock($this->rodsuser->getRodsAccount(), $this->intake_path, $datasetId)){
                $this->session->set_userdata('alertOnPageReload', pageLoadAlert('danger','UNLOCK_NOK', $result));
                $hasError=TRUE;
                break;
            }
        }
        $this->output->set_output(json_encode(array(
            'result' => $result,
            'hasError' => $hasError
        )));
    }

    /*  function saveDatasetComment()

        save comment on dataset as added by the user from within the detail view of the dataset.

         Accessed via ajax via POSTS
                studyID - required
                datasetID - required
                comment - required

         Returns json representation of the row data to be added to comment-table
    */
    public function saveDatasetComment()
    {
        $this->output->enable_profiler(FALSE);

        // return a json representation of the result
        $this->output->set_content_type('application/json');

        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $studyID = $this->input->post('studyID');
        $datasetID = $this->input->post('datasetID');
        $comment = $this->input->post('comment');

        // input validation
        if(!$studyID OR !$datasetID OR !$comment){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid input',
                'hasError' => TRUE
            )));
            return;
        }

        // assistant and manager both are allowed to view the details of a dataset.
        $errorMessage='';
        if(!$this->user->validateIntakeStudyPermissions($studyID, $permissionsAllowed=array($this->user->ROLE_Manager,$this->user->ROLE_Assistant), $errorMessage)){
            $this->output->set_output(json_encode(array(
                'result' => $errorMessage,
                'hasError' => TRUE
            )));
            return;
        }

        // do save action.
        $this->intake_path = '/'.$this->config->item('rodsServerZone').'/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $result = $this->yodaprods->addCommentToDataset($this->rodsuser->getRodsAccount(), $this->intake_path, $datasetID, $comment);

        // Return the new row data so requester can add in comments table
        $this->output->set_output(json_encode(array(
                'output' => array('user'=>  $this->rodsuser->getUsername(),
                                'timestamp'=>date('Y-m-d H:i:s'),
                                'comment'=>$comment),
                'hasError' => FALSE
        )));
    }

    /*  function scanSelection()

        start file scanning process.
        If root is among the

         Accessed via ajax via POSTS
                 studyID - required
                 collections - required

         Returns json representation of succes or not
    */
    public function scanSelection()
    {
        $hasError=FALSE;

        $this->output->enable_profiler(FALSE);

        $this->output->set_content_type('application/json');

        // must be a post
        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $collection = $this->input->post('collection'); // used to be an array of folders .. OBSOLETE!! @TODO: change to single folder
        $studyID = $this->input->post('studyID');

        // input validation
        if(!$studyID){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid input',
                'hasError' => TRUE
            )));
            return;
        }

        // assistant and manager both are allowed to view the details of a dataset.
        $errorMessage='';
        if(!$this->user->validateIntakeStudyPermissions($studyID, $permissionsAllowed=array($this->user->ROLE_Manager,$this->user->ROLE_Assistant), $errorMessage)){
            $this->output->set_output(json_encode(array(
                'result' => $errorMessage,
                'hasError' => TRUE
            )));
            return;
        }

        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;
        // scan subfolder only
        if(strlen($collection)){
            $this->intake_path .= '/' . $collection;
        }


        if($result = $this->yodaprods->scanIrodsCollection($this->rodsuser->getRodsAccount(), $this->intake_path)){ // Study-root
            $this->session->set_userdata('alertOnPageReload', pageLoadAlert('danger','SCAN_NOK',$result)); // for presentation purposes after page reload
            $hasError=TRUE ;
        }
        else{
            $this->session->set_userdata('alertOnPageReload', pageLoadAlert('success','SCAN_OK'));
        }

        $this->output->set_output(json_encode(array(
            'result' => $result,
            'hasError' => $hasError
        )));
        return;
    }

    /*
        Get detail view on dataset:
         1) Errors/warnings
         2) Comments
         3) Tree view of files within dataset.

         Accessed via ajax via POSTS (all data required)
                 path - required
                 tbl_id - required (numeric)
                 studyID - required
                 datasetID - required

         Returns json representation of Dataset detail view
    */
    public function getDatasetDetailView()
    {
        $hasError=false;
        $this->output->enable_profiler(FALSE);

        // return a json representation of the result
        $this->output->set_content_type('application/json');

        // must be a post
        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $studyID = $this->input->post('studyID');
        $datasetID = $this->input->post('datasetID');
        $strPath = $this->input->post('path');
        $tbl_id = $this->input->post('tbl_id');

        // input validation
        if(!$studyID OR !$datasetID OR !$strPath OR !strlen($tbl_id)){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid input',
                'hasError' => TRUE
            )));
            return;
        }

        // assistant and manager both are allowed to view the details of a dataset.
        $errorMessage='';
        if(!$this->user->validateIntakeStudyPermissions($studyID, $permissionsAllowed=array($this->user->ROLE_Manager,$this->user->ROLE_Assistant),$errorMessage)){
            $this->output->set_output(json_encode(array(
                'result' => $errorMessage,
                'hasError' => TRUE
            )));
            return;
        }

        $rodsAccount = $this->rodsuser->getRodsAccount();
        $home = new ProdsDir($rodsAccount, $strPath);

        $pathItems = array();
        $this->_getNewPathInfo($pathItems, $home, $parentNodeId="0", $datasetID);

        $this->data['pathItems'] = $pathItems;

        //$this->data['content'] = 'intake/intake/index';
        $this->data['tbl_id'] = $tbl_id;

        $scannedByWhen = '';
        // prepare data for the error, warning and comments  table.
        $datasetErrors = array();
        $datasetWarnings = array();
        $datasetComments = array();
        // For getting rid of double information:
        $datasetIDsProcessed = array(); // dataset ids that have been processed already
        // Get rid off double information in the case of files sitting on the same level and having the same data n times.
        // Temporary storage of info is required as it might be double.
        // Can only be taken into account after knowing whether the dataset-id has not been processed yet.
        // Beware, the metadata we're looking at here, is already limited to toplevel-information only.
        // dataset_error, dataset_warning, comment are only linked to toplevel-datasets.
        foreach($pathItems as $nodeId=>$item){
            $tempErrors = array(); // temp storage of metadata
            $tempWarnings = array();
            $tempComments = array();
            $datasetID = null;
            foreach($item->meta as $m){
                switch ($m->name){
                    case 'scanned':
                        $scannedByWhen = $m->value;
                        break;
                    case 'dataset_id':
                        $datasetID = $m->value;
                        break;
                    case 'dataset_error':
                        $tempErrors[] = $m->value;
                        break;
                    case 'dataset_warning':
                        $tempWarnings[] = $m->value;
                        break;
                    case 'comment':
                        $tempComments[] = $m->value;
                        break;
                }
            }

            // temp set is complete. If not already present, than add to output-arrays.
            if($datasetID AND !in_array($datasetID, $datasetIDsProcessed)){
                $datasetIDsProcessed[]=$datasetID;

                // errors
                foreach($tempErrors as $error){
                    $datasetErrors[] = $error;
                }
                // warnings
                foreach($tempWarnings as $warning){
                    $datasetWarnings[] = $warning;
                }
                // comments
                foreach($tempComments as $comment){
                    $commentParts = explode(':', $comment, 3); //0=name, 1=timestamp, 2=comment
                    array_push($datasetComments, array(
                        'name' => $commentParts[0],
                        'time' => $commentParts[1],
                        'comment' => $commentParts[2]));
                }
            }
        }

        // order comments by time
        usort($datasetComments, function($a, $b) {
            return $a['time'] - $b['time'];
        });

        $this->data['datasetPath'] = $strPath;
        $this->data['scannedByWhen'] = explode(':',$scannedByWhen);
        $this->data['datasetErrors'] = $datasetErrors;
        $this->data['datasetWarnings'] = $datasetWarnings;
        $this->data['datasetComments'] = $datasetComments;

        $this->data['datasetID'] = $datasetID;

        $strTableDef = $this->load->view('intake/intake/snippets/dataset_detail_view', $this->data,true);


        $this->output->set_output(json_encode(array(
             'output' => $strTableDef,
            'hasError' => $hasError
        )));
        return;
    }

    private function _getNewPathInfo(&$pathItems, $prodsParentPath, $parentNodeId, $datasetID)
    {
        // There is a possibility that the parent node holds all toplevel info.
        // Therefore, take this into account but only on the first go, i.e. the root of all.
        if(count($pathItems)== 0) {
            $topLevelMeta = $prodsParentPath->getMeta();
            foreach($topLevelMeta as $tlmeta){
                if($tlmeta->name=='dataset_id' AND $tlmeta->value==$datasetID){
                    $pathItems['']= (object) array("name" => '',
                        'isFolder' => true,
                        'parent_id' => '',
                        'meta' => $topLevelMeta
                    );
                }
            }
        }

        $folders = $prodsParentPath->getChildDirs();

        $subLevel = 0;

        foreach($folders as $folder){
            // check if is of correct dataset!!
            $metaData = $folder->getMeta();
            foreach($metaData as $meta){
                if($meta->name=='dataset_id' AND $meta->value==$datasetID){
                    $nodeId = $parentNodeId . '.' . $subLevel;

                    $pathItems[$nodeId]= (object) array("name"=>$folder->getName(),
                        'isFolder'=> true,
                        'parent_id' => $parentNodeId,
                        'meta' => $folder->getMeta()
                    );

                    $this->_getNewPathInfo($pathItems, $folder, $nodeId, $datasetID);

                    $subLevel++;
                }
            }
        }

        $files = $prodsParentPath->getChildFiles();
        $usedFileNames = array();
        foreach($files as $file){
            // check if is of correct dataset!!

            // Due to replication same file names can occur more than once.
            //echo '<br>' . $file->getName();
            if (!in_array($file->getName(), $usedFileNames)) {
                $usedFileNames[] = $file->getName();
                $metaData = $file->getMeta();
                foreach ($metaData as $meta) {
                    if ($meta->name == 'dataset_id' AND $meta->value == $datasetID) {
                        $nodeId = $parentNodeId . '.' . $subLevel;

                        $pathItems[$nodeId] = (object)array("name" => $file->getName(),
                            'isFolder' => false,
                            'parent_id' => $parentNodeId,
                            'meta' => $file->getMeta());
                        $subLevel++;

                        break;
                    }
                }
            }
        }
    }
}

/* End of file intake.php */
/* Location: ./application/controllers/intake.php */
