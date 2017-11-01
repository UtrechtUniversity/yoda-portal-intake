<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Extension of Form_validation for Superform
 */ 
 
class MY_Form_validation extends CI_Form_validation
{
	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	function run($group = '')
	{
		$post = $this->CI->input->post();

		// Do we even have any data to process?  Mm?
		if (count($post) == 0)
		{
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) == 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) == 0)
			{
				return FALSE;
			}

			// Is there a validation rule for the particular URI being accessed?
			$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

			if ($uri != '' AND isset($this->_config_rules[$uri]))
			{
				$this->set_rules($this->_config_rules[$uri]);
			}
			else
			{
				$this->set_rules($this->_config_rules);
			}

			// We're we able to set the rules correctly?
			if (count($this->_field_data) == 0)
			{
				log_message('debug', "Unable to find validation rules");
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('form_validation');

		//print_r($this->_field_data);

		// Cycle through the rules for each field, match the
		// corresponding $_POST item and test for errors
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.

			if ($row['is_array'] == TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($post, $row['keys']);
			}
			else
			{
				if (isset($post[$field]) AND $post[$field] != "")
				{
					$this->_field_data[$field]['postdata'] = $post[$field];
				}
			}

			//Superform edit for the ~ and ` 
			$rules = array();
			$rule = $row['rules'];
			$ignorepipes = false;
			$current = 0;
			for($i = 0; $i < strlen($rule); $i++)
			{
				//check if we'd like to ignore pipes
				if(substr($rule, $i, 1) == '~')
				{
					$ignorepipes = true;
				}
				//check if we'd like to stop ignoring pipes
				elseif(substr($rule, $i, 1) == '`')
				{
					$ignorepipes = false;
				}
				//detect a not-to-be-ignored pipe
				elseif(substr($rule, $i, 1) == '|' && $ignorepipes == false)
				{
					//make sure we grab out the ~ and `
					$rules[] = substr(str_replace('~', '', str_replace('`', '', $rule)), $current, $i - $current);
					$current = $i + 1;
				}
			}
			$rules[] = substr(str_replace('~', '', str_replace('`', '', $rule)), $current);
			
			$this->_execute($row, $rules, $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);

		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		// No errors, validation passes!
		if ($total_errors == 0)
		{
			return TRUE;
		}
		
		// Validation fails
		return FALSE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @access	public
	 * @param	string
	 * @param	regex
	 * @return	bool
	 */
	function regex_match($str, $regex)
	{
		/*if ( ! preg_match('' . $regex . '$', $str))
		{
			return FALSE;
		}*/		
		
		return  TRUE;
	}
}

/* End of file Superform.php */
/* Location: ./application/libraries/Superform.php */