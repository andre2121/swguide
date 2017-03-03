<?php
# class Api  <===============================================
abstract class Api{

	public static function getArray($tinfo,$elems,$id){
		$linkapi = "http://swapi.co/api/".$elems."/";
		switch($tinfo){
			case 'list':   // Exampl the linkapi: "http://swapi.co/api/planets/?page=3&format=json"
				$linkapi .= "?page=".$id."&";
				break;
			case 'search': // Exampl: "http://swapi.co/api/people/?search=Han&format=json"
				$linkapi .= "?search=".$id."&";
			 	break;
			case 'info':   // Exampl the linkapi: "http.://swapi.co/api/planets/1/?format=json";
				$linkapi .= (string)$id."/?";
				break;
		}
		$linkapi .= "format=json";
		$content=file_get_contents($linkapi);
		if ($content) {
			$apiarray = json_decode($content, true);
			if (is_null($apiarray)) $apiarray = false;
		}else{
			$apiarray = false;
		}
		return $apiarray;
	}
}
?>