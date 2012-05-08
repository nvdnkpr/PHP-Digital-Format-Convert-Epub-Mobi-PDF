<?php
/**
 * @defgroup Epub
 */
/**
 * @file application/models/EpubModel.php
 * Distributed under the GNU GPL v2. For
 * @class EpubModel
 * @ingroup Epub
 * @brief Class defining low-level operations for Epub conversion from Microsoft Word Files
 */

	class EpubModel 
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
		 * createEpub
		 * Get instance of TransformModel, get HTML from manuscript, pass to conversion tools and send Epub to browser
		 * @param $options array output options and manuscript src
		 */	
		public function createEpub($options) 
		{
			$transform = new TransformModel();
			$html = $transform->getDocumentHTML($options['src']);
			$epub = $this->dfcTools['epubConverter'];
			$epub->addChapter("Body", "Body.html", $html, true);
			$zipData = $epub->sendBook("Example");
		} 
		
		
	}