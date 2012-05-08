<?php

/**
 * @defgroup CBPPlatform
 */

/**
 * @file classes/CBPPlatform/conv/MobiConvert.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CBPPlatformMobiConvert
 * 
 * @ingroup CBPPlatform
 *
 * @brief Class defining operations MOBI conversion from Microsoft Word Files
 *
 */

	class CBPPlatformMobiConvert {
		
		/**
		 * Constructor.
		 * Imprort the required class files and set properties
		 */
		function __construct() {
			import('classes.CBPPlatform.conv.mobi.Mobi');
			import('classes.CBPPlatform.conv.phpDocx.classes.TransformDoc');
			import('classes.CBPPlatform.conv.cssparse.CSSParser');
			
			$this->fileDir = ".";
			$this->contentStart =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
				. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
				. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
				. "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:mbp='http://www.w3.org/2000/mbp'><!--spoofing the mbp ns to allow mobi-specific markup...-->\n"
				. "<head>"
				. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
				. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
				. "<title>eBook</title>\n"
				. "</head>\n"
				. "<body>\n";
		}
		
		/**
		 * Create a MOBI and export to defined path
		 * @param $dir str directory of the source file to convert
		 * @param $src str filename of the source file to convert
		 * @param $path str path to export the resultant MOBI to
		 * @param $chapters array chapters to convert into a single MOBI
		 * @param $journalId int Id of the journal(imprint)
		 * @param $args array arguments for the conversion (e.g. Description, cover image, etc)
		 */
		function createMobi($dir = null, $src, $path, $chapters = array(), $journalId, $args = array()) {
			$mobi = new MOBI();
			$CBPPlatformDao =& DAORegistry::getDAO('CBPPlatformDAO');
			$imprintType = $CBPPlatformDao->getImprintType($journalId);
			$mobiContent = $this->contentStart;
			
			if (isset($args['cover'])) {
				$mobiContent .= "<img src='" . $args['cover'] . "' style=\"margin: 0px auto;\" />";
			} else {
				if (isset($args['title'])) $mobiContent .= "<h1>" . $args['title'] . "</h1><br />";
				if (isset($args['author'])) $mobiContent .= "<p><strong>" . $args['author'] . "</strong></p>";
			}

			$copyrightStatement = $CBPPlatformDao->getJournalCopyrightStatement($journalId);
			if (!empty($copyrightStatement)) {
				$copyrightStatement = reset($copyrightStatement);
				$mobiContent .= "<mbp:pagebreak /><div style='width: 75%; text-align: center; margin: 0 auto;'><p>" . $copyrightStatement . "</p></div>";
			}
			  
			if (!empty($chapters)) {
				for ($i = 0; $i < count($chapters); $i++) {
					$document = new TransformDoc();
					$document->setStrFile($chapters[$i]['src'], $chapters[$i]['dir']);
					$document->generateXHTML(); //problem, here
					$document->validatorXHTML();
					$contentPreg = $this->stripTags($document->getStrXHTML());
					$introduction = "";
					if ($imprintType == "collection") {
						if ($chapters[$i]['description'] != "") {
							$introduction =  "<mbp:pagebreak /><div class='submissionIntroEpub'><h1>" . $chapters[$i]['author'] . "</h1><div style='font-style: italic;'>" . $this->stripTags($chapters[$i]['description'], true) . "</div></div></body></html>";
						}
					}
					$mobiContent .= $introduction . $contentPreg;
				}	
			} else {
				$content = new TransformDoc();
				$content->setStrFile($src, $dir);
				$content->generateXHTML();
				$content->validatorXHTML();
				$contentPreg = $this->stripTags($content->getStrXHTML());
				$mobiContent .= $contentPreg;
			}
			$mobiContent .= "</body></html>";
			
			isset($args['title']) ? $options['title'] = $args['title'] : $options['title'] = "No Title";
			isset($args['description']) ? $options['subject'] = $args['description'] : $options['subject'] = "No description";
			isset($args['author']) ? $options['author'] = $args['author'] : $options['author'] = "No author";	
			isset($args['isbn']) ? $options['uniqueID'] = $args['isbn'] : $options['uniqueID'] = "No isbn";
			$mobi->setData($mobiContent);
			$mobi->setOptions($options);
			$mobi->save($path);
		}
		
		/**
		 * Strip HTML tags, inline styles and convert input src to semantic, styled HTML
		 * @param $src str input HTML to be processed
		 * @param $all bool true/false to strip ALL HTML tags
		 * @return $str str stripped HTML
		 */
		function stripTags($src, $all = false) {
			$dom = new DOMDocument;
			@$dom->loadHTML('<?xml encoding="UTF-8">' . $src);
			$xPath = new DOMXPath($dom);
			$elements = $xPath->query('//p');
			foreach($elements as $element){
				if(strstr($element->getAttribute('class'), 'Heading1') !== FALSE){
			      $newElement = $dom->createElement('h1', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	}
				if(strstr($element->getAttribute('class'), 'Heading2') !== FALSE){
			      $newElement = $dom->createElement('h2', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	}
				if(strstr($element->getAttribute('class'), 'Heading3') !== FALSE){
			      $newElement = $dom->createElement('h3', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	}
				if(strstr($element->getAttribute('class'), 'Heading4') !== FALSE){
			      $newElement = $dom->createElement('h4', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	}
			}
			//css parser
			$elements = $xPath->query('//p|//span');
			foreach($elements as $element){
				$oParser = new CSSParser("p{" . $element->getAttribute('style') . "}");
				$oCss = $oParser->parse();
				foreach($oCss->getAllRuleSets() as $oRuleSet) {
				    $oRuleSet->removeRule('line-');
				 	$oRuleSet->removeRule('margin-');
					$oRuleSet->removeRule('font-family');
					$oRuleSet->removeRule('font-size');
					$oRuleSet->removeRule('color');
					$indent = $oRuleSet->getRules('text-indent');
					if (is_array($indent) && isset($indent['text-indent'])) {
						$value = $indent['text-indent']->getValue();
						if ($value != null) {
							$value->setSize('4');
							$value->setUnit('mm');
						}
					}
				}
				$css = $oCss->__toString();
				$css = substr_replace($css, '', 0, 3);
				$css = substr_replace($css, '', -1, 1);
				$element->setAttribute('style', $css);
			}
			$src = $dom->saveHTML();
			
			$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript 
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly 
               '@<![\s\S]*?--[ \t\n\r]*>@',         // Strip multi-line comments including CDATA 
				'/(?si)<span\s+class\s*=\s*"-H"\s* style="letter-spacing: 0pt;">(.*?)<\/span>/',
			); 
			$src = preg_replace($search, array('','','','$1'), $src); 
			if ($all == false) { 
				$src = strip_tags($src, "<p><br><img><span><strong><em><h1><h2><h3><h4>");
			} else {
				$allow = "<p>";
				$src = strip_tags($src, $allow);
				$src = $this->clean_inside_tags($src, $allow);
				$src = preg_replace('#<p[^>]*>(\s|&nbsp;?)*</p>#', '', $src);
			}
			$src = str_replace("<h1>", "<mbp:pagebreak /><h1>", $src);
			$src = str_replace("#", "<br />", $src);
			$src = str_replace("***", "<br /><br />", $src);
			return $src;
		}
		
		/**
		 * Remove any inline styles from the selected tags
		 * @param $txt str input HTML to be processed
		 * @param $tags str tags to remove styles from
		 * @return $txt str resultant HTML
		 */
		function clean_inside_tags($txt,$tags){
	 		preg_match_all("/<([^>]+)>/i",$tags,$allTags,PREG_PATTERN_ORDER);
			foreach ($allTags[1] as $tag){
				$txt = preg_replace("/<".$tag."[^>]*>/i","<".$tag.">",$txt);
			}
			return $txt;
		}
	}