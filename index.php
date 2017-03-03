<?php
	include 'autoload.php';

	$htmlPage = new Html(__ROOT__, $params, "Home");
?>
	<p>Home Information...</p>

<?php
	$htmlPage->getFooter();
?>
