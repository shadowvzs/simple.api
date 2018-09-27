<?php

	namespace Model;

	class App 
	{
		protected $auth;
		protected $datasource;
		public static $dispatchedUrl;
		
		public function __construct()
		{
			$this->init();
		}

		protected function init()
		{

			$this->auth = $this->loadAuthData();
			$this->dispatchUrl();
			$this->sendData();
		}

		protected function dispatchUrl()
		{

			$query = $_GET['query'] ?? false;
			$reqMethod = in_array(
				strtoupper($_SERVER['REQUEST_METHOD']), 
				['POST' , 'PUT']
			) ? 'POST' : 'GET';

			if (!$query || !static::isValidData($query, 'URL_QUERY')) {
				return static::error( 'Invalid url');
			}

			$dispatchedUrl = [];
			$queryArray = explode('/', $query);

			if (count($queryArray) < 2) {
				static::error('Incorrect url, we need datasource and model');
			}

			$this->datasource = array_shift($queryArray);

			if (empty(DATASOURCE_LIST[$this->datasource])) {
				$list = implode(', ', array_keys(DATASOURCE_LIST));
				static::error('Incorrect datasource in url, we have: ' . $list);
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

				if ($routeMethod !== $reqMethod) {
					continue;
				}
				
				foreach ($urlArray as $urlFragment) {
			
					$routeDataPair = $routeData[$index - $actionInUrl];

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
						return self::$dispatchedUrl = $dispatchedUrl;
					}
				}
			}

			return static::error( 'Wrong url');
		}

		public function sendData()
		{
			$urlData = self::$dispatchedUrl;
			$action = $urlData['action'];
			$controller = ucfirst($urlData['controller']);
			$repoClass = "\\Repo\\" . $controller . 'Repository';
			$datasourceClass = 'Model\\' . $controller.ucfirst($this->datasource);
			$obj = new $repoClass($datasourceClass);
			$obj->$action();
			//unset($_SESSION['DB']);
			
			/*
			$obj->save([
			//	'id' => '5bab64178323f',
				'name' => 'aasd',
				'email' => '1vbn@cim.hi',
				'registeredAt' => '2016. 08. 01 00:00:'
			]);
			*/
			
			var_dump($_SESSION);
			
		}

		protected function loadAuthData()
		{

			return [
				'id' => 1,
				'name' => 'John Smith',
				'email' => 'something@test.com',
				'role' => 2
			];

		}

		public static function error ($message = "Unknown error!")
		{
			static::response(false, false, $message);
		}

		public static function success ($data)
		{
			static::response($data, true, false);
		}

		public static function response($data, $status = true, $message = false)
		{
			die(json_encode([
				'data' => $data,
				'status' => $status,
				'message' => $message,
			]));
		}

		public static function isValidData($data, $pattern) {
			return isset(PATTERNS[$pattern]) 
				? preg_match(PATTERNS[$pattern], $data) > 0
				: true;
		}
	}

?>