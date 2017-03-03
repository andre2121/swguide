<?php
	include 'autoload.php';

	$htmlPage = new Html(__ROOT__, $params, "About");
?>
	<h2>About</h2>
	<p>A little technical information about the project.</p>
<?php
	$htmlPage->getFooter();

?>
