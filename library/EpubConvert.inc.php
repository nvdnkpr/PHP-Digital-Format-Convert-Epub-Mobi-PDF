<?php

/**
 * @defgroup CBPPlatform
 */

/**
 * @file classes/CBPPlatform/conv/EpubConvert.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CBPPlatformEpubConvert
 * 
 * @ingroup CBPPlatform
 *
 * @brief Class defining operations EPUB conversion from Microsoft Word Files
 *
 */
	class CBPPlatformEpubConvert {
		
		/**
		 * Constructor.
		 * Imprort the required class files and set properties
		 */
		function __construct() {
			import('classes.CBPPlatform.conv.epub.EPub');
			import('classes.CBPPlatform.conv.epub.EPubChapterSplitter');
			import('classes.CBPPlatform.conv.phpDocx.classes.TransformDoc');
			import('classes.CBPPlatform.conv.cssparse.CSSParser');

			$this->fileDir = ".";
			$this->stylesheetDir = "styles/ebook/";
			$this->contentStart =
				"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
				. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
				. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
				. "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
				. "<head>"
				. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
				. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
				. "<title>eBook</title>\n"
				. "</head>\n"
				. "<body>\n";
		}
		
		/**
		 * Create an EPUB and export to defined path
		 * @param $dir str directory of the source file to convert
		 * @param $src str filename of the source file to convert
		 * @param $path str path to export the resultant EPUB to
		 * @param $chapters array chapters to convert into a single EPUB
		 * @param $journalId int Id of the journal(imprint)
		 * @param $args array arguments for the conversion (e.g. Description, cover image, etc)
		 */
		function createEpub($dir = null, $src, $path, $chapters = array(), $journalId, $args = array()) {
			$book = new EPub();
			if (isset($args['title'])) {
				$book->setTitle($args['title']);
			} else {
				$book->setTitle("No Title");	
			}
			$book->setLanguage("en");
			$book->setPublisher(Config::getVar('general', 'base_url'), Config::getVar('general', 'base_url'));
			$book->setDate(time());
			$book->setRights("Copyright and licence information specific for the book."); //TODO: import specific copyright/licence information
			$book->setSourceURL(Config::getVar('general', 'base_url'));
			
			$CBPPlatformDao =& DAORegistry::getDAO('CBPPlatformDAO');
			$imprintType = $CBPPlatformDao->getImprintType($journalId);
			
			$stylesheet = $CBPPlatformDao->getImprintStylesheet($journalId);
			$book->addCSSFile("styles.css", "css1", file_get_contents($this->stylesheetDir . "$stylesheet.css"));
			isset($args['description']) ? $book->setDescription($args['description']) : $book->setDescription("No description");
			isset($args['author']) ? $book->setAuthor($args['author'], $args['author']) : $book->setAuthor("No author", "No author");	
			$args['isbn'] != null ? $book->setIdentifier($args['isbn'], EPub::IDENTIFIER_URI) : $book->setIdentifier(Config::getVar('general', 'base_url'), EPub::IDENTIFIER_URI);
			
			if (isset($args['cover'])) {
				$cover = $args['cover'];
				$coverSrc = "
					<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='100%' height='100%' viewBox='0 0 410 597' preserveAspectRatio='xMidYMid meet'>
						<image width='410' height='597' xlink:href='images/Cover.jpg' />
					</svg>";
				$book->addChapter("Cover Page", "Cover.html", $this->contentStart . "$coverSrc</body>\n</html>\n", false, EPub::EXTERNAL_REF_ADD, $this->fileDir);
				$book->setCoverImage("Cover.jpg", file_get_contents($args['cover']));
			} else {
				$book->addChapter("Cover Page", "Cover.html", $this->contentStart . "<h1>" . $book->getTitle() . "</h1><h3>" . $book->getAuthor() . "</h3></body>\n</html>\n", false, EPub::EXTERNAL_REF_ADD, $this->fileDir);
			}
			
			$copyrightStatement = $CBPPlatformDao->getJournalCopyrightStatement($journalId);
			if (!empty($copyrightStatement)) {
				$copyrightStatement = reset($copyrightStatement);
				$copyrightStatement = "<div style='width: 75%; text-align: center; margin: 0 auto;'><p>" . $copyrightStatement . "</p></div>";
				$book->addChapter("Copyright Notice", "notice.html", $this->contentStart . $copyrightStatement, false, EPub::EXTERNAL_REF_ADD, $this->fileDir);
			}

			if (!empty($chapters)) {
				$chapterCount = 0;
				foreach ($chapters as $chapter) {
					if (!isset($chapter['type']) && $chapter['type'] != "supp") {
						$chapterCount++;
					} else {
						$suppChapters = true;
					}
				}
				for ($i = 0; $i < count($chapters); $i++) {
					$document = new TransformDoc();
					$document->setStrFile($chapters[$i]['src'], $chapters[$i]['dir']);
					$document->generateXHTML(); //problem, here
					$document->validatorXHTML();
					$contentPreg = $this->stripTags($document->getStrXHTML());
					if ($chapterCount == 1) {
						$splitter = new EPubChapterSplitter();
						$xhtml = $this->contentStart . $contentPreg . "</body></html>";
						$html2 = $splitter->splitChapter($xhtml, true, '@<h1[^>]*?.*?</h1>@siu');/* '#^<.+?>Chapter \d*#i'); */ 
						foreach($html2 as $key=>$value) {
							$cTitles[] = $key;
							$cContent[] = $value;
						}
						for($i=0;$i<count($cContent);$i++) {
							$html3[$cTitles[$i]] = $cContent[$i];
						}
						$idx = 0;
						if (!empty($html3)) {
							foreach ($html3 as $k=>$v) {
								$idx++;
								$cName = preg_replace('#<br.+?>#i', " - ", $k);
								// Remove any other tags
								$cName = preg_replace('/<[^>]*>/', '', $cName);
								// clean the chapter name by removing any double spaces left behind to single space. 
								$cName = preg_replace('#\s+#i', " ", $cName);
								$book->addChapter($cName, "Chapter" . $idx . ".html", $v, false, EPub::EXTERNAL_REF_ADD, $fileDir);
							}
						}
						if ($suppChapters == true) {
							$i = 0;
							foreach ($chapters as $chapter) { 
								if (isset($chapter['type']) && $chapter['type'] == "supp") {
									$document = new TransformDoc();
									$document->setStrFile($chapter['src'], $chapter['dir']);
									$document->generateXHTML();
									$document->validatorXHTML();
									$contentPreg = $this->stripTags($document->getStrXHTML());	
									$book->addChapter($chapter['name'], "Chapter$i.html", $this->contentStart . $contentPreg . "</body></html>", false, EPub::EXTERNAL_REF_ADD, $this->fileDir);
								}
							}
							$i++;
						}
						break;		
					} else {
						if ($imprintType == "atomistic") { 
							$book->addChapter($cName, "Chapter" . $i . ".html", $this->contentStart . $contentPreg . "</body></html>", false, EPub::EXTERNAL_REF_ADD, $fileDir);
						} elseif ($imprintType == "collection") {
							if ($chapters[$i]['description'] != "") {
								$book->addChapter("Introduction to " . $chapters[$i]['name'] . " by " . $chapters[$i]['author'], "Chapter" . "$i-I" . ".html", $this->contentStart . "<div class='submissionIntroEpub'><h1>" . $chapters[$i]['author'] . "</h1>" . $this->stripTags($chapters[$i]['description'], true) . "</div></body></html>", false, EPub::EXTERNAL_REF_ADD, $fileDir, false);	
							}
							$book->addChapter($chapters[$i]['name'] . " by " . $chapters[$i]['author'], "Chapter" . $i . ".html", $this->contentStart . "$contentPreg</body></html>", false, EPub::EXTERNAL_REF_ADD, $fileDir);	
						}
					}
				}
			} else {
				$content = new TransformDoc();
				$splitter = new EPubChapterSplitter();
				$content->setStrFile($src, $dir);
				$content->generateXHTML();
				$content->validatorXHTML();
				$contentPreg = $this->stripTags($content->getStrXHTML());
				$xhtml = $this->contentStart . $contentPreg . "</body></html>";
				$html2 = $splitter->splitChapter($xhtml, true, '@<h1[^>]*?.*?</h1>@siu');
				foreach($html2 as $key=>$value) {
					$cTitles[] = $key;
					$cContent[] = $value;
				}
				array_shift($cContent);
				for($i=0;$i<count($cContent);$i++) {
					$html3[$cTitles[$i]] = $cContent[$i];
				}
				$idx = 0;
				if (!empty($html3)) {
					foreach ($html3 as $k=>$v) {
						$idx++;
						$cName = preg_replace('#<br.+?>#i', " - ", $k);
						// Remove any other tags
						$cName = preg_replace('/<[^>]*>/', '', $cName);
						//clean the chapter name by removing any double spaces left behind to single space. 
						$cName = preg_replace('#\s+#i', " ", $cName);
						$book->addChapter($cName, "Chapter" . $idx . ".html", $v, false, EPub::EXTERNAL_REF_ADD, $fileDir);
					}
				} else {
					$book->addChapter("Body Text", "Chapter1.html", $xhtml, false, EPub::EXTERNAL_REF_ADD, $fileDir);
				}
			}
			$book->finalize(); 
			$zipData = $book->getBook();
			file_put_contents($path, $zipData);
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
				'/(?si)<span\s+class\s*=\s*"-H"\s* style=" letter-spacing:0pt;">(.*?)<\/span>/',
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