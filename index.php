<?php
	if (isset($_GET['function'])) {
		require_once 'application/controllers/EpubController.php';
		require_once 'application/controllers/MobiController.php';
		require_once 'application/controllers/PdfController.php';
		
		$function = ucfirst($_GET['function']);
		$controllerName = "{$function}Controller";
		$actionName = "create{$function}Action";
		
		//use variable variables based on $_GET variable to instantiate class and call method
		$controller = new $controllerName();
		$controller->$actionName();
		exit();
	}
?>
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