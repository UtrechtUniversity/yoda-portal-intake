<?php
/**
 * @param     $bytes
 * @param int $decimals
 *
 * @return string
 */

    function human_filesize($bytes, $decimals = 2) {
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

/**
 * @param     $alertType
 * @param     $alertNr
 * @param int $alertSubNr
 *
 * @return object
 *
 *    Information to be displayed in standard alert box within intake window.
 *
 * @param string $alertType - any type that bootstrap alert box can handle: {danger, info, success etc }
 * @param string $alertNr - code for translation table (turned out not to remain a number but left the variable name the same none the less
 * @param string $alertSubNr - possibility to present extra (technical) information to user to differentiate from the standard text.
 *
 * @return Object - objectified array holding all info for alert.
 *
 * When $alertSubNr<>0 an extra line will be presented in the alert box holding this $alertSubNr.
 *
 * When $alertNr starts with 'ACCESS_' this means that there is an issue with the selected study or folder within the study.
 * This is  always due to a manual intervention by the user.
 * The alert-box gives the possibility to return to a valid area in that case.
 */

    function pageLoadAlert($alertType, $alertNr, $alertSubNr=0)
    {
        return (object) array('alertType'=>$alertType,
            'alertNr' => $alertNr,
            'alertSubNr' => $alertSubNr
        );
    }

/**

 * Present error page when study (or folder within study) is erroneous
 * userIsAllowed parameter takes care of not entering the part that holds all the relevant information within the view.
 */
 function showErrorOnPermissionExceptionByValidUser($controller, $error, $contentPath, $referenceContext = 'intake')
{
   // $controller->data['userIsAllowed'] = FALSE; // will prevent access to the view part with all the relevant data

    $controller->session->set_userdata('alertOnPageReload',pageLoadAlert('danger',$error));

  //  $controller->data['content'] = $contentPath;
  //  $controller->data['referenceContext'] = $referenceContext; // depending on the reference context a user gets different options in the error-dialog

//    $controller->load->view('template', $controller->data);
}
