<?php

	namespace Model;

	class UserApi {
		
		protected static $DB;
				
		public function __construct() 
		{
			//unset($_SESSION['DB']);
			static::$DB = json_decode($_SESSION['DB'] ?? "[]", true);
		}
		
		public static function find(string $id, $index = false)
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
				return $order == 'ASC' ? $cmp >= 0 :  $cmp < 0;
			});
			return $users;
		}

		public static function filter($filter, $order = "ASC")
		{
			$users = self::$DB;
			$len = strlen($filter['name']);
			$result = array_filter(
				$users, 
				function($key, $user) use ($filter, $len){
					return substr($user['full_name'], 0, $len) == $filter['name'];
				}, ARRAY_FILTER_USE_BOTH);
			usort($users, function($a, $b) use ($order) {
				$cmp = strcmp($a['registeredAt'], $b['registeredAt']);
				return $order == 'ASC' ? $cmp >= 0 :  $cmp < 0;
			});			
			return $result;
		}

		public static function saveData(array $data)
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
				array_push(self::$DB, $user);
			}
			
			$_SESSION['DB'] = json_encode(self::$DB);
			return true;
		}

		public static function remove($id)
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