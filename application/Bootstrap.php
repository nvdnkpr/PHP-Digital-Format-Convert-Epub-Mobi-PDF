<?php
/**
 * @defgroup Bootstrap
 */
/**
 * @file application/Bootstrap.php
 * Distributed under the GNU GPL v2. For
 * @class Bootstrap
 * @ingroup Bootstrap
 * @brief Class defining bootstrap operations - this class uses the Singleton design pattern: http://php.net/manual/en/language.oop5.patterns.php
 */
	
	date_default_timezone_set('GMT');
	global $libraryPath;

	
	class Bootstrap 
	{
		private static $instance;
		private $tools = array();
		
		/**
		 * Singleton
		 * If instance of class doesn't exist, autoload application models and create new instance of class
		 * If instance of class does exist, return instance
		 * @return object
		 */
		public static function singleton() 
		{	
			if (!isset(self::$instance)) {
				foreach (glob("application/models/*.php") as $filename) {
			    	include $filename;
				}
           		$className = __CLASS__;
            	self::$instance = new $className;
        	}
        	return self::$instance;
		}
		
		/**
		 * Constructor
		 * Private method, as per Singleton design pattern
		 */
		private function __construct() 
		{
		}
		
		/**
		 * getTools
		 * Load files from library, push instance of tools into array and return array
		 * @return array
		 */
		public function getTools() 
		{
			$libraryPath = "library";
			require_once "$libraryPath/epub/Epub.inc.php";
			require_once "$libraryPath/phpDocx/classes/TransformDoc.inc.php";
			require_once "$libraryPath/mobi/Mobi.inc.php";
			require_once "$libraryPath/mpdf/mpdf.inc.php";

			$this->tools['epubConverter'] = new EPub();
			$this->tools['transformDoc'] = new TransformDoc;
			$this->tools['mobiConverter'] = new MOBI();
			$this->tools['pdfConverter'] = new mPDF();

			return $this->tools;
		}
		
	}