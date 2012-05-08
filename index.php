<html>
<head></head>
<link rel="stylesheet" href="bootstrap.css" />
<body>
	<div class="container">
	<?php
		//a simple page router to include the 'default' view if the index.php file is requested
		if ($_SERVER['PHP_SELF'] == "/digitalFormatConvert/index.php") {
			include 'application/views/default.php';
		}
	?>
	</div>
</body>
</html>