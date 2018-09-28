<?php

	namespace Model;

	class UserApi implements UserInterface {
		
		protected static $DB;
		public static $autofill;
		public $validation = [];
				
		public function __construct() 
		{
			static::$DB = json_decode($_SESSION['DB'] ?? "[]", true);
		}
		
		public static function find($id, $index = false)
		{
			foreach (self::$DB as $key => $user) {
				if ($user['id'] == $id) {
					return $index ? $key : $user;
				}
			}
			return [];
		}

		public static function all($order = 'ASC')
		{
			$users = self::$DB;
			usort($users, function($a, $b) use ($order) {
				$cmp = strcmp($a['registeredAt'], $b['registeredAt']);
				return $order == 'DESC' ? $cmp >= 0 :  $cmp < 0;
			});
			return $users;
		}

		public static function filter($filter, $order = "ASC")
		{
			$users = self::$DB;
			$filter['name'] = str_replace('%', '', $filter['name']);
			$len = strlen($filter['name']);
			$result = array_filter(
				$users, 
				function($user) use ($filter, $len){
					return substr($user['fullname'], 0, $len) == $filter['name'];
				}, ARRAY_FILTER_USE_BOTH);
			usort($users, function($a, $b) use ($order) {
				$cmp = strcmp($a['registeredAt'], $b['registeredAt']);
				return $order == 'DESC' ? $cmp >= 0 :  $cmp < 0;
			});	
			return array_values($result);
		}

		public static function add(array $data)
		{
			if (empty($data['id'])) {
				$user = [];
			} else {
				$index = self::find($data['id'], true);
				if (empty($index)) { return false; }
				$user = &self::$DB[$index];
				if (empty($user)) { return false; }
			}
			
			foreach ($data as $key => $value) {
				$user[$key] = $value;
			}
			
			if (empty($data['id'])) {
				$user['id'] = uniqid();
				$user['registeredAt'] = date("Y-m-d H:i:s");
				array_push(self::$DB, $user);
			}
			
			$_SESSION['DB'] = json_encode(self::$DB);
			return $user;
		}

		public static function delete($id)
		{
			$index = self::find($id, true);
			if (empty($index) && $index !== 0) { return false; }
			if (!isset(self::$DB[$index])) {
				return false;
			}
			unset (self::$DB[$index]);
			$_SESSION['DB'] = json_encode(self::$DB);
			return true;
		}
	}

?>