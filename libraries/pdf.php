<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * pdf
 *
 * Wrapper for dompdf
 *
*
 */
//class pdf {
//
//    // holds the dompdf object
//     private $dompdf;
//
//    /**
//     * contructor. includes the PHPExcel library from third party folder
//     */
//    public function __construct()
//    {
//
//        // initialise the reference to the codeigniter instance
//        require_once APPPATH.'third_party/dompdf/load.php';
//
//        $this->dompdf=$class;
//
//        var_dump($class);
//    }
//
//}
// require_once APPPATH.'third_party/dompdf/load.php';
require_once(APPPATH.'third_party/dompdf/dompdf_config.inc.php');

/**
 * Class PDFRenderer
 */
class PDF
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
