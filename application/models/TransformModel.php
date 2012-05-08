<?php
/**
 * @defgroup Transform
 */
/**
 * @file application/models/TransformModel.php
 * Distributed under the GNU GPL v2. For
 * @class TransformModel
 * @ingroup Transform
 * @brief Class defining transformation operations for DOCX to HTML
 */

	class TransformModel 
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
		 * getDocumentHTML
		 * Get instance of TransformDoc tool, generate and validate xHTML
		 * @param $src str path to manuscript file
		 * @return string
		 */	
		public function getDocumentHTML($src) 
		{
			$transformDoc = $this->dfcTools['transformDoc'];
			$transformDoc->setStrFile($src);
			$transformDoc->generateXHTML();
			$transformDoc->validatorXHTML();
			return $transformDoc->getStrXHTML();
		}
		
	}