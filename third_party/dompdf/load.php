<?php

/**
 * DOMPDF autoload function
 *
 * If you have an existing autoload function, add a call to this function
 * from your existing __autoload() implementation.
 *
 * @param string $class
 */
require_once("dompdf_config.inc.php");

/**
 * Class PDFRenderer
 */
class PDFRenderer
{

	/**
	 * @var null|DOMPDF
	 */
	protected $_renderer = null;

	/**
	 * Initialize
	 */
	public function __construct(array $options = array())
	{
//		spl_autoload_register('PDFRenderer::DOMPDF_autoload', true);

		$this->_renderer = new DOMPDF();
		$this->_renderer->set_base_path(DIRNAME(__FILE__));

		$orientation = isset($options['orientation']) ? $options['orientation'] : 'landscape';
		$this->_renderer->set_paper('a4', $orientation);

	}

	/**
	 * @param $html
	 *
	 * @return $this
	 */
	public function setHtml($html)
	{
		$this->_renderer->load_html($html);

		return $this;
	}

	/**
	 * @param bool $stdout
	 *
	 * @return bool|string
	 */
	public function render($stdout = true)
	{
		$this->_renderer->render();

		if (!$stdout) {
			ob_start();
		}

		$output = $this->_renderer->output();

		if (!$stdout) {
			return ob_get_clean();
		}

		return $output;
	}

	/**
	 * @param $filename
	 *
	 * @return bool
	 */
	public function outputWithFilename($filename)
	{
		$this->_renderer->render();

		$this->_renderer->stream($filename);

		return true;
	}

	/**
	 * @param $class
	 */
	static public function DOMPDF_autoload($class) {
		$filename = mb_strtolower($class) . ".cls.php";
//		require_once(DOMPDF_INC_DIR . "/" . $filename);
	}

}

$options = isset($options) ? $options : array();
$class = new PDFRenderer($options);
