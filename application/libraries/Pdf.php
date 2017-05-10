<?php defined('BASEPATH') OR exit('No direct script access allowed');



require_once(dirname(__FILE__) . '/dompdf/autoload.inc.php');

use Dompdf\Dompdf;


class Pdf extends Dompdf{
    /**
     * Get an instance of CodeIgniter
     *
     * @access	protected
     * @return	void
     */
    protected function ci()    {
        return get_instance();
    }

    /**
     * Load a CodeIgniter view into domPDF
     *
     * @access	public
     * @param	string	$view The view to load
     * @param	array	$data The view data
     * @return	void
     */
    public function load_view($view, $data = array())    {
        $html = $this->ci()->load->view($view, $data, TRUE);
        $this->load_html($html);
    }

    function pdf_create($view,$data)
    {

        $html = $this->ci()->load->view($view, $data, TRUE);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        return $dompdf->stream();

    }

}
?>