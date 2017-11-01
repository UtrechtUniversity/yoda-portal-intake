<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter Charconv Helpers
 * Please make sure that the charset in config is set to iso-8859-1
 *
 * @package		FSC_custom
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Rik van Duijn <ik@rikvanduijn.nl>
 */

// --------------------------------------------------------------------

/**
 * Replace all diacritics in a string, and clean the string up for url / filename use.
 * If specified as filename, the "." will not be removed in the cleaning proces.
 *
 * @access	public
 * @param	string	$toClean	the string that needs to be cleaned for URI and filename use.
 * @param	bool	$isFilename	should be TRUE if $toClean is a filename for preservation of the "." in the extension. FALSE by default.
 * @param	bool	$forUrl		indicates if the cleaned string is to be used for Url or Filename use for extra rigid cleaning. TRUE by default.
 * @return	string				$toClean, with replaced diacritics, removed or replaced illegal chars. 
 */
if ( ! function_exists('replace_diacritics'))
{
	function replace_diacritics($toClean, $isFilename = FALSE, $forUrl = TRUE)
	{
		$chars['normalizeChars'] = array(
			'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A1', 'Á'=>'A2', 'Â'=>'A3', 'Ã'=>'A4', 'Ä'=>'A5', 
			'Å'=>'A6', 'Æ'=>'AE', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 
			'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 
			'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 
			'å'=>'a', 'æ'=>'ae', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 
			'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 
			'ü'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', 'Œ'=>'OE', 'œ'=>'oe',
			'¨'=>'-', 'Ÿ'=>'Y', '“' => '"', '”' => '"', '‘' => '\'', '’' => '\''
		);

		$chars['extraChars'] = array(
			'&'=>'-and-', '+'=>'-plus-', '/'=>'-', '\\'=>'-', '?'=>'-', ':'=>'-', ';'=>'-', '@'=>'-at-', '_'=>'-',
			'='=>'-', '$'=>'-USD-', '€'=>'-EUR-', '*'=>'-', '.'=>'-', ','=>'-', '!'=>'-', '('=>'-', ')'=>'-'
		);

		if($isFilename===TRUE)
		{
			$path_parts = pathinfo($toClean);
			$toClean = $path_parts['filename'];
		}
		$toClean	=	strtr($toClean, $chars['normalizeChars']);				//replace all diacritics
		
		if($forUrl===TRUE)
		{
			$toClean	=	strtr($toClean, $chars['extraChars']);					//replace special characters
			$toClean    =   trim(preg_replace('/[^\w\d_ -]/si', '', $toClean));		//remove other illegal chars
			$toClean    =   str_replace(' ', '-', $toClean);						//replace all spaces
			$toClean    =   str_replace('--', '-', $toClean);						//clean up dashes
			$toClean    =   str_replace('--', '-', $toClean);						//clean up dashes some more
			$toClean	=	url_title($toClean);
		}
		

		if($isFilename===TRUE)
		{
			$toClean = $toClean . "." . $path_parts['extension'];
		}
		
		return $toClean; 
	}
}

/* End of file charconv_helper.php */
/* Location: ./system/helpers/charconv_helper.php */