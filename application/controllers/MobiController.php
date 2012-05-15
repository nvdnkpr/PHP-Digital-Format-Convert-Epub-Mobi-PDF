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
		public function __construct(MobiModel $model, array $tools, TransformModel $transform) 
		{
			$this->mobiModel = $model;
			$this->tools = $tools;
			$this->transform = $transform;
		}
	
		/**
		 * createMobiAction
		 * Set options, pass to instance of MobiModel()
		 */	
		public function createMobiAction(array $customOptions = null) {
			$options = array(
				'options' => array (
					'Title' => 'Conversion Demonstration',
					'Language' => 'en',
					'Publisher' => 'FUBAR Publications',
				),
				'src' => 'application/example/manuscript.docx'
			);
			if ($customOptions) $options['customOptions'] = $customOptions;
			$this->mobiModel->createMobi($this->transform, $options);
		}
	}