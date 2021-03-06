<?php

	namespace Router;
	
	use \Controller\App;

	define('ROUTES', 
		[
			'users' => [
				'GET',
				'User@index'
			],
			'users/:sort' => [
				'GET',
				'User@index',
				'SORT'
			],
			'user/save' => [
				'POST',
				'User@add'
			],
			'user/delete/:id' => [
				'GET',
				'User@delete',
				'SLUG'
			],
			'search/user/:name' => [
				'GET',
				'User@search', 
				'NAME_EX'
			],
			'search/user/:name/:sort' => [
				'GET',
				'User@search', 
				'NAME_EX', 
				'SORT'
			],
		]
	);
	
	class Router
	{
		public $datasource;
		public $dispatchedUrl;
		
		public function __construct()
		{
			$this->dispatchUrl();
		}

		protected function dispatchUrl()
		{

			$query = $_GET['query'] ?? false;

			if (!$query || !self::isValidData($query, 'URL_QUERY')) {
				return App::error( 'Invalid url');
			}

			$queryArray = explode('/', $query);

			if (count($queryArray) < 2) {
				App::error('Incorrect url, we need datasource and model');
			}

			$this->datasource = array_shift($queryArray);

			if (empty(DATASOURCE_LIST[$this->datasource])) {
				$list = implode(', ', array_keys(DATASOURCE_LIST));
				App::error('Incorrect datasource in url, we have: ' . $list);
			}

			foreach (ROUTES as $url => $routeData) {

				$urlArray = explode('/', $url);
				$dispatchedUrl = [];

				if (count($queryArray) != count($urlArray)) {
					continue;
				}

				$index = 0;
				$actionInUrl = false;
				$routeMethod = strtoupper(array_shift($routeData));

				if ($routeMethod !== METHOD) {
					continue;
				}
				
				foreach ($urlArray as $urlFragment) {
			
					$diff = $index - $actionInUrl;
					if (empty($routeData[$diff])) {
						return $this->dispatchedUrl = $dispatchedUrl;
					}
					
					$routeDataPair = $routeData[$diff];


					if ($index == 0) {
						if ($urlFragment !== $queryArray[$index]) {
							break;
						}

						$coreUrl = explode("@", $routeDataPair);
						$dispatchedUrl = [ 
							'controller' =>  $coreUrl[0],
							'action' =>  $coreUrl[1] ?? 'index'
						];
					} elseif ($urlFragment[0] === ":") {
						$varName = substr($urlFragment, 1);
						
						if ( !empty($routeDataPair) && !static::isValidData($queryArray[$index], $routeDataPair)) {
							$dispatchedUrl = [];
							break;
						}

						$dispatchedUrl[$varName] = $queryArray[$index];
					} elseif ($index == 1) {
						if ($urlFragment !== $queryArray[$index]) {
							break;
						}
						$actionInUrl = true;
					} else {
						break;
					}

					$index++;

					if ((count($dispatchedUrl) + $actionInUrl) > count($queryArray)) {
						return $this->dispatchedUrl = $dispatchedUrl;
					}
				}
			}

			return App::error( 'Wrong url');
		}

		
		protected static function isValidData(string $data, $pattern) {
			return isset(PATTERNS[$pattern]) 
				? preg_match(PATTERNS[$pattern], $data) > 0
				: true;
		}
	}
?>