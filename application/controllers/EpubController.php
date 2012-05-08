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
		public function __construct() 
		{
			require_once('application/Bootstrap.php');
			$this->bs = Bootstrap::singleton();
			$this->dfcTools = $this->bs->getTools();
		}
	
		/**
		 * createEpubAction
		 * Set options, pass to instance of EpubModel()
		 */	
		public function createEpubAction() {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
				),
				'src' => 'application/example/manuscript.docx'
			);
			$epub = new EpubModel();
			$epub->createEpub($options);
		}
	}