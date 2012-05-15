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
		public function __construct(array $tools) 
		{
			$this->dfcTools = $tools;
		}
		
		/**
		 * createEpub
		 * Get instance of TransformModel, get HTML from manuscript, pass to conversion tools and send Epub to browser
		 * @param $options array output options and manuscript src
		 */	
		public function createEpub(TransformModel $transform, array $options) 
		{
			$epub = $this->dfcTools['epubConverter'];
			if (!$options['customOptions']['html']) { //if no html has been passed, transform the Word Document
				$html = strip_tags($transform->getDocumentHTML($options['src']), "<p><script><style><span>"); //an example of basic 'content cleansing'
			} else {
				$html = $options['customOptions']['html'];
			}
			$epub->setTitle($options['options']['Title']); //setting specific options to the EPub library
			$epub->setIdentifier($options['options']['Identifier'], EPub::IDENTIFIER_URI); 
			$epub->addChapter("Body", "Body.html", $html);
			$epub->finalize();
			$zipData = $epub->sendBook("Example");
		} 
		
		
	}