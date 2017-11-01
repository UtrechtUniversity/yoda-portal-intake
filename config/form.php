<?php
/**
 * Name as ID
 * 
 * If set to TRUE an 'id' attribute with the value of the 'name' will be
 * generated automatically in all elements (where applicable)
 */
$config['nameasid'] = TRUE;

/**
 * Replace
 * 
 * These parameters set the replace vs. combine behaviour for
 * multi-value attributes defined in $config['globals'] and $config['defaults']
 *
 * 1st parameter sets CLASSES replacement behaviour
 * 2nd parameter sets STYLES replacement behaviour
 * 3rd parameter sets SCRIPTS ('on...') replacement behaviour
 */
$config['replace'] = 'FALSE|TRUE|TRUE';

/**
 * XSS Clean Post Data
 * 
 * If set to TRUE all post data retrieved via $this->get_post()
 * will be xss_cleaned automatically
 */
$config['xss_clean_post'] = TRUE;

/**
 * Prefixes and Suffixes
 * 
 * Wraps these prefixes and suffixes around elements
 * Please use double quotes when using control characters (e.g. "\n")
 */
$config['group_prefix'] 	= '<div class="radiogroup">';		// wraps around checkgroups and radiogroups
$config['group_suffix'] 	= '<div class="clr"></div></div>';
$config['element_prefix'] 	= '';		// wraps around all elements
$config['element_suffix'] 	= '<br />';
$config['label_prefix'] 	= '';		// wraps around labels
$config['label_suffix'] 	= '';
$config['field_prefix'] 	= ''; 		// wraps around fieldsets (inside), inputs, buttons, textareas, selects, captchas & recaptchas
$config['field_suffix'] 	= '';

/**
 * Error Tags
 * 
 * Opening tag of a single error, e.g. '<span class="error">- '
 * Closing tag of a signle error, e.g. '</span>'
 *
 * This allows you to wrap all single errors within a styled element
 */
$config['error_open'] = '';
$config['error_close'] = '<br />';

/**
 * Error String Tags
 * 
 * Opening tag of the error string, e.g. '<div id="errors">'
 * Closing tag of the error string, e.g. '</div>'
 *
 * This allows you to wrap all single errors within a styled element
 */
$config['error_string_open'] = '<div style="color: #ff0000;">';
$config['error_string_close'] = '</div>';

/**
 * Error Class
 * 
 * This class will be added to the element and its label if validation rules do not pass
 */
$config['error_class'] = '';

/**
 * Inline Error
 * 
 * If set to 'TRUE' the error message will be shown directly after the element
 * You can also set the inline error message opening and closing tag.
 */
$config['error_inline'] = FALSE;
$config['error_inline_open'] = '<span style="color: #ff0000;">';
$config['error_inline_close'] = '</span>';

/**
 * Error Flag
 * 
 * This class will be added to the element and its label if validation rules do not pass
 * To include the error message with an image title attribute you can use the placeholder {error}
 */
$config['error_flag'] = '';

/**
 * Global Attributes
 * 
 * Define global standard attributes
 */
$config['globals'] = array();

/**
 * Default Attributes
 * 
 * Define standard attributes to element types or specific elements (by name)
 * 
 * NEW!! 
 * To change label attributes for an element type or specific element name
 * add 'label' => array('attribute' => 'value') to the respective array
 * (see 'checkbox' for example)
 */
$config['defaults'] = array(
	'label' => array(										// all label configuration is done here now!!
		'position' => 'before',
		'class' => 'left',
		'required_class' => '',
		'required_flag' => '*'
	),
	'fieldset' => array(
		'element_suffix' => ''								// do not use an element_suffix for fieldsets
	),
	'text' => array(
		'onkeypress' => ""		// reset border color after error
	),
	'textarea' => array(
		'label' => array('class' => 'textarea')
	),
	'password' => array(
		'onkeypress' => ""		// reset border color after error
	),
	'upload' => array(
		'upload_path' => FCPATH . 'file_uploads/',
		'allowed_types' => 'pdf',
		'max_size' => 30750
	),
	'checkbox' => array(
		'element_suffix' => '<br />',								// do not use an element_suffix for checkboxes
		'class' => 'check',									// assign a CSS class to the <input type="checkbox" />
		'label' => array(
			'position' => 'after',							// display the label after the actual checkbox
			'class' => 'check'								// assign a CSS class to the <label>
		)
	),
	'radio' => array(
		'element_suffix' => '',
		'class' => 'check',
		'label' => array(
			'position' => 'after',
			'class' => 'check'
		)
	),
	'submit' => array(
		'element_suffix' => '',								// do not use an element_suffix for submit buttons
		'class' => 'button',
	),
	'reset' => array(
		'class' => 'button'
	),
	// always define defaults for specific elements (below) after defaults by element type (above)
	'multiple' => array(
		'style' => 'height: 120px'
	)
);

/**
 * Rule translations from Form_validation to Livevalidation
 */
$config['rule_translations'] = array('valid_email'  => array('name'         => 'email',
															 'error_params' => array('failureMessage')
															 ),
									 'min_length'   => array('name'         => 'length',
															 'param'        => 'minimum',
															 'error_params' => array('wrongLengthMessage', 'tooShortMessage', 'tooLongMessage')
															 ),
									 'max_length'   => array('name'         => 'length',
															 'param'        => 'maximum',
															 'error_params' => array('wrongLengthMessage', 'tooShortMessage', 'tooLongMessage')
															 ),
									 'exact_length' => array('name'         => 'length',
															 'param'        => 'is',
															 'error_params' => array('wrongLengthMessage', 'tooShortMessage', 'tooLongMessage')
															 ),
									 'numeric'  =>     array('name'         => 'numericality',
															 'error_params' => array('notANumberMessage')
															 ),
									 'integer'  =>     array('name'         => 'numericality',
															 'param'        => 'onlyInteger',
															 'error_params' => array('notANumberMessage')
															 ),
									 'required' =>     array('name'         => 'presence',
															 'error_params' => array('failureMessage')
															 ),
									 'regex_match' =>  array('name'         => 'format',
															 'param'        => 'pattern',
															 'error_params' => array('failureMessage')
															 ),
									 'callback_required_select' => array('name' => 'format',
																		 'param' => array('pattern' => ''),
																		 'error_params' => array('failureMessage')
																		 )
									);
/**
 * Default settings to pass on to the Livevalidation class
 */
$config['livevalidation_default_params'] = array('validMessage' => ' ');

/**
 * The path to the img used as hint button relative to the base url
 */
$config['hint_img_url']   = 'images/hint_icon.png';
$config['hint_img_class'] = 'hint_icon';

/**
 * Recaptcha Settings
 * 
 * Please provide your public and private key
 * to make the recaptcha element work
 */
$config['recaptcha_api_server']			= 'http://api.recaptcha.net';
$config['recaptcha_api_secure_server']	= 'https://api-secure.recaptcha.net';
$config['recaptcha_ssl']				= 'FALSE';
$config['recaptcha_verify_server']		= 'api-verify.recaptcha.net';
$config['recaptcha_key_public']			= '';	// Complete with your key
$config['recaptcha_key_private']		= '';	// Complete with your key
$config['recaptcha_theme']				= 'white';
$config['recaptcha_lang']				= 'en';	// Valid language codes on http://recaptcha.net/apidocs/captcha/client.html

/* End of file form.php */
/* Location: ./application/config/form.php */