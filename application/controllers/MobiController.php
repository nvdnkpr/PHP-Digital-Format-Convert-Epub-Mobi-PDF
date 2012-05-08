<?php
/**
 * @defgroup Mobi
 */
/**
 * @file application/controllers/MobiController.php
 * Distributed under the GNU GPL v2. For
 * @class MobiController
 * @ingroup Mobi
 * @brief Class defining operations for Mobi conversion from Microsoft Word Files
 */

	class MobiController 
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
		 * createMobiAction
		 * Set options, pass to instance of MobiModel()
		 */	
		public function createMobiAction() {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
				),
				'src' => 'application/example/manuscript.docx'
			);
			$epub = new MobiModel();
			$epub->createMobi($options);
		}
	}