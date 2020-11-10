<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
		parent::__construct();

        // Set current menu item
        $this->data['currentMenuItem'] = 'reports';

        // initially no rights for any study
        $this->permissions = (object)array(
            'assistant' => FALSE,
            'manager'   => FALSE,
        );

        $this->load->config('config');
        $this->load->config('module'); // load the module info as a config file

        $this->load->model('yodaprods');
        $this->load->model('user');
        $this->load->model('dataset');

        $this->load->library('api');
        $studies = $this->api->call('intake_list_studies')->data;

        // @TODO!! DIt moet Aparte API call worden
        $dmStudies = array();
        // Filter studies only to studies with datamanager permissions
        foreach($studies as $study) {
            $permissions = $this->user->getIntakeStudyPermissions($study);

            if($permissions->manager) {
                $dmStudies[] = $study;
            }
        }

        $this->studies = $dmStudies;  // Now studies contains datamanager-studies only

        $this->load->helper('yoda_intake');
    }

    public function index($studyID=null)
    {
        // studyID handling from session info
        if(!$studyID){
            if($tempID = $this->session->userdata('studyID') AND $tempID){
                $studyID = $tempID;
            }
        }

        $this->data['title'] = 'VAULT: NO ACCESS'; // only after knowing for sure that this person has the proper rights the title will change

        // Studies (held in $this->studies) are limited to studies with datamanager-access only

        $error = '';
        if(!$this->studies) {
            $error = 'ACCESS_NO_DATAMANAGER';
        }

        if(!$this->user->validateStudy($this->studies, $studyID)){
            $error = 'ACCESS_INVALID_STUDY';
        }

        if ($error != '') {
            $viewParams = array(
                'styleIncludes' => array(
                ),
                'scriptIncludes' => array(
                    'scripts/controllers/reports.js',
                ),
                'error' => $error,
                'activeModule'   => 'intake',
                'studies' => $this->studies,
                'title' => 'VAULT: Study ' . $studyID,
            );
            loadView('/reports/index', $viewParams);
            return;
        }
        // study is validated. Put in session.
        $this->session->set_userdata('studyID',$studyID);

        $this->intake_path = '/' . $this->config->item('rodsServerZone') . '/home/' . $this->config->item('INTAKEPATH_StudyPrefix') . $studyID;

        $viewParams = array(
            'styleIncludes' => array(
            ),
            'scriptIncludes' => array(
                  'scripts/controllers/reports.js',
            ),
            'error' => $error,
            'activeModule'   => 'intake',
            'moduleGlyph' => $this->config->item('module-glyph'),
            'studies' => $this->studies,
            'intakePath' => $this->intake_path,
            'datasetTypeCounts' => $this->dataset->vaultDatasetCountsPerStudy($studyID),
            'aggregatedInfo' => $this->dataset->vaultAggregatedInfo($studyID),
            'studyID' => $studyID,
            'studyFolder' => '',
            'title' => 'VAULT: Study ' . $studyID,
        );
        loadView('/reports/index', $viewParams);
    }
}