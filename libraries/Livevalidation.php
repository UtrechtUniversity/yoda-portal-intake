<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (JS) Live Validation parser class
 *
 * This class utilizes the JS LiveValidation class from Alec Hill (http://www.alec-hill.com) and can be used to
 * create the JS required to utilize his js LiveValidation class out of php and is meant to be uses in congestion
 * with the SELVFGL (Super Extensible Live Validating Form Generating Library)
 *
 * @package superform
 * @author Bram Couteaux bramcou@gmail.com
 *
 * @param array contains all field objects
 * @param array contains default parameters for all field objects, can be set through the set_field_defaults() method or in the file
 */ 
class Livevalidation
{
	private $fields = array();
	private $field_default_parameters = array();
	
	/**
	 * Creates a new field, checks whether their is a field_id supplied and that there isn't already an field object for that field_id
	 *
	 * @param mixed the parameters to be given to the field (the array must contain a field_id with a set value) or the field_id as string
	 * @return boolean returns the object when it is succesfully created, false otherwise
	 */
	public function create_field($params)
	{
		if(is_array($params))
		{
			if(!isset($params['field_id']))
			{
				return false;
			}
			else
			{
				$id_exists = array_key_exists($params['field_id'] , $this->fields);

				if($id_exists)
				{
					if(!is_object($this->fields[$params['field_id']]))
					{
						$params = array_merge($this->field_default_parameters, $params);
						$field = new Field($params);
						$this->fields[$params['field_id']] = $field;
						
						return $field;
					}
				}
				else
				{
					$params = array_merge($this->field_default_parameters, $params);
					$field = new Field($params);
					$this->fields[$params['field_id']] = $field;
					
					return $field;
				}
				
				return false;
			}
		}

		if(!is_object($this->fields[$params]))
		{
			$allparams = $this->field_default_parameters;
			$allparams['field_id'] = $params;
			$field = new Field($allparams);
			$this->fields[$params] = $field;
			
			return $field;
		}
		
		return false;
	}
	
	/**
	 * Delete a field object
	 *
	 * @param string the field to be deleted
	 * @return boolean returns true
	 */
	public function delete_field($field_id)
	{
		unset($this->fields[$field_id]);
		
		return true;
	}
	
	/**
	 * Creates the complete javascript code from all field objects
	 *
	 * @return string returns the complete javascript code from all field objects
	 */
	public function print_js()
	{
		$output = '';
		if(!empty($this->fields))
		{
			foreach($this->fields as $field)
			{
				$output .= $field->print_js();
			}
			
		}
		
		return $output;
	}
	
	/**
	 * Set default parameters for all fields
	 *
	 * @param array the parameters to be given as an array or empty (if you wanna replace earlier set default params for the JS defaults)
	 * @return boolean returns true when the parameters are in a valid format (param existence isn't checked!)
	 */
	public function set_field_defaults($params = '')
	{
		if(empty($params))
		{
			$this->field_default_parameters = array();
			
			return true;
		}
		elseif(is_array($params))
		{
			foreach($params as $key => $val)
			{
				$this->field_default_parameters[$key] = $val;
			}
			
			return true;
		}
		
		return false;
	}
}

/**
 * Field class
 *
 * This class defines a field, it's parameters and can add, remove and print it's JS validation functions
 *
 * @param string the id of the html form element
 * @param array the parameters for the js instance of livevalidation
 * @param array keys => validation functions, val => array with all parameters for the validation function (key => parameter, val => value)
 * @param array contains all field types supported by live validation
 */ 
class Field
{
	private $field_id;
	private $parameters = array();
	private $validations = array();
	private $supported_element_types = array('text', 'password', 'file', 'checkbox', 'textarea', 'select');	
	
	/**
	 * Constructor can be given either ONLY the htmlid of the field or an array with one ore more parameters
	 * These parameters are passed to the params function (which can also be called later on)
	 * however if there is no field_id passed it will return false
	 *
	 * @param mixed either a string which will be set as the field_id or an array with parameters which must at least contain the field_id
	 * @return boolean if there is no field_id passed (as string or in the array) it will unset the newly made object and return false
	 */
	public function __construct($params = '')
	{
		$this->params($params);
	}
	
	/**
	 * Sets the given parameters, if only a string it will be set as field_id (to make it possible to use constructor to simply set the field_id)
	 *
	 * @param mixed either a string which will be set as the field_id or an array with parameters
	 * @return boolean returns true if any parameter is passed and set otherwise it returns false
	 */
	public function params($params = '')
	{
		if(is_array($params) && !empty($params))
		{
			if(array_key_exists('field_id', $params))
			{
				$this->field_id = $params['field_id'];
				unset($params['field_id']);
			}
			$this->parameters = $params;
		
			return true;		
		}
		elseif(is_string($params) && $params != '')
		{
			$this->field_id = $params;
			
			return true;	
		}
		
		return false;
	}
	
	/**
	 * Adds an new validating method for a particular field to the $validations array, this function only stacks all the validations
	 * it doesn't create the code yet. This will be performed by the print_js function
	 *
	 * @param string the validation function to be used
	 * @param array the parameters to be passed along to the js function (in a key/value pair) e.g. array('min' => 5, 'max' => 10);
	 * @return boolean returns true when the parameters are in a valid format (function/param existence isn't checked!)
	 */
	public function add($function, $function_params = '')
	{		
		if($function_params != '' && !is_array($function_params))
		{
			return false;
		}
		
		$function = ucfirst(strtolower($function));
		
		//if their already are validations than we merge them with the new one's as leading. 
		if(isset($this->validations[$function]))
		{
			$this->validations[$function] = array_merge($this->validations[$function], $function_params);
		}
		else
		{
			$this->validations[$function] = $function_params;
		}
		
		return true;
	}
	
	/**
	 * Removes a specific or all validating methods from the field
	 *
	 * @param string the validation function to be removed, leave empty to remove all validation functions from the field
	 * @return boolean returns true upon removing the validation(s)
	 */
	public function remove($function = '')
	{
		if($function == '')
		{
			$this->validations = array();
		}
		else
		{
			unset($this->validations[$function]);
		}
		
		return true;
	}
	
	/**
	 * Creates the javascript for the field, first it makes the syntax to initialize the LiveValidation class and
	 * than it loops through all validations and adds their syntax
	 *
	 * @return string the javascript code for the field and it's validations
	 */
	public function print_js()
	{
		if($this->field_id == '')
		{
			return false;
		}
		
		$output = "var " . $this->field_id . " = new LiveValidation('" . $this->field_id . "', { ";
		if(!empty($this->parameters))
		{
			foreach($this->parameters as $var => $val)
			{	
				if($val === true)
				{
					$output .= $var . ": true, ";
				}
				elseif(is_string($val))
				{
					$output .= $var . ": '" . $val . "', ";
				}
				else
				{
					$output .= $var . ": " . $val . ", ";
				}
			}
			
			$output = substr($output, 0, -2);
		}
		
		$output .= " });\n";
		
		if(!empty($this->validations))
		{
			foreach($this->validations as $function => $params)
			{
				$output .= $this->field_id . ".add(Validate." . $function . ", { ";
				if(!empty($params))
				{
					foreach($params as $param => $val)
					{
						if($function == 'Format' && $param == 'pattern')
						{
							$output .= $param . ": /" . $val . "/i, ";
						}
						elseif(is_string($val) && $val != 'true')
						{
							$output .= $param . ": '" . $val . "', ";
						}
						else
						{
							$output .= $param . ": " . $val . ", ";
						}
					}
					
					$output = substr($output, 0, -2);
				}
				
				$output .= " } );\n";
			}
		}
		
		return $output;
	}
	
	/**
	 * Get the supported field types
	 *
	 * @return array the supported element types
	 */
	public function get_supported_element_types()
	{
		return $this->supported_element_types;
	}
}

/* End of file Livevalidation.php */
/* Location: ./application/libraries/Livevalidation.php */