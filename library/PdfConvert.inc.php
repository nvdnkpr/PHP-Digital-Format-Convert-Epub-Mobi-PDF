<?php

/**
 * @defgroup CBPPlatform
 */

/**
 * @file classes/CBPPlatform/conv/PdfConvert.inc.php
 *
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CBPPlatformPdfConvert
 * 
 * @ingroup CBPPlatform
 *
 * @brief Class defining operations PDF conversion from Microsoft Word Files
 *
 */

	class CBPPlatformPdfConvert {
		
		/**
		 * Constructor.
		 * Imprort the required class files and set properties
		 */
		function __construct() {
			import('classes.CBPPlatform.conv.phpDocx.classes.TransformDoc');
			import('classes.CBPPlatform.conv.mpdf.mpdf');
			import('classes.CBPPlatform.conv.cssparse.CSSParser');
			
			$this->fileDir = ".";
			$this->stylesheetDir = "styles/ebook/";
			$this->contentStart = "<html>\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /><body>";
			$this->paperSizes = array(
				'a5' => array ('w' => '148', 'h' => '210'),
				'a4' => array ('w' => '210', 'h' => '297'),
				'trade' => array ('w' => '129', 'h' => '198')
			);
		}
		
		/**
		 * Create a PDF and export to defined path
		 * @param $dir str directory of the source file to convert
		 * @param $src str filename of the source file to convert
		 * @param $path str path to export the resultant PDF to
		 * @param $chapters array chapters to convert into a single PDF
		 * @param $journalId int Id of the journal(imprint)
		 * @param $args array arguments for the conversion (e.g. Description, cover image, etc)
		 * @param $coverPath str path to export the front cover artwork to
		 */
		function createPdf($dir = null, $src, $path, $chapters = array(), $journalId, $args = array(), $coverPath) {
			$mpdf=new mPDF('utf-8');
			$mpdf->useOddEven = 1;
			
			$htmlEncode = array('title', 'author');
			foreach($htmlEncode as $encode) {
				$args[$encode] = htmlentities($args[$encode], ENT_QUOTES, "UTF-8");
			}
			
			isset($args['title']) ? $mpdf->SetTitle($args['title']) : $mpdf->SetTitle("No Title");
			isset($args['description']) ? $mpdf->SetSubject($args['description']) : $mpdf->SetSubject("No description");
			isset($args['author']) ? $mpdf->SetCreator($args['author']) : $mpdf->SetCreator("No author");	

			$CBPPlatformDao =& DAORegistry::getDAO('CBPPlatformDAO');
			$imprintType = $CBPPlatformDao->getImprintType($journalId);
			$stylesheet = $CBPPlatformDao->getImprintStylesheet($journalId);
			$stylesheetContents = file_get_contents($this->stylesheetDir . "$stylesheet.css");
			$mpdf->WriteHTML($stylesheetContents, 1);
			
			$mpdf->WriteHTML($this->contentStart . '
			<htmlpagefooter name="myFooter1" style="display:none">
			<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; 
			    color: #000000; font-weight: bold; font-style: italic;"><tr>
			    <td width="33%" style="text-align: right; ">{PAGENO}</td>
			    </tr></table>
			</htmlpagefooter>
			<htmlpagefooter name="myFooter2" style="display:none">
			<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; 
			    color: #000000; font-weight: bold; font-style: italic;"><tr>
			    <td width="33%"><span style="font-weight: bold; font-style: italic;">{PAGENO}</span></td>
			    </tr></table>
			</htmlpagefooter>');
			
			$imagesize = getimagesize($args['cover']);
			if (substr($imagesize[1] / $imagesize[0], 0, ((strpos($imagesize[1] / $imagesize[0], '.')+1)+2)) == 1.41 || substr($imagesize[1] / $imagesize[0], 0, ((strpos($imagesize[1] / $imagesize[0], '.')+1)+2)) == 1.53) {
				$pdfContent .= '<div style="position: absolute; left:0; right: 0; top: 0; bottom: 0;"><img src="' . $args['cover'] . '" id="cover" /></div>';
			} else {
				$pdfContent .= "<div style='margin: 0 auto; width: 80%; text-align: center;'>";
				if (isset($args['title'])) $pdfContent .= "<h1>" . $args['title'] . "</h1>";
				if (isset($args['cover'])) { $pdfContent .= "<img src=\"" . $args['cover'] . "\" >"; } else { $pdfContent .= "<br/>"; }
				if (isset($args['author'])) $pdfContent .= "<h2>" . $args['author'] . "</h2>";
				$pdfContent .= "</div>";
			}
			$mpdf->WriteHTML($pdfContent);
			$mpdf->AddPage('','','','','Off');
			
			$copyrightStatement = $CBPPlatformDao->getJournalCopyrightStatement($journalId);
			if (!empty($copyrightStatement)) {
				$copyrightStatement = reset($copyrightStatement);
				$mpdf->AddPage('','','','','Off');
				$innerPageConent = "<div style='width: 90%; text-align: center; margin: 0 auto;'><p>" . $copyrightStatement . "</p></div>";
				$mpdf->WriteHTML($innerPageConent);
			}
			
			if (!empty($chapters)) {
				$mpdf->TOCpagebreakByArray(array(
				    'TOCusePaging' => true,
				    'TOCuseLinking' => true,
				    'toc_preHTML' => '<h1>Table of Contents</h1>',
					'toc_postHTML' => '',
					'resetpagenum' => 1,
					'suppress' => 'true',
			    ));
				$chapterCount = 0;
				$authorBiographies = 0;
				foreach ($chapters as $chapter) {
					if (!isset($chapter['type']) && $chapter['type'] != "supp") {
						$chapterCount++;
					} else {
						if ($chapter['desc'] == "Author Biography")  $authorBiographies++;
						$suppChapters = true;
					}
				}
				for ($i = 0; $i < count($chapters); $i++) {
					$htmlEncode = array('name', 'author');
					foreach($htmlEncode as $encode) {
						$chapters[$i][$encode] = htmlentities($chapters[$i][$encode], ENT_QUOTES, "UTF-8");
					}
					$document = new TransformDoc();
					$document->setStrFile($chapters[$i]['src'], $chapters[$i]['dir']);
					$document->generateXHTML(); //problem, here
					$document->validatorXHTML();
					if ($chapterCount == 1) {
						$contentPreg = $this->stripTagsAddChapters($document->getStrXHTML());
						$contentPreg = ltrim($contentPreg);
						if (substr($contentPreg, 0, 13) == "<pagebreak />") $contentPreg = substr_replace($contentPreg, '', 0, 13);
						$mpdf->addPage('', '', '', '', 'On');
						$mpdf->PageNumSubstitutions[] = array('from'=>$mpdf->page+1, 'reset'=> 1, 'type'=>'1', 'suppress'=>'off');
						$mpdf->WriteHTML("<div class='content'>", 2);
						$mpdf->WriteHTML($contentPreg, 2);
						if ($suppChapters == true) {
							foreach ($chapters as $chapter) { 
								if (isset($chapter['type']) && $chapter['type'] == "supp") {
									$document = new TransformDoc();
									$document->setStrFile($chapter['src'], $chapter['dir']);
									$document->generateXHTML();
									$document->validatorXHTML();
									if ($authorBiographies > 1) {
										$contentPreg = $this->stripTags($document->getStrXHTML());	
										$mpdf->TOC_Entry($chapter['name']);
										$mpdf->WriteHTML("<pagebreak />" . $contentPreg, 2);
									} else {
										$addAuthorBiographyToBack = true;
										$authorBiography = $this->stripTags($document->getStrXHTML());	
									}
								}
							}
						}
						break;
					} else {
						$contentPreg = $this->stripTags($document->getStrXHTML());
						$contentPreg = ltrim($contentPreg);
						if (substr($contentPreg, 0, 13) == "<pagebreak />") $contentPreg = substr_replace($contentPreg, '', 0, 13);
						if ($i != 0) { 
							$prepend = "<pagebreak />"; 
						} else { 
							$mpdf->addPage('', 'E', '', '', 'On');
							$mpdf->PageNumSubstitutions[] = array('from'=>$mpdf->page+1, 'reset'=> 1, 'type'=>'1', 'suppress'=>'off');
							$mpdf->WriteHTML("<div class='content'>", 2);
						}
						if ($imprintType == "atomistic") { 
							$mpdf->WriteHTML($prepend . "<tocentry content='" . $chapters[$i]['name'] . "' level='0' />" . $contentPreg, 2);
						} elseif ($imprintType == "collection") {
							if ($chapters[$i]['description'] != "") { 
								$introduction = "<div class='submissionIntro'><h1>" . $chapters[$i]['author'] . "</h1>" . $this->stripTags($chapters[$i]['description'], true) . "</div><pagebreak /><tocentry content='" . $chapters[$i]['name'] . " by " . $chapters[$i]['author'] . "' level='0' />";
							}
							$mpdf->WriteHTML($prepend . $introduction . $contentPreg, 2);
						}
					}
				}
				$mpdf->writeHTML("</div>");
				if (isset($args['description'])) {
					$mpdf->WriteHTML("<pagebreak page-selector='none' odd-footer-value = '' even-footer-value= '' /><pagebreak /><div style='width: 90%; text-align: center; margin: 0 auto;'><p>" . $this->stripTags($args['description'], true) . "</p></div>", 2);
					if ($addAuthorBiographyToBack == true) { $backCoverContent .= "<div style='width: 90%; text-align: center; margin: 0 auto; margin-top: 10px;'><p>" . $authorBiography . "</p></div>"; }
					$backCoverContent .= "<p style='width: 90%; text-align: center; margin: 0 auto;'><strong>Published " . date("F Y") . ", Scarborough, UK</strong></p>";
					$mpdf->WriteHTML($backCoverContent, 2);
				}
				$mpdf->WriteHTML("</body></html>");
				$pdfData = $mpdf->Output('', 'S');
				$pageCount = $mpdf->page;
				file_put_contents($path, $pdfData);
				if (file_exists(($this->stylesheetDir . "$stylesheet-FC.css"))) {
					$this->createCoverPdf($stylesheet, $pageCount, $args['cover'], $this->stripTags($args['description'], true), $addAuthorBiographyToBack, $authorBiography, $args['title'], $args['imprint'], $coverPath);
				}
				return true;
				
			} else {
				$document = new TransformDoc();
				$document->setStrFile($src, $dir);
				$document->generateXHTML();
				$document->validatorXHTML();
				$contentPreg = $this->stripTagsAddChapters($document->getStrXHTML());
				$contentPreg = ltrim($contentPreg);
				if (substr($contentPreg, 0, 13) == "<pagebreak />") $contentPreg = substr_replace($contentPreg, '', 0, 13);
				$mpdf->addPage('', 'E', '', '', 'On');
				$mpdf->PageNumSubstitutions[] = array('from'=>$mpdf->page+1, 'reset'=> 1, 'type'=>'1', 'suppress'=>'off');
				$mpdf->WriteHTML("<div class='content'>", 2);
				$mpdf->WriteHTML($contentPreg,2);
				$mpdf->WriteHTML("</div></body></html>");
				$pdfData = $mpdf->Output('', 'S');
				
				file_put_contents($path, $pdfData);
				return true;
			}
		}
		
		/**
		 * Create a cover artwork PDF and export to defined path
		 * @param $stylesheet str filename of the stylesheet for the conversion
		 * @param $pagecount int count of pages in the 'inners' PDF
		 * @param $description str description/'blurb' for the back page
		 * @param $addAuthorBiographyToBack bool true/false to add author biography to back cover
		 * @param $authorBiography str author biography
		 * @param $title str title of the issue/book
		 * @param $coverPath str path to export the front cover artwork to
		 */
		function createCoverPdf($stylesheet, $pageCount, $cover, $description, $addAuthorBiographyToBack = false, $authorBiography = "", $title, $imprint, $coverPath) { 
			$sheetThickness = Config::getVar('printod', 'sheet_thickness');
			$spineWidth = ($sheetThickness / 10) * ($pageCount / 2);
			foreach ($this->paperSizes as $paperSize => $value) {
				if(strpos($stylesheet, $paperSize)) {
					$paperWidth = $value['w'];
					$paperHeight = $value['h'];
				}
			}
			$bleed = Config::getVar('printod', 'bleed');
			$totalSizeWidth = ($paperWidth * 2) + $spineWidth;
			$totalSizeHeight = $paperHeight;
			$sheetSizeWidth = $totalSizeWidth + $bleed;
			$sheetSizeHeight = $totalSizeHeight + $bleed;
			
			$css = file_get_contents($this->stylesheetDir . "$stylesheet-FC.css");
			$css .= "
				@page {
					sheet-size: {$sheetSizeWidth}mm {$sheetSizeHeight}mm;
					size: {$totalSizeWidth}mm {$totalSizeHeight}mm;	
				}
				#frontCover img {
					width: {$paperWidth}mm; 
					height: {$paperHeight}mm;
				}
				#backCover {
					width: {$paperWidth}mm; 
					height: {$paperHeight}mm;
				}
				#spine {
					width: {$spineWidth}mm;
					height: {$sheetSizeHeight}mm;
					left: {$paperWidth}mm;
				}";
			
			$mpdf=new mPDF('utf-8');
			$mpdf->WriteHTML($css, 1);
			$mpdf->WriteHTML('<div id="frontCover"><img src="' . $cover . '" /></div>', 2);
			$mpdf->WriteHTML('<div id="spine"><table><tr><td id="title"><p>' . $title . '</p></td></tr><tr><td id="imprint">' . $imprint . '</td></tr></table></div>', 2);
			$backCoverContent = "<div style='width: 80%; text-align: center; margin: 0 auto; margin-top: 30mm'><p>" . $description . "</p></div>";
			if ($addAuthorBiographyToBack == true) { $backCoverContent .= "<div style='width: 80%; text-align: center; margin: 0 auto; margin-top: 10px;'><p>" . $authorBiography . "</p></div>"; }
			$backCoverContent .= "<p style='width: 80%; text-align: center; margin: 0 auto;'><strong>Published " . date("F Y") . ", Scarborough, UK</strong></p>";
			$mpdf->WriteHTML("<div id='backCover'>$backCoverContent</div>", 2);
			$pdfData = $mpdf->Output('', 'S');
			file_put_contents($coverPath, $pdfData);
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
				if(strstr($element->getAttribute('class'), 'Heading1') !== FALSE) {
			      $newElement = $dom->createElement('h1', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	} 
			  	if(strstr($element->getAttribute('class'), 'Heading2') !== FALSE) {
			      $newElement = $dom->createElement('h2', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	} 
			  	if(strstr($element->getAttribute('class'), 'Heading3') !== FALSE) {
			      $newElement = $dom->createElement('h3', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	} 
			  	if(strstr($element->getAttribute('class'), 'Heading4') !== FALSE) {
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
			$src = str_replace("<h1>", "<pagebreak /><h1>", $src);
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
		
		/**
		 * Strip HTML tags, inline styles and convert input src to semantic, styled HTML and add page breaks and chapter entries to table of contents
		 * @param $src str input HTML to be processed
		 * @param $all bool true/false to strip ALL HTML tags
		 * @return $str str stripped HTML
		 */
		function stripTagsAddChapters($src, $all = false) {
			$dom = new DOMDocument;
			@$dom->loadHTML('<?xml encoding="UTF-8">' . $src);
			$xPath = new DOMXPath($dom);
			$elements = $xPath->query('//p');
			foreach($elements as $element){
				if(strstr($element->getAttribute('class'), 'Heading1') !== FALSE){
			      $newElement = $dom->createElement('h1', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	}
			  	if (strstr($element->getAttribute('class'), 'Heading2') !== FALSE) {
			      $newElement = $dom->createElement('h2', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	} 
			  	if(strstr($element->getAttribute('class'), 'Heading3') !== FALSE) {
			      $newElement = $dom->createElement('h3', $element->nodeValue);
			      $element->parentNode->replaceChild($newElement, $element);
			  	} 
			  	if(strstr($element->getAttribute('class'), 'Heading4') !== FALSE) {
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
			$src = str_replace("#", "<br />", $src);
			$src = str_replace("***", "<br /><br />", $src);
			$src = preg_replace
				(
				array(
				'/(?si)<h1(.*?)\s*>(.*?)<\/h1>/',
				'/(?si)<h2(.*?)\s*>(.*?)<\/h2>/',
				'/(?si)<h3(.*?)\s*>(.*?)<\/h3>/',
				'/(?si)<h4(.*?)\s*>(.*?)<\/h4>/'
				),
				array(
				"<pagebreak /><h1>$2<tocentry content='$2' level='0' /></h1>", "<h2>$2<tocentry content='$2' level='1' /></h2>", '<h3>$2</h3>', '<h4>$2</h4>',
				), $src
			);
			return $src;
		}
	}