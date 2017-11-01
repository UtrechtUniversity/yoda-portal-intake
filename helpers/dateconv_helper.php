<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Helper for translating dates between application format and database format
 */

// --------------------------------------------------------------------

/**
 * Change a database date string to a user interface string
 *
 * @param string $sqldate - the date as it was saved in the database
 * @return string $uidate - the input date but formatted for the user interface
 */
if ( ! function_exists('date_sql_to_ui'))
{

	function date_sql_to_ui($sqldate)
	{
		// Default date format visible to the user
		$dateformat_ui = 'd-m-Y';
		// Default date format as saved in the database
		$dateformat_sql = 'Y-m-d';

		$CI =& get_instance();

		// translate empty string to empty string
		if (is_null($sqldate) || $sqldate==='') return '';

		// create a date object from the sqldate string
		$date = date_create_from_format($dateformat_sql, $sqldate);

		// return a string formatted according to the config dateformat_ui
		return date_format($date, $dateformat_ui);
	}
}

/**
 * Change a database user interface string to a date string
 *
 * @param string $uidate - the date as it was saved in the database
 * @return string        - the input date but formatted for the user interface
 */
if ( ! function_exists('date_ui_to_sql'))
{
	function date_ui_to_sql($uidate)
	{
		// Default date format visible to the user
		$dateformat_ui = 'd-m-Y';
		// Default date format as saved in the database
		$dateformat_sql = 'Y-m-d';
		
		$CI =& get_instance();

		// translate empty string to empty string
		if (is_null($uidate) || $uidate==='') return NULL;

		// create a date object from the uidate string
		$date = date_create_from_format($dateformat_ui, $uidate);

		// return a string formatted according to the config dateformat_sql
		return date_format($date, $dateformat_sql);
	}
}

/* End of file dateconv_helper.php */
/* Location: ./application/helpers/dateconv_helper.php */
