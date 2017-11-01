<?php
class Dataset {
    /**
     * Definition of all known dataset experiment types
     */
    const EXPERIMENT_TYPE_ECHO = 'echo';
    const EXPERIMENT_TYPE_PCI = 'pci';
    const EXPERIMENT_TYPE_EEG = 'eeg';
    const EXPERIMENT_TYPE_ET = 'et';
    const EXPERIMENT_TYPE_COMPTASK = 'comptask';
    const EXPERIMENT_TYPE_OTHER = 'other';

/* properties as used. However not validated.
    dataset_id
    path
    wave
    expType
    pseudocode
    version
    datasetStatus
    datasetCreateName
    datasetCreateDate
    datasetErrors
    datasetWarnings
    datasetComments
    objects
    objectErrors
    objectWarnings
*/

    /**
     * @param array $properties
     *
     * Initialize the dataset info with the passed array info
     * The propertykeys should be validated against the class properties
     */
    function __construct($properties = NULL)
    {
        if(is_array($properties)){
            foreach($properties as $key=>$val) {
                $this->$key = $val;
            }
        }
    }

    /**
     * @param $studyID
     *
     * @return array

     * Get the count of datasets wave/experimenttype
     * In the vault a dataset is always located in a folder.
     * Therefore, looking at the folders only is enough
     */
    function vaultDatasetCountsPerStudy($studyID)
    {
        $datasetTypeCounts = array();
        $dataSets = array();

        $iRodsColumns = array(
            "COL_COLL_NAME" => NULL,
            "COL_COLL_PARENT_NAME" => NULL,
            "COL_META_COLL_ATTR_NAME" => NULL,
            "COL_META_COLL_ATTR_VALUE" => NULL
        );

        $data = get_instance()->yodaprods->query(get_instance()->rodsuser->getRodsAccount(),
            $iRodsColumns,
            '/' . get_instance()->config->item('rodsServerZone') . '/home/grp-vault-' . $studyID . '%',
            'COL_META_COLL_ATTR_NAME',
            array('dataset_id', 'dataset_date_created', 'wave', 'version', 'experiment_type', 'pseudocode')
        );

        if($data['recordCount']) {
            $columns = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {

                $index = $columns["COL_COLL_NAME"][$i];

                $metaName = $columns["COL_META_COLL_ATTR_NAME"][$i];
                $metaValue = $columns["COL_META_COLL_ATTR_VALUE"][$i];

                $interestingMeta = false;

                switch ($metaName) {
                    case 'dataset_date_created':
                        $interestingMeta = true;
                        break;
                    case 'wave':
                        $interestingMeta = true;
                        break;
                    case 'version':
                        $metaValue = strtolower($metaValue);
                        $interestingMeta = true;
                        break;
                    case 'experiment_type':
                        $metaValue = strtolower($metaValue);
                        $interestingMeta = true;
                        break;
                }
                if($interestingMeta) {
                    $dataSets[$index][$metaName] = $metaValue;

                }
            }
        }

        foreach($dataSets as $datasetId=>$meta){
           if(isset($meta['dataset_date_created'])) { // this defines a folder that holds a complete set.
               $type = $meta['experiment_type'];
               $wave = $meta['wave'];
               $version = $meta['version'];
               if(isset($datasetTypeCounts[$type][$wave][$version])){
                   $datasetTypeCounts[$type][$wave][$version]++;
               }
               else{
                   $datasetTypeCounts[$type][$wave][$version]=1;
               }
            }
        }

        return $datasetTypeCounts;
    }

    /**
     * @param $studyID
     * @return array

     * Collects the following information for Raw, Processed datasets. Including a totalisation of this all
      (Raw/processed is kept in VERSION)

        -Total datasets
        -Total files
        -Total file size
        -File size growth in a month
        -Datasets growth in a month
        -Pseudocodes  (distinct)
     */
    public function vaultAggregatedInfo($studyID)
    {
        $dataSets = array();
        $dataSetPaths = array();

        $datasetCount = array('raw' => 0, 'processed' => 0);

        $fileCount = array('raw' => 0, 'processed' => 0);

        $fileSize = array('raw' => 0, 'processed' => 0);

        $fileGrowth = array('raw' => 0, 'processed' => 0);

        $datasetGrowth = array('raw' => 0, 'processed' => 0);

        $pseudoCodes = array('raw'=>array(), 'processed'=>array());

        $refLastMonth = 30*24*3600;

        $iRodsColumns = array(
            "COL_COLL_NAME" => NULL,
            "COL_COLL_PARENT_NAME" => NULL,
            "COL_META_COLL_ATTR_NAME" => NULL,
            "COL_META_COLL_ATTR_VALUE" => NULL
        );

        $data = get_instance()->yodaprods->query(get_instance()->rodsuser->getRodsAccount(),
            $iRodsColumns,
            '/' . get_instance()->config->item('rodsServerZone') . '/home/grp-vault-' . $studyID . '%',
            'COL_META_COLL_ATTR_NAME',
            array('dataset_id', 'dataset_date_created', 'version', 'pseudocode')
        );

        if($data['recordCount']) {
            $columns = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {

                $index = $columns["COL_COLL_NAME"][$i];

                $metaName = $columns["COL_META_COLL_ATTR_NAME"][$i];
                $metaValue = $columns["COL_META_COLL_ATTR_VALUE"][$i];

                $interestingMeta = false;

                switch ($metaName) {
                    case 'dataset_date_created':
                        $metaValue = intval($metaValue);
                        $interestingMeta = true;
                        break;
                    case 'version':
                        $metaValue = strtolower($metaValue);
                        $interestingMeta = true;
                        break;
                    case 'pseudocode':
                        $pseudocode = $metaValue;
                        $interestingMeta = true;
                        break;
                }
                if($interestingMeta) {
                    $dataSets[$index][$metaName] = $metaValue;
                }
            }
        }

        foreach($dataSets as $path => $meta) {
            if(isset($meta['dataset_date_created'])) {
                // Now divide to be able to accomplish counting as fast as possible.
                $dataSetPaths[] = $path;

                $index = $meta['version']=='raw' ? 'raw' : 'processed';
                $datasetCount[$index]++;

                if(($meta['dataset_date_created']-$refLastMonth) >= 0) {
                    $datasetGrowth[$index]++;
                }

                if(!in_array($meta['pseudocode'], $pseudoCodes[$index])) {
                    $pseudoCodes[$index][] = $meta['pseudocode'];
                }
            }
        }

        // now process file information within that region
        $iRodsColumns = array(
            "COL_DATA_NAME" => NULL,
            "COL_COLL_NAME" => NULL,
//            "COL_COLL_PARENT_NAME" => NULL,
            "COL_DATA_SIZE" => NULL,
            "COL_COLL_CREATE_TIME" => NULL,
//            "COL_COLL_OWNER_NAME" => NULL
        );

        $data = get_instance()->yodaprods->query(get_instance()->rodsuser->getRodsAccount(),
            $iRodsColumns,
            '/' . get_instance()->config->item('rodsServerZone') . '/home/grp-vault-' . $studyID . '%');

        if($data['recordCount']) {
            $columns = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {

                // first check whether the file is part of a dataset.
                $isPartOfSet = false;
                foreach($dataSetPaths as $path) {
                    // path must be a substring of de collection name
                    if(strpos($columns["COL_COLL_NAME"][$i], $path)!==false) {
                        $isPartOfSet = true;
                        break;
                    }
                }

                if($isPartOfSet){ // process further
                    $setMeta = $dataSets[$path];

                    $index = $setMeta['version']=='raw' ? 'raw' : 'processed';

                    $fileCount[$index]++;
                    $fileSize[$index] += $columns["COL_DATA_SIZE"][$i];
                    if(($columns["COL_COLL_CREATE_TIME"][$i]-$refLastMonth) >= 0) {
                        $fileGrowth[$index] += $columns["COL_DATA_SIZE"][$i];
                    }
                }
            }
        }

        return array(
            'total' => array(
                'totalDatasets' => $datasetCount['raw'] + $datasetCount['processed'],
                'totalFiles' => $fileCount['raw'] + $fileCount['processed'],
                'totalFileSize' => human_filesize($fileSize['raw'] + $fileSize['processed']),
                'totalFileSizeMonthGrowth' => human_filesize($fileGrowth['raw'] + $fileGrowth['processed']),
                'datasetsMonthGrowth' => $datasetGrowth['raw'] + $datasetGrowth['processed'],
                'distinctPseudoCodes' => count($pseudoCodes['raw'] + $pseudoCodes['processed']),
            ),
            'raw' => array(
                'totalDatasets' => $datasetCount['raw'],
                'totalFiles' => $fileCount['raw'],
                'totalFileSize' => human_filesize($fileSize['raw']),
                'totalFileSizeMonthGrowth' => human_filesize($fileGrowth['raw']),
                'datasetsMonthGrowth' => $datasetGrowth['raw'],
                'distinctPseudoCodes' => count($pseudoCodes['raw']),
            ),
            'notRaw' => array(
                'totalDatasets' => $datasetCount['processed'],
                'totalFiles' => $fileCount['processed'],
                'totalFileSize' => human_filesize($fileSize['processed']),
                'totalFileSizeMonthGrowth' => human_filesize($fileGrowth['processed']),
                'datasetsMonthGrowth' => $datasetGrowth['processed'],
                'distinctPseudoCodes' => count($pseudoCodes['processed']),
            ),
        );
    }

    /**
     * returns count of files within the referenced path
     *
     * File state is not import (scanned or not)
     *
     * @param $referencePath
     * @return int
     */
    function getIntakeFileCount($referencePath)
    {
        // Get all files that have a scanned tag
        $iRodsColumns = array(
            "COL_DATA_NAME" => NULL,
            "COL_COLL_NAME" => NULL
        );
        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', 'like',$referencePath.'%');
        $data = array();

        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        // intake files have to be matched against the exclusion pattern to be counted
        $file_exclusion_patterns = explode(';',get_instance()->config->item('file_exclusion_patterns'));

        $fileCount = 0;

        if( $data['recordCount']) {
            $recordData = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {
                if($this->_fileIsToBeViewed( $recordData["COL_DATA_NAME"][$i], $file_exclusion_patterns)) {
                    $fileCount++;
                }
            }
        }
        return $fileCount;
    }

    /**
     * Collect the files that hold the attribute 'unrecognised'.
     * These are files that went through the intake process but were not (fully) recognisable as being part of a dataset
     *
     * @param $referencePath
     * @param $erroneousFiles
     */
    function getErroneousIntakeFiles($referencePath, &$erroneousFiles)
    {
        $erroneousText = 'Experiment type, wave or pseudocode is missing from path';
        // Step through collections
        $iRodsColumns = array(
            "COL_DATA_NAME" => NULL,
            "COL_COLL_NAME" => NULL,
            "COL_COLL_CREATE_TIME" => NULL,
            "COL_COLL_OWNER_NAME" => NULL,
        );

        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', 'like', $referencePath.'%');
        $condition->add('COL_META_DATA_ATTR_NAME','=', 'unrecognized');

        $data = array();
        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        // For excluding files that match the exclusion pattern(s)
        $file_exclusion_patterns = explode(';',get_instance()->config->item('file_exclusion_patterns'));

        if($data['recordCount']) {
            $recordData = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {
                if($this->_fileIsToBeViewed( $recordData["COL_DATA_NAME"][$i], $file_exclusion_patterns)) {
                    $metaData = array();
                    $this->getMetaInfoOnFile($recordData["COL_COLL_NAME"][$i], $recordData["COL_DATA_NAME"][$i], $metaData);

                    $pseudocode = isset($metaData['pseudocode']) ? $metaData['pseudocode'] : '';
                    $experiment_type = isset($metaData['experiment_type']) ? $metaData['experiment_type'] : '';
                    $wave = isset($metaData['wave']) ? $metaData['wave'] : '';
                    $version = isset($metaData['version']) ? $metaData['version'] : '';

                    $dataRow = (object)array('name' => $recordData["COL_DATA_NAME"][$i],
                        'path' => $recordData["COL_COLL_NAME"][$i],
                        'error' => $erroneousText,
                        'date' => $recordData["COL_COLL_CREATE_TIME"][$i],
                        'creator' => $recordData["COL_COLL_OWNER_NAME"][$i],
                        'pseudocode' => $pseudocode,
                        'experiment_type' => $experiment_type,
                        'wave' => $wave,
                        'version' => $version
                    );

                    $erroneousFiles[] = $dataRow;
                }
            }
        }
    }

    /**
     * Get the meta relevant for the intake module for a specific file.
     * - experiment typee
     * - pseudocode
     * - version
     * - wave
     *
     * @param $filePath
     * @param $fileName
     */
    function getMetaInfoOnFile($filePath, $fileName, &$data)
    {
        $iRodsColumns = array(
            "COL_META_DATA_ATTR_VALUE" => NULL,
            "COL_META_DATA_ATTR_NAME" => NULL,
        );
        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', '=',$filePath);
        $condition->add('COL_DATA_NAME', '=',$fileName);

        $condition->add('COL_META_DATA_ATTR_NAME','=','experiment_type', array(
            array('op'=>'=','val'=>'pseudocode'),
            array('op'=>'=','val'=>'version'),
            array('op'=>'=','val'=>'wave'),
        ));

        $data = array();
        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        if($data['recordCount']) {
            $recordData = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {
                $data[$recordData["COL_META_DATA_ATTR_NAME"][$i]] = $recordData["COL_META_DATA_ATTR_VALUE"][$i];            }
        }
    }

    /**
     * @param $referencePath
     * @param array $dataSets
     * @return bool
     */
    function getIntakeDataSets($referencePath, &$dataSets = array())
    {
        // Step through collections
        $iRodsColumns = array(
            "COL_META_COLL_ATTR_VALUE" => NULL,
            "COL_COLL_NAME" => NULL
        );

        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', 'like', $referencePath . '%');
        $condition->add('COL_META_COLL_ATTR_NAME', '=', 'dataset_toplevel');

        $data = array();
        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        if ($data['recordCount']) {
            $recordData = $data['recordValues'];
            for ($i = 0; $i < $data['recordCount']; $i++) {
                $dataSetInfo = array('dataset_id' => $recordData["COL_META_COLL_ATTR_VALUE"][$i],
                    'path' => $recordData["COL_COLL_NAME"][$i]
                );

                get_instance()->yodaprods->getMetaDataForIntakeDataset(get_instance()->rodsuser->getRodsAccount(),
                    $recordData["COL_META_COLL_ATTR_VALUE"][$i],
                    $dataSetInfo);

                $dataSets[] = new Dataset($dataSetInfo);
            }
        }

        // Collect datasets from files
        $iRodsColumns = array(
            "COL_META_DATA_ATTR_VALUE" => NULL,
            "COL_COLL_NAME" => NULL,
        );

        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', 'like', $referencePath . '/%');
        $condition->add('COL_META_DATA_ATTR_NAME', '=', 'dataset_toplevel');

        $data = array();
        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        if ($data['recordCount']) {
            $recordData = $data['recordValues'];
            for ($i = 0; $i < $data['recordCount']; $i++) {
                $dataSetInfo = array('dataset_id' => $recordData["COL_META_DATA_ATTR_VALUE"][$i],
                    'path' => $recordData["COL_COLL_NAME"][$i]
                );

                get_instance()->yodaprods->getMetaDataForIntakeDataset(get_instance()->rodsuser->getRodsAccount(),
                    $recordData["COL_META_DATA_ATTR_VALUE"][$i],
                    $dataSetInfo);

                $dataSets[] = new Dataset($dataSetInfo);
            }
        }

        // Collect data from files that are located in the folder itself as these fall out the query above.
        $iRodsColumns = array(
            "COL_META_DATA_ATTR_VALUE" => NULL,
            "COL_COLL_NAME" => NULL,
        );

        $select = new RODSGenQueSelFlds(array_keys($iRodsColumns), array_keys($iRodsColumns));

        $condition = new RODSGenQueConds();
        $condition->add('COL_COLL_NAME', '=', $referencePath);
        $condition->add('COL_META_DATA_ATTR_NAME', '=', 'dataset_toplevel');

        $data = array();
        get_instance()->yodaprods->queryGeneral(get_instance()->rodsuser->getRodsAccount(),
            $select,
            $condition,
            $data
        );

        if ($data['recordCount']) {
            $recordData = $data['recordValues'];
            for ($i = 0; $i < $data['recordCount']; $i++) {
                $dataSetInfo = array('dataset_id' => $recordData["COL_META_DATA_ATTR_VALUE"][$i],
                    'path' => $recordData["COL_COLL_NAME"][$i]
                );

                get_instance()->yodaprods->getMetaDataForIntakeDataset(get_instance()->rodsuser->getRodsAccount(),
                    $recordData["COL_META_DATA_ATTR_VALUE"][$i],
                    $dataSetInfo);

                $dataSets[] = new Dataset($dataSetInfo);
            }
        }

        return true;
    }

    /**
     * @param $fileName
     * @param $exclusion_patterns
     *
     * @return bool
     * is file to be taken into account or, as set in the config,
     */
    private function _fileIsToBeViewed($fileName, $exclusion_patterns)
    {
        $view = true;
        foreach($exclusion_patterns as $pattern){
            if(fnmatch($pattern,$fileName)){
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * @param $studyID
     *
     * @return array
     *
     * Find all datasets in the vault for $studyID.
     * Include file count and total file size as well as dataset meta data version, experiment type, pseudocode and wave
     */
    function exportVaultDatasetInfo($studyID)
    {
        $tempCollections = array(); // holds all collections in the vault for $studyID. A collectionn is not necessarily a datasets
        $dataSets = array(); // holds all valid found datasets (dataset_date_created must be present)

        $iRodsColumns = array(
            "COL_COLL_NAME" => NULL,
            "COL_COLL_PARENT_NAME" => NULL,
            "COL_META_COLL_ATTR_NAME" => NULL,
            "COL_META_COLL_ATTR_VALUE" => NULL
        );

        $data = get_instance()->yodaprods->query(get_instance()->rodsuser->getRodsAccount(),
            $iRodsColumns,
            '/' . get_instance()->config->item('rodsServerZone') . '/home/grp-vault-' . $studyID . '%',
            'COL_META_COLL_ATTR_NAME',
            array('dataset_date_created', 'version', 'pseudocode', 'wave', 'experiment_type')
        );

        if($data['recordCount']) {
            $columns = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {

                $index = $columns["COL_COLL_NAME"][$i];

                $metaName = $columns["COL_META_COLL_ATTR_NAME"][$i];
                $metaValue = $columns["COL_META_COLL_ATTR_VALUE"][$i];

                $interestingMeta = false;

                switch ($metaName) {
                    case 'dataset_date_created':
                        $metaValue = intval($metaValue);
                    case 'version':
                    case 'pseudocode':
                    case 'wave':
                    case 'experiment_type':
                        $interestingMeta = true;
                        break;
                }
                if($interestingMeta) {
                    $tempCollections[$index][$metaName] = $metaValue;
                }
            }
        }

        // now find the real datasets within the collections gathered
        foreach($tempCollections as $path => $meta) {
            if(isset($meta['dataset_date_created'])) {

                // fill array with only valid datasets
                $dataSets[$path] = $meta; // create true datasets from collections & copy the meta info

                $dataSets[$path]['totalFileSize'] = 0; // add for file totalisation purposes
                $dataSets[$path]['totalFiles'] = 0;
            }
        }

        // now process file information for the study
        $iRodsColumns = array(
            "COL_DATA_NAME" => NULL,
            "COL_COLL_NAME" => NULL,
            "COL_DATA_SIZE" => NULL,
        );

        $data = get_instance()->yodaprods->query(get_instance()->rodsuser->getRodsAccount(),
            $iRodsColumns,
            '/' . get_instance()->config->item('rodsServerZone') . '/home/grp-vault-' . $studyID . '%');

        if($data['recordCount']) {
            $columns = $data['recordValues'];
            for($i=0; $i<$data['recordCount']; $i++) {

                // first check whether the file is part of a dataset.
                $isPartOfSet = false;
                foreach($dataSets as $path=>$set) {
                    // path must be a substring of de collection name
                    if(strpos($columns["COL_COLL_NAME"][$i], $path)!==false) {
                        $isPartOfSet = true;
                        break;
                    }
                }

                if($isPartOfSet) { // process file info further when file belongs to the set
                    $dataSets[$path]['totalFileSize'] += $columns["COL_DATA_SIZE"][$i];
                    $dataSets[$path]['totalFiles']++;
                }
            }
        }

        return $dataSets;
    }
}
