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
		public function __construct(array $tools) 
		{
			$this->dfcTools = $tools;
		}
		
		/**
		 * createPdf
		 * Get instance of TransformModel, get HTML from manuscript, pass to conversion tools and send Pdf to browser
		 * @param $options array output options and manuscript src
		 */	
		public function createPdf(TransformModel $transform, array $options) 
		{
			$html = $transform->getDocumentHTML($options['src']);
			$pdf = $this->dfcTools['pdfConverter'];
			$pdf->WriteHTML('<?xml encoding="UTF-8">' . $html);
			$pdf->Output();
		} 
		
	}