<?php

	namespace Controller;

	class App 
	{
		protected $auth;
		protected $datasource;
		public $dispatchedUrl;
		
		// if not dynamic then it would be \Model\UserMysql
		public function __construct(array $dispatchedUrl, $Repo)
		{
			$this->dispatchedUrl = $dispatchedUrl;
			$className = substr(static::class, strlen(__NAMESPACE__) + 1);
			$this->$className = $Repo;
			$data = $_POST ?? [];
			$existData = !empty($data[$className]);
			if (!empty($Repo::$validation) && $existData) {
				$allow = $this->validation($data['User'], $Repo::$validation);
			}

			if ($existData) {
				$this->request['data'] = $data['User'];
			}
		}

		protected function init()
		{
			$this->auth = $this->loadAuthData();
		}
		
		protected function validation($data, $ruleFields)
		{
			$messages = [];
			foreach ($ruleFields as $key => $value) {
				if (empty($value['rule'])) { continue; }
				$rules = explode('|', $value['rule']);
				foreach ($rules as $rule) {
					if (strpos($rule, ':') !== false) {
						list ($rule, $param) = explode(':', $rule);
					}
					if ($rule == "required") {
						if (empty($data[$key])) {
							$messages[] = $value['message'] ?? "Empty $key";
							break;
						}
						continue;
					} elseif($rule == "min") {
						if (strlen($data[$key]) < intval($param)) {
							$messages[] = $value['message'] ?? "Too short $key";
							break;
						}
					} elseif($rule == "max") {
						if (strlen($data[$key]) > intval($param)) {
							$messages[] = $value['message'] ?? "Too long $key";
							break;
						}
					} elseif($rule == "rpk") {
						if (isset(PATTERNS[$param])) {
							continue;
						}
						if (preg_match(PATTERNS[$param], $data[$key]) == 0) {
							$messages[] = $value['message'] ?? "Invalid $key";
							break;
						}
					}
				}
			}
			
			if (!empty($messages)) {
				self::error($messages);
			}
		}

		public function sendData()
		{
			$urlData = $this->$dispatchedUrl;
			$action = $urlData['action'];
			$controller = ucfirst($urlData['controller']);
			$repoClass = "\\Repo\\" . $controller . 'Repository';
			die(__class__);
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
	}

?>