<?php
	namespace Model;

	class UserMysql extends MysqlModel {

		protected static $columns = [
			'id' => 'i',
			'name' => 's',
			'email' => 's',
			'password' => 's',
			'created' => 's',
			'updated' => 's'
		];

		protected static $autofill;

		protected static $validation = [];

		public function __construct() 
		{
			parent::__construct('users');
			self::$autofill = [
				'password' => ['insert', md5(uniqid())],
				'created' => ['insert', date("Y-m-d H:i:s")],
				'updated' => ['update', date("Y-m-d H:i:s")],
			];
		}
		
		public static function find(int $id)
		{
			return static::getById($id);
		}

		public static function all($order = "ASC")
		{
			return static::getAll($order);
		}

		public static function filter($filter, $order = "ASC")
		{
			return static::get($filter, $order);
		}
		
		public static function saveData(array $data)
		{
			$action = empty($data['id']) ? 'insert' : 'update';
			return static::$action($data);
		}

		public static function remove(int $id)
		{
			return static::delete($id);
		}
	}

?>