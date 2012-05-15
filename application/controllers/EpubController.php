<?php
/**
 * @defgroup Epub
 */
/**
 * @file application/controllers/EpubController.php
 * Distributed under the GNU GPL v2. For
 * @class EpubController
 * @ingroup Epub
 * @brief Class defining operations for EPUB conversion from Microsoft Word Files
 */

	class EpubController 
	{
		
		/**
		 * Constructor
		 * Instantiate bootstrap, get instance of conversion tools
		 */
		public function __construct(EpubModel $model, array $tools, TransformModel $transform) 
		{
			$this->epubModel = $model;
			$this->tools = $tools;
			$this->transform = $transform;
		}
	
		/**
		 * createEpubAction
		 * Set options, pass to instance of EpubModel()
		 */	
		public function createEpubAction(array $customOptions = null) {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
					'Identifier' => '111-999-222'
				),
				'src' => 'application/example/manuscript.docx'
			);
			if ($customOptions) $options['customOptions'] = $customOptions;
			$this->epubModel->createEpub($this->transform, $options);
		}
	}