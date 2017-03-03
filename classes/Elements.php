<?php
# class SwMain  <===============================================
abstract class SwMain{
	protected $db_host;
	protected $db_user;
	protected $db_pass;
	protected $db_name;
	protected $model_array;
	protected $model_array2;
	protected $compare;
	protected $dbcur;

	public function __construct($params,$id,$type){
		$this->compare = false;
		$this->setParams($params);
		$this->dbcur = new DB($this->db_host, $this->db_user, $this->db_pass, $this->db_name);

		if ($this->dbcur->connected) { // Connect to DB?
			$this->model_array = $this->getFromDB($id,$type);
			if (count($this->model_array) != 0) { //exist dates in DB?
				if($this->compare){ // compare ?
					$this->model_array2 = $this->getFromApi($id,$type);
					if ($this->model_array2) { // upload array from API
						if ($this->model_array != $this->model_array2) {
							$this->model_array = $this->model_array2;
							$this->updateToDB($this->model_array,$type);
						}
					}
				}
			}else{ // don't exist datas in DB
				$tmparray = $this->getFromApi($id,$type);
				if ($tmparray) {
					$this->model_array = $tmparray;
					$this->setToDB($this->model_array,$type);
				}else{
					$this->model_array = array();
				}
			}
		}else{ // don't connect to DB
			$tmparray = $this->getFromApi($id,$type);
			if ($tmparray) {
				$this->model_array = $tmparray;
			}else{
				$this->model_array = array();
			}
		}
	}

	abstract protected function getFromApi($id,$type);
	abstract protected function getFromDB($id,$type);
	abstract protected function setToDB($model_array,$type);
	abstract protected function updateToDB($model_array,$type);
	abstract public function getArray();

	protected function setParams($params){
		$this->db_host = $params['database']['host'];
		$this->db_user = $params['database']['user'];
		$this->db_pass = $params['database']['password'];
		$this->db_name = $params['database']['db_name'];
		$this->compare = (rand(0,1) < $params['control']); //boolean
	}

	protected function UrltoId($url){
		// Exampl the url: "http://swapi.co/api/planets/12/"
		if (strlen(trim($url))==0) {
			return "";
		}else{
			$urlarray = explode("/", $url);
			return $urlarray[5];
		}
	}
}

# class ListElems  <===============================================
class ListElems extends SwMain{

	protected function getFromApi($pg,$type){
		$apiname = $type == 'films' ? "title" : "name";
		$apiarray = Api::getArray("list",$type,$pg);
		if ($apiarray) {
			$pgall = ceil($apiarray['count']/10);
			$modarray = array();
			foreach ($apiarray['results'] as $key => $result) {
				$modarray[$key] = [
					'type' => $type,
					'page' => $pg,
					'pageall' => $pgall,
					'id' => $this->UrltoId($result['url']),
					'name' => $result[$apiname],
				];
			}
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	protected function getFromDB($pg,$type){
		$dbarray = $this->dbcur->all('listelems', ['type'=>$type,'page'=>$pg]);
		$modarray = array();
		if (count($dbarray)!=0) {
			foreach ($dbarray as $dbresult) {
				$modarray[] = [
					'type' => $type,
					'page' => $pg,
					'pageall' => $dbresult['pageall'],
					'id' => $dbresult['id'],
					'name' => $dbresult['name'],
				];
			}
		}
		return $modarray;
	}

	protected function setToDB($model_array,$type){
		$this->dbcur->addArray('listelems', $model_array);
	}

	protected function updateToDB($model_array,$type){
		$this->dbcur->deleteWhere('listelems', ['type'=>$type,'page'=>$model_array[0]['page']]);
		$this->setToDB($model_array,$type);
	}

	public function getArray(){
		if ($this->model_array) { // Datas prepared
			$type = $this->model_array[0]['type'];
			$apiname = $type == 'films' ? "title" : "name";
			$outarray = 
			[
				'name' => ucfirst($type), // Planets
				'search' => "Enter ".$apiname,
				'curpage' => $this->model_array[0]['page']."/".$this->model_array[0]['pageall']			
			];
			foreach ($this->model_array as $key => $value) {
				$par = ($value['page']-1)*10 + $key+1;
				$outarray['swdate'][$key] = 
							[
								'title' => $par,
								'listvalue' => [$value['id']=>$value['name']],
								'comment'=>"",
								'type'=>$type
							];
			}
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}

# class Elements  <===============================================
abstract class Elements extends SwMain{

	protected function getFromDB($id,$type){
		$dbarray = $this->dbcur->one($type, ['id'=>$id]);
		$modarray = count($dbarray)==0 ? array() : $dbarray;
		return $modarray;
	}

	protected function setToDB($model_array,$type){
		$this->dbcur->add($type, $model_array);
	}

	protected function updateToDB($model_array,$type){
		$this->dbcur->deleteWhere($type, ['id'=>$model_array['id']]);
		$this->setToDB($model_array,$type);
	}

	protected function ArrayUrlToStrId($arrayurl){
		// Exampl the arrayurl: ['01'=>"http://swapi.co/api/planets/7/", '02'=>"http://swapi.co/api/planets/12/"]
		$strid = "";
		if (is_array($arrayurl)) {
			foreach ($arrayurl as $key => $url) {
				$strid .= $key !=0 ? "," : "";
				$strid .= $this->UrltoId($url);
			}
		}
		return $strid;
	}

	protected function StrIdToArraiUrl($strid,$type){
		// Exampl the arrayurl: ['1'=>"Luke Skywalker", '14'=>"Han Solo"]
		$arrayurl = array();
		if (strlen(trim($strid))>0) { // exist id
			$arrayid = explode(",", $strid);
			foreach ($arrayid as $id) {
				$arrayurl[$id] = $this->dbcur->nameID($type,$id);
			}
		}
		return $arrayurl;
	}

	public function getName(){
		return $this->model_array['name'];
	}
}

# class People  <===============================================
class People extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['name'];
			$modarray['birth_year'] = $apiarray['birth_year'];
			$modarray['eye_color'] = $apiarray['eye_color'];
			$modarray['gender'] = $apiarray['gender'];
			$modarray['hair_color'] = $apiarray['hair_color'];
			$modarray['height'] = $apiarray['height'];
			$modarray['mass'] = $apiarray['mass'];
			$modarray['skin_color'] = $apiarray['skin_color'];
			$modarray['homeworld'] = $this->UrltoId($apiarray['homeworld']);
			$modarray['films'] = $this->ArrayUrlToStrId($apiarray['films']);
			$modarray['species'] = $this->ArrayUrlToStrId($apiarray['species']);
			$modarray['starships'] = $this->ArrayUrlToStrId($apiarray['starships']);
			$modarray['vehicles'] = $this->ArrayUrlToStrId($apiarray['vehicles']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter name",
			];
			$outarray['swdate'][] = ['title' => "Name", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Birth year", 'listvalue' => [$this->model_array['birth_year']], 'comment'=>"The birth year of the person, using the in-universe standard of BBY or ABY - Before the Battle of Yavin or After the Battle of Yavin. The Battle of Yavin is a battle that occurs at the end of Star Wars episode IV: A New Hope.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Gender", 'listvalue' => [$this->model_array['gender']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Height, cm", 'listvalue' => [$this->model_array['height']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Mass, kg", 'listvalue' => [$this->model_array['mass']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Eye color", 'listvalue' => [$this->model_array['eye_color']], 'comment'=>"Will be \"unknown\" if not known or \"n/a\" if the person does not have an eye.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Hair color", 'listvalue' => [$this->model_array['hair_color']], 'comment'=>"Will be \"unknown\" if not known or \"n/a\" if the person does not have hair.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Skin color", 'listvalue' => [$this->model_array['skin_color']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Homeworld", 'listvalue' => [$this->model_array['homeworld'] => $this->dbcur->nameID('planets', $this->model_array['homeworld'])], 'comment'=>"The planet that this person was born on or inhabits.", 'type'=>"planets"];
			$outarray['swdate'][] = ['title' => "Films", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['films'],'films'), 'comment'=>"", 'type'=>"films"];
			$outarray['swdate'][] = ['title' => "Specie", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['species'],'species'), 'comment'=>"", 'type'=>"species"];
			$outarray['swdate'][] = ['title' => "Starships", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['starships'],'starships'), 'comment'=>"Starships that this person has piloted", 'type'=>"starships"];
			$outarray['swdate'][] = ['title' => "Vehicles", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['vehicles'],'vehicles'), 'comment'=>"Vehicles that this person has piloted", 'type'=>"vehicles"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}

}

# class Films  <===============================================
class Films extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['title'];
			$modarray['episode_id'] = $apiarray['episode_id'];
			$modarray['opening_crawl'] = $apiarray['opening_crawl'];
			$modarray['director'] = $apiarray['director'];
			$modarray['producer'] = $apiarray['producer'];
			$modarray['release_date'] = $apiarray['release_date'];
			$modarray['species'] = $this->ArrayUrlToStrId($apiarray['species']);
			$modarray['starships'] = $this->ArrayUrlToStrId($apiarray['starships']);
			$modarray['vehicles'] = $this->ArrayUrlToStrId($apiarray['vehicles']);
			$modarray['characters'] = $this->ArrayUrlToStrId($apiarray['characters']);
			$modarray['planets'] = $this->ArrayUrlToStrId($apiarray['planets']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter title",
			];
			$outarray['swdate'][] = ['title' => "Title", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Episode number", 'listvalue' => [$this->model_array['episode_id']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Beginning of this film", 'listvalue' => [$this->model_array['opening_crawl']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Director", 'listvalue' => [$this->model_array['director']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Producer", 'listvalue' => [$this->model_array['producer']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Film release", 'listvalue' => [$this->model_array['release_date']], 'comment'=>"Date of film release at original creator country", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Specie", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['species'],'species'), 'comment'=>"", 'type'=>"species"];
			$outarray['swdate'][] = ['title' => "Characters", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['characters'],'people'), 'comment'=>"", 'type'=>"people"];
			$outarray['swdate'][] = ['title' => "Starships", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['starships'],'starships'), 'comment'=>"", 'type'=>"starships"];
			$outarray['swdate'][] = ['title' => "Vehicles", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['vehicles'],'vehicles'), 'comment'=>"", 'type'=>"vehicles"];
			$outarray['swdate'][] = ['title' => "Planets", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['planets'],'planets'), 'comment'=>"", 'type'=>"planets"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}

# class Planets  <===============================================
class Planets extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['name'];
			$modarray['diameter'] = $apiarray['diameter'];
			$modarray['rotation_period'] = $apiarray['rotation_period'];
			$modarray['orbital_period'] = $apiarray['orbital_period'];
			$modarray['gravity'] = $apiarray['gravity'];
			$modarray['population'] = $apiarray['population'];
			$modarray['climate'] = $this->ArrayUrlToStrId($apiarray['climate']);
			$modarray['terrain'] = $this->ArrayUrlToStrId($apiarray['terrain']);
			$modarray['surface_water'] = $this->ArrayUrlToStrId($apiarray['surface_water']);
			$modarray['residents'] = $this->ArrayUrlToStrId($apiarray['residents']);
			$modarray['films'] = $this->ArrayUrlToStrId($apiarray['films']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter name",
			];
			$outarray['swdate'][] = ['title' => "Name", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Diameter, km", 'listvalue' => [$this->model_array['diameter']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Rotation period", 'listvalue' => [$this->model_array['rotation_period']], 'comment'=>"The number of standard hours it takes for this planet to complete a single rotation on its axis", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Orbital period", 'listvalue' => [$this->model_array['orbital_period']], 'comment'=>"The number of standard days it takes for this planet to complete a single orbit of its local star.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Gravity", 'listvalue' => [$this->model_array['gravity']], 'comment'=>" A number denoting the gravity of this planet, where 1 is normal or 1 standard G. 2 is twice or 2 standard Gs. 0.5 is half or 0.5 standard Gs.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Population", 'listvalue' => [$this->model_array['population']], 'comment'=>"The average population of sentient beings inhabiting this planet", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Climate", 'listvalue' => [$this->model_array['climate']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Terrain", 'listvalue' => [$this->model_array['terrain']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Surface water, %", 'listvalue' => [$this->model_array['surface_water']], 'comment'=>"The percentage of the planet surface that is naturally occurring water or bodies of water.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Residents", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['residents'],'people'), 'comment'=>"", 'type'=>"people"];
			$outarray['swdate'][] = ['title' => "Films", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['films'],'films'), 'comment'=>"", 'type'=>"films"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}

# class Species  <===============================================
class Species extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['name'];
			$modarray['classification'] = $apiarray['classification'];
			$modarray['designation'] = $apiarray['designation'];
			$modarray['average_height'] = $apiarray['average_height'];
			$modarray['average_lifespan'] = $apiarray['average_lifespan'];
			$modarray['eye_color'] = $apiarray['eye_colors'];
			$modarray['hair_color'] = $apiarray['hair_colors'];
			$modarray['skin_color'] = $apiarray['skin_colors'];
			$modarray['language'] = $apiarray['language'];
			$modarray['homeworld'] = $this->UrltoId($apiarray['homeworld']);
			$modarray['people'] = $this->ArrayUrlToStrId($apiarray['people']);
			$modarray['films'] = $this->ArrayUrlToStrId($apiarray['films']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter name",
			];
			$outarray['swdate'][] = ['title' => "Name", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Classification", 'listvalue' => [$this->model_array['classification']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Designation", 'listvalue' => [$this->model_array['designation']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Average height, cm", 'listvalue' => [$this->model_array['average_height']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Average lifespan, years", 'listvalue' => [$this->model_array['average_lifespan']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Eye color", 'listvalue' => [$this->model_array['eye_color']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Hair color", 'listvalue' => [$this->model_array['hair_color']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Skin color", 'listvalue' => [$this->model_array['skin_color']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Language", 'listvalue' => [$this->model_array['language']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Homeworld", 'listvalue' => [$this->model_array['homeworld'] => $this->dbcur->nameID('planets', $this->model_array['homeworld'])], 'comment'=>"The list of a planet that this species originates from.", 'type'=>"planets"];
			$outarray['swdate'][] = ['title' => "People", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['people'],'people'), 'comment'=>"", 'type'=>"people"];
			$outarray['swdate'][] = ['title' => "Films", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['films'],'films'), 'comment'=>"", 'type'=>"films"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}

# class Starships  <===============================================
class Starships extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['name'];
			$modarray['model'] = $apiarray['model'];
			$modarray['starship_class'] = $apiarray['starship_class'];
			$modarray['manufacturer'] = $apiarray['manufacturer'];
			$modarray['cost_in_credits'] = $apiarray['cost_in_credits'];
			$modarray['length'] = $apiarray['length'];
			$modarray['crew'] = $apiarray['crew'];
			$modarray['passengers'] = $apiarray['passengers'];
			$modarray['max_atmosphering_speed'] = $apiarray['max_atmosphering_speed'];
			$modarray['hyperdrive_rating'] = $apiarray['hyperdrive_rating'];
			$modarray['MGLT'] = $apiarray['MGLT'];
			$modarray['cargo_capacity'] = $apiarray['cargo_capacity'];
			$modarray['consumables'] = $apiarray['consumables'];
			$modarray['films'] = $this->ArrayUrlToStrId($apiarray['films']);
			$modarray['pilots'] = $this->ArrayUrlToStrId($apiarray['pilots']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter name",
			];
			$outarray['swdate'][] = ['title' => "Name", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Model", 'listvalue' => [$this->model_array['model']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Starship class", 'listvalue' => [$this->model_array['starship_class']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Manufacturer", 'listvalue' => [$this->model_array['manufacturer']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Cost, credits", 'listvalue' => [$this->model_array['cost_in_credits']], 'comment'=>"The cost of this starship new, in galactic credits.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Length, m", 'listvalue' => [$this->model_array['length']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Crew", 'listvalue' => [$this->model_array['crew']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Passengers", 'listvalue' => [$this->model_array['passengers']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Max atmosphering speed", 'listvalue' => [$this->model_array['max_atmosphering_speed']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Hyperdrive rating", 'listvalue' => [$this->model_array['hyperdrive_rating']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "MGLT", 'listvalue' => [$this->model_array['MGLT']], 'comment'=>"The Maximum number of Megalights this starship can travel in a standard hour. A Megalight is a standard unit of distance and has never been defined before within the Star Wars universe. This figure is only really useful for measuring the difference in speed of starships. We can assume it is similar to AU, the distance between our Sun (Sol) and Earth.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Cargo capacity, kg", 'listvalue' => [$this->model_array['cargo_capacity']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Consumables", 'listvalue' => [$this->model_array['consumables']], 'comment'=>"The maximum length of time that this starship can provide consumables for its entire crew without having to resupply.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Films", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['films'],'films'), 'comment'=>"", 'type'=>"films"];
			$outarray['swdate'][] = ['title' => "Pilots", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['pilots'],'people'), 'comment'=>"An list of people that this starship has been piloted by.", 'type'=>"people"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}

# class Vehicles  <===============================================
class Vehicles extends Elements{

	protected function getFromApi($id,$type){
		$apiarray = Api::getArray("info",$type,$id);
		if ($apiarray) {
			$modarray['id'] = $this->UrltoId($apiarray['url']);
			$modarray['name'] = $apiarray['name'];
			$modarray['model'] = $apiarray['model'];
			$modarray['vehicle_class'] = $apiarray['vehicle_class'];
			$modarray['manufacturer'] = $apiarray['manufacturer'];
			$modarray['cost_in_credits'] = $apiarray['cost_in_credits'];
			$modarray['length'] = $apiarray['length'];
			$modarray['crew'] = $apiarray['crew'];
			$modarray['passengers'] = $apiarray['passengers'];
			$modarray['max_atmosphering_speed'] = $apiarray['max_atmosphering_speed'];
			$modarray['cargo_capacity'] = $apiarray['cargo_capacity'];
			$modarray['consumables'] = $apiarray['consumables'];
			$modarray['films'] = $this->ArrayUrlToStrId($apiarray['films']);
			$modarray['pilots'] = $this->ArrayUrlToStrId($apiarray['pilots']);
		}else{
			$modarray = false; // don't exist datas from API
		}
		return $modarray;
	}

	public function getArray(){

		if ($this->model_array) { // Datas prepared
			$outarray = 
			[
				'name' => $this->model_array['name'], // Luke Skywalker
				'search' => "Enter name",
			];
			$outarray['swdate'][] = ['title' => "Name", 'listvalue' => [$this->model_array['name']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Model", 'listvalue' => [$this->model_array['model']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Vehicles class", 'listvalue' => [$this->model_array['vehicle_class']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Manufacturer", 'listvalue' => [$this->model_array['manufacturer']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Cost, credits", 'listvalue' => [$this->model_array['cost_in_credits']], 'comment'=>"The cost of this starship new, in galactic credits.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Length, m", 'listvalue' => [$this->model_array['length']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Crew", 'listvalue' => [$this->model_array['crew']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Passengers", 'listvalue' => [$this->model_array['passengers']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Max atmosphering speed", 'listvalue' => [$this->model_array['max_atmosphering_speed']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Cargo capacity, kg", 'listvalue' => [$this->model_array['cargo_capacity']], 'comment'=>"", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Consumables", 'listvalue' => [$this->model_array['consumables']], 'comment'=>"The maximum length of time that this vehicle can provide consumables for its entire crew without having to resupply.", 'type'=>""];
			$outarray['swdate'][] = ['title' => "Films", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['films'],'films'), 'comment'=>"", 'type'=>"films"];
			$outarray['swdate'][] = ['title' => "Pilots", 'listvalue' => $this->StrIdToArraiUrl($this->model_array['pilots'],'people'), 'comment'=>"An list of people that this vehicle has been piloted by.", 'type'=>"people"];
		}else{ // Don't prepared the array
			$outarray = array();
		}
		return $outarray;
	}
}


?>