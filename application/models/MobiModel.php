<?php
/**
 * @defgroup Mobi
 */
/**
 * @file application/models/MobiModel.php
 * Distributed under the GNU GPL v2. For
 * @class MobiModel
 * @ingroup Mobi
 * @brief Class defining low-level operations for Mobi conversion from Microsoft Word Files
 */

	class MobiModel 
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
		 * createMobi
		 * Get instance of TransformModel, get HTML from manuscript, pass to conversion tools and send Mobi to browser
		 * @param $options array output options and manuscript src
		 */	
		public function createMobi($options) 
		{
			$transform = new TransformModel();
			$html = $transform->getDocumentHTML($options['src']);
			$mobi = $this->dfcTools['mobiConverter'];
			$mobi->setData($html);
			$zipData = $mobi->download("Example.mobi");
		} 
		
	}