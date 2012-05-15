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
			$pdf = $this->dfcTools['pdfConverter'];
			if (!$options['customOptions']['html']) { //if no html has been passed, transform the Word Document
				$html = '<?xml encoding="UTF-8">' . $transform->getDocumentHTML($options['src']);
			} else {
				$html = $options['customOptions']['html'];
			}
			$pdf->WriteHTML($html);
			$pdf->Output();
		} 
		
	}