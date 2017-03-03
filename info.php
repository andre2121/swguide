<?php
	include 'autoload.php';

	if (isset($_GET['info'])) {
		list($tpage, $type, $id) = explode("-", $_GET['info']);
	}else{
		if (isset($_POST['search']) && $_POST['search']!="") {
			$tpage = "search";
			$type = $_POST['type'] != "" ? $_POST['type'] : "people";
			$searchWords = trim(htmlspecialchars($_POST['search']));
		}else{
			$tpage = "list";
			$type = "people";
			$id = "1";
		}
	}
	switch($tpage){
		case 'list':
			$Element = new ListElems($params, $id, $type);
			break;
		case 'search':
			$Element = new SearchElems($params, $type, $searchWords);
		 	break;
		case 'info':
			$class = new ReflectionClass(ucfirst($type));
			$Element = $class->newInstance($params, $id, $type);
			break;
	}
	$htmlPage = new Html(__ROOT__, $params, ucfirst($type), $tpage);
	$modarray = isset($Element) ? $Element->getArray() : array();
	if (count($modarray) != 0) {
		$htmlPage->builderTable($modarray);
	}else{
		echo "Sorry, this site is overloaded. Come back later. :(";
	}
	$htmlPage->getFooter();
?>
