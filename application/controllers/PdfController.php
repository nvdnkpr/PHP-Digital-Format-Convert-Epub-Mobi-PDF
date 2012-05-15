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
		public function __construct(PdfModel $model, array $tools, TransformModel $transform) 
		{
			$this->pdfModel = $model;
			$this->tools = $tools;
			$this->transform = $transform;
		}
	
		/**
		 * createPdfAction
		 * Set options, pass to instance of PdfModel()
		 */	
		public function createPdfAction(array $customOptions = null) {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
				),
				'src' => 'application/example/manuscript.docx'
			);
			if ($customOptions) $options['customOptions'] = $customOptions;
			$this->pdfModel->createPdf($this->transform, $options);
		}
	}