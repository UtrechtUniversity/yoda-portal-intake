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
        $this->load->model('user');
        $this->load->library('api');

        $this->studies = $this->api->call('intake_list_studies')->data;

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

        $studyFolder = urldecode($studyFolder);

        if($studyFolder AND !in_array($studyFolder,$validFolders)){
            // invalid folder for this study
            //echo '<br>invalid folder in valid study';
            showErrorOnPermissionExceptionByValidUser($this, 'ACCESS_INVALID_FOLDER', 'intake/intake/index');
        }

        if ($studyFolder) { // change the actual folder when person selected a different point of reference.
            $dir = new ProdsDir($rodsAccount, $this->intake_path . '/' . $studyFolder);
        }

        $dataSets = $this->api->call('intake_list_datasets',
            ["coll" => $this->intake_path . ($studyFolder ? '/' . $studyFolder : '')])->data;

        // get the total of dataset files
        $totalDatasetFiles = 0;
        foreach ($dataSets as $set) {
            $totalDatasetFiles += $set->objects;
        }

        $dataErroneousFiles = $this->api->call('intake_list_unrecognized_files',
            ["coll" => $this->intake_path . ($studyFolder ? '/' . $studyFolder : '')])->data;

        //        //$totalFileCount = $this->dataset->getIntakeFileCount($this->intake_path . ($studyFolder ? '/' . $studyFolder : ''));
        $totalFileCount = $this->api->call('intake_count_total_files', ["coll" => $this->intake_path . ($studyFolder ? '/' . $studyFolder : '')])->data;

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

        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $this->session->set_userdata('alertOnPageReload', pageLoadAlert('success','LOCK_OK'));

        $result=0;
        // per collection find latest lock/freeze-status. Possibly the presented data is outdated.
        foreach($datasets as $datasetId){
            $this->api->call('intake_lock_dataset', ["path" => $this->intake_path, "dataset_id" => $datasetId]);
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
            $this->api->call('intake_unlock_dataset', ["path" => $this->intake_path, "dataset_id" => $datasetId]);
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

        $comment_data = $this->api->call('intake_dataset_add_comment',
            ["coll" => $this->intake_path, "dataset_id" => $datasetID, "comment" => $comment])->data;

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
    {   $hasError=FALSE;

        // must be a post
        if(!$this->input->post()){
            $this->output->set_output(json_encode(array(
                'result' => 'Invalid request',
                'hasError' => TRUE
            )));
            return;
        }

        $studyID = $this->input->post('studyID');
        $collection = $this->input->post('collection');

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
        if(strlen($collection)){
            $this->intake_path .= '/' . $collection;
        }

        $result = $this->api->call('intake_scan_for_datasets',
                ["coll" => $this->intake_path]);

        print_r($result);

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

        $result = $this->api->call('intake_dataset_get_details',
            ["coll" => $strPath, "dataset_id" => $datasetID]);

        $orderedArr = (array)$result->data->files;

        ksort($orderedArr, SORT_STRING);

        $this->data['pathItems'] = $orderedArr;

        $this->data['tbl_id'] = $tbl_id;

        $scannedByWhen = $result->data->scanned;

        // prepare data for the error, warning and comments  table.
        $datasetErrors = (array)$result->data->dataset_errors;
        $datasetWarnings = (array)$result->data->dataset_warnings; #array();

        // comment handling
        $datasetComments = array();
        foreach((array)$result->data->comments as $comment){
            $commentParts = explode(':', $comment, 3); //0=name, 1=timestamp, 2=comment
            array_push($datasetComments, array(
                'name' => $commentParts[0],
                'time' => $commentParts[1],
                'comment' => $commentParts[2]));
        }

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
}

/* End of file intake.php */
/* Location: ./application/controllers/intake.php */
