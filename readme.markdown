# Digital Format Conversion Tools 
## Convert from DOCX to EPUB, Kindle, PDF in PHP

### Introduction
Digital Format Conversion tools enable conversion from Microsoft Word (2007+) DOCX format to EPUB, Kindle and PDF.
The EPub Controller/Model examples are the most complete in this application, including basic content cleansing and setting of options specific to the EPub conversion library.
The tools are constructed from a range of Open Source elements, including:
* PHP CSS Parser (https://github.com/sabberworm/PHP-CSS-Parser)
* EPub PHP class (http://www.phpclasses.org/package/6115)
* phpMobi (https://github.com/raiju/phpMobi)
* mPDF (http://www.mpdf1.com/mpdf/)
* PHPDOCX (http://www.phpdocx.com/)
* Twitter Bootstrap (http://twitter.github.com/bootstrap/)

A copy of the source code for these elements are available in /library

### Background
I have developed this package to demonstrate, in a simple way, the conversion tools seen in https://github.com/campus-based-publishing/CBPPlatform

### Application Structure
This simple application uses a basic MVC architecture. Models/Views/Controllers are found in the /application directory.
There are 4 models and 3 controllers. 3 of the models and the controllers contain the code to convert from DOCX to EPub, MOBI, and PDF.
The final model (TransformModel) contains common methods to extract HTML from a Word DOCX Document.

### How It Works
The conversion process works thus:
* PHPDOCX is used to unzip and extract XML from the DOCX file
* PHPDOCX applies XSLT to the XML, which ultimately produces valid HTML
* At this stage, a DOMParser, third-party CSS parsers, or custom CSS, can be utilised to customise the HTML. I have opted to leave these features out to keep the code as generic as possible.
* The final HTML and other arguments (author, publisher etc) are passed to the range of conversion tools dependent on the scenario: EPub, phpMobi, and mPDF

### Documentation
Documentation is available in /docs

### Demo
N.B. The demonstration manuscript is stored in /application/example