<div class="span12">
	<div class="page-header">
    	<h1>Digital Format Conversion Tools <br /><small>Convert from DOCX or HTML to EPUB, Kindle, and PDF in PHP</small></h1>
  	</div>
  	<h2>Introduction</h2>
  	<p>Digital Format Conversion tools enable conversion from Microsoft Word (2007+) DOCX format or HTML to EPUB, Kindle and PDF in PHP.</p>
	<p>The EPub Controller/Model examples are the most complete in this application, including basic content cleansing and setting of options specific to the EPub conversion library.</p>
	<p>The tools are constructed from a range of Open Source elements, including:</p>
		<ul>
			<li>EPub PHP class (http://www.phpclasses.org/package/6115)</li>
			<li>phpMobi (https://github.com/raiju/phpMobi)</li>
			<li>mPDF (http://www.mpdf1.com/mpdf/)</li>
			<li>PHPDOCX (http://www.phpdocx.com/)</li>
			<li>Twitter Bootstrap (http://twitter.github.com/bootstrap/)</li>
		</ul>
	<p>A copy of the source code for these elements are available in /library</p>
	<h2>Background</h2>
	<p>I have developed this package to demonstrate, in a simple way, the conversion tools seen in https://github.com/campus-based-publishing/CBPPlatform</p> 
	<h2>Application Structure</h2>
	<p>This simple application uses a basic MVC architecture. Models/Views/Controllers are found in the /application directory.</p>
	<p>There are 4 models and 3 controllers. 3 of the models and the controllers contain the code to convert from DOCX to EPub, MOBI, and PDF.</p>
	<p>The final model (TransformModel) contains common methods to extract HTML from a Word DOCX Document.</p>
	<h2>How It Works</h2>
	<p>The conversion process works thus:</p>
	<ul>
		<li>If a Word Document is specified as input: PHPDOCX is used to unzip and extract XML from the DOCX file</li>
		<li>If a Word Document is specified as input: PHPDOCX applies XSLT to the XML, which ultimately produces valid HTML</li>
		<li>If HTML is specified as input: The HTML is passed 'as-is' to the following stage</li>
		<li>At this stage, a DOMParser, third-party CSS parsers, or custom CSS, can be utilised to customise the HTML. I have opted to leave these features out to keep the code as generic as possible.</li>
		<li>The final HTML and other arguments (author, publisher etc) are passed to the range of conversion tools dependent on the scenario: EPub, phpMobi, and mPDF</li>
	</ul>
	<h2>Demo</h2>
	<p><em>N.B. The demonstration manuscript is stored in /application/example</em></p>
	<div class="btn-group">
		<button class="btn"><a href="?function=epub">Convert example DOCX to EPUB</a></button>
		<button class="btn"><a href="?function=mobi">Convert example DOCX to MOBI</a></button>
		<button class="btn"><a href="?function=pdf">Convert example DOCX to PDF</a></button>
		<button class="btn"><a href="application/example/manuscript.docx">View the sample DOCX file</a></button>
	</div>
	<br />
	<div class="btn-group">
		<button class="btn"><a href="?function=epub&str=1">Convert example HTML to EPUB</a></button>
		<button class="btn"><a href="?function=mobi&str=1">Convert example HTML to MOBI</a></button>
		<button class="btn"><a href="?function=pdf&str=1">Convert example HTML to PDF</a></button>
	</div>
	<br />
	<h2>TODO</h2>
	<ul>
		<li>Update documentation to reflect 9 May 2012 changes onwards</li>
		<li>Detailed testing of HTML to *.* formats - PDF tested (basic elements only) 15 May 2012</li>
	</ul>
</div>
