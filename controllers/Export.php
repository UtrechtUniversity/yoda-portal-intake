<?php

/**
 * Class Export required for exporting of reports
 */
class Export extends MY_Controller {
    public $user = NULL;
    public $intake_path = '';
    public $studies=array();

    public function __construct() {
        parent::__construct();

        $this->load->config('config');
        $this->load->model('user');

        $this->load->library('api');
        $this->studies = $this->api->call('intake_list_studies')->data;

        //$this->studies = $this->yodaprods->getStudies($this->rodsuser->getRodsAccount());
        //$this->studies = $this->intakeapi->intake_list_studies();

        $this->load->helper('yoda_intake');
    }

    public function download($studyID=null)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $studyID . '.csv"');

        if(!$studyID){
            echo '"Valid study required"';
            return FALSE;
        }

        if(!$this->user->validateStudy($this->studies, $studyID)){
            echo 'Invalid study';
            return FALSE;
        }

        if(!$this->user->isGroupMember($this->rodsuser->getRodsAccount(), $this->user->PERM_GroupDataManager . $studyID, $this->rodsuser->getUsername())){
            echo "You have no rights for this report of this study";
            return FALSE;
        }

//        $exportData = $this->dataset->exportVaultDatasetInfo($studyID);
        $exportDataAPI = $this->api->call('intake_report_export_study_data', ['study_id'=>$studyID])->data;

        echo '"Study"';
        echo ",";
        echo '"Wave"';
        echo ",";
        echo '"ExpType"';
        echo ",";
        echo '"Pseudo"';
        echo ",";
        echo '"Version"';
        echo ",";
        echo '"ToVaultDay"';
        echo ",";
        echo '"ToVaultMonth"';
        echo ",";
        echo '"ToVaultYear"';
        echo ",";
        echo '"DatasetSize"';
        echo ",";
        echo '"DatasetFiles"';
        echo "\r\n";

        foreach($exportDataAPI as $dataClass) {
            $data = (array) $dataClass;
            echo $this->_expFormatString($studyID);
            echo ",";
            echo $this->_expFormatString($data['wave']);
            echo ",";
            echo $this->_expFormatString($data['experiment_type']);
            echo ",";
            echo $this->_expFormatString($data['pseudocode']);
            echo ",";
            echo $this->_expFormatString($data['version']);
            echo ",";
            echo date('j,n,Y', $data['dataset_date_created']);
            echo ",";
            echo $data['totalFileSize'];
            echo ",";
            echo $data['totalFiles'];
            echo "\r\n";
        }
   }

    private function _expFormatString($string)
    {
        return '"' . $string . '"';
    }
}
