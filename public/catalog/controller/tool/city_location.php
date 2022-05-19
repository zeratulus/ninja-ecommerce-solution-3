<?php

class ControllerToolCityLocation extends \Ninja\NinjaController {

	private $_route = 'tool/city_location';

	public function index()
	{
		$addr = new RemoteAddress();
		$ip = $addr->getIpAddress();
		$ip = '93.170.66.135';
		$this->getSxGeoCityByIp($ip);
	}

	private function getSxGeoCityByIp($ip) {
		if (empty($this->getSession()->data['sxgeo'])) {
			$sxgeo = new SxGeo(DIR_STORAGE . 'SxGeoCity.dat');

			// Краткая информация о городе или код страны (если используется база SxGeo Country)
			$result = $sxgeo->get($ip);
			$this->getSession()->data['sxgeo'] = $result;
		}
	}

	public function html_location() {
		$city_title = false;

		// Trying to get HTML5 location
		if (isset($this->request->post['latlng'])) {
			$latlng = $this->request->post['latlng'];
			//lang code
			$code = $this->getLanguage()->get('code');
			//TODO: get api key
			$key = 'AIzaSyCMdZjPn2huh4hpFyTfHVaS5WHIKYixlRc';

			//format: ?latlng=lat,lng
			//https://maps.googleapis.com/maps/api/geocode/json?latlng=50.9216,34.80029&key=AIzaSyCMdZjPn2huh4hpFyTfHVaS5WHIKYixlRc
			$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latlng}&sensor=false&language={$code}&key={$key}";
			$geo_info = file_get_contents($url);
			$result = json_decode($geo_info);
			if ($result->status == 'OK') {
				$city_title = $this->getGmapsCity($result);
				//TODO: save data to cache
			}
		}

//		if ($city_title !== false) {
//			$this->load->model('catalog/cities');
//			$city_info = $this->model_catalog_cities->getCityByTitle($city_title);
//			if (!empty($city_info)) {
//				$this->session->data['city_id'] = $city_info['id'];
//			} else {
//				$city_info = $this->model_catalog_cities->getCityByTitle('Санкт-Петербург');
//				$this->session->data['city_id'] = $city_info['id'];
//				$city_title = $city_info['title'];
//			}
//		}

//        $city_json = array(
//            'title' => $city_title,
//            'isFinded' => (!empty($city_info) && ($city_info['id'] > 0))
//        );

		echo $city_title;
	}

	// Get city title from Google Maps API JSON (JSON decoded as class)
	private function getGmapsCity($json) {
		$result = false;
		foreach ($json->results[0]->address_components as $component) {
			foreach ($component->types as $type) {
				if (strtolower($type) == 'locality') { //this is City!!! =]
					$result = $component->long_name;
					break(2);
				}
			}
		}
		return $result;
	}


//{"city":{"id":692194,"lat":50.9216,"lon":34.80029,"name_ru":"\u0421\u0443\u043c\u044b","name_en":"Sumy"},"country":{"id":222,"iso":"UA"}}
}