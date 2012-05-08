<?php
/**
 * @defgroup Pdf
 */
/**
 * @file application/models/PdfModel.php
 * Distributed under the GNU GPL v2. For
 * @class PdfModel
 * @ingroup Pdf
 * @brief Class defining low-level operations for Pdf conversion from Microsoft Word Files
 */

	class PdfModel 
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
		 * createPdf
		 * Get instance of TransformModel, get HTML from manuscript, pass to conversion tools and send Pdf to browser
		 * @param $options array output options and manuscript src
		 */	
		public function createPdf($options) 
		{
			$transform = new TransformModel();
			$html = $transform->getDocumentHTML($options['src']);
			$pdf = $this->dfcTools['pdfConverter'];
			$pdf->WriteHTML('<?xml encoding="UTF-8">' . $html);
			$pdf->Output();
		} 
		
	}