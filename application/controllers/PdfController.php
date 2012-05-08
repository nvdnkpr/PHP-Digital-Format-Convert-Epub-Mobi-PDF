<?php
/**
 * @defgroup Pdf
 */
/**
 * @file application/controllers/PdfController.php
 * Distributed under the GNU GPL v2. For
 * @class PdfController
 * @ingroup Pdf
 * @brief Class defining operations for Pdf conversion from Microsoft Word Files
 */

	class PdfController 
	{
		
		/**
		 * Constructor
		 * Instantiate bootstrap, get instance of conversion tools
		 */
		public function __construct() 
		{
			require_once('application/Bootstrap.php');
			$this->bs = Bootstrap::singleton();
			$this->dfcTools = $this->bs->getTools();
		}
	
		/**
		 * createPdfAction
		 * Set options, pass to instance of PdfModel()
		 */	
		public function createPdfAction() {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
				),
				'src' => 'application/example/manuscript.docx'
			);
			$pdf = new PdfModel();
			$pdf->createPdf($options);
		}
	}