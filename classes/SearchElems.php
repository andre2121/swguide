<?php
class SearchElems{

	private $model_array;
	private $searchWords;
	private $type;

	public function __construct($params, $type, $searchWords){
		$this->searchWords = $searchWords;
		$this->type = $type;
		$tmparray = $this->getFromApi();
		if ($tmparray) {
			$this->model_array = $tmparray;
		}else{
			$this->model_array = array();
		}
	}

	private function getFromApi(){
		$apiarray = Api::getArray("search",$this->type,$this->searchWords);
		$apiname = $this->type == 'films' ? "title" : "name";
		if ($apiarray) {
			$modarray = array();
			foreach ($apiarray['results'] as $key => $result) {
				$modarray[$key] = [
					'id' => $this->UrltoId($result['url']),
					'name' => $result[$apiname],
					];
			}
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){
		$apiname = $this->type == 'films' ? "title" : "name";
		$outarray = 
			[
				'name' => "Results search", // Planets
				'search' => "Enter ".$apiname,
				'swtext' => "You searched for <strong>".$this->searchWords."</strong> in ".ucfirst($this->type)."...",
			];
		if ($this->model_array) { // Datas prepared
			foreach ($this->model_array as $key => $value) {
				$par = $key+1;
				$outarray['swdate'][$key] = 
							[
								'title' => $par,
								'listvalue' => [$value['id']=>$value['name']],
								'comment'=>"",
								'type'=>$this->type
							];
			}
		}else{ // Don't prepared the array
			$outarray['swdate'] = [];
		}
		return $outarray;
	}

	private function UrltoId($url){
		// Exampl the url: "http://swapi.co/api/planets/12/"
		if (strlen(trim($url))==0) {
			return "";
		}else{
			$urlarray = explode("/", $url);
			return $urlarray[5];
		}
	}
}

?>