<?php
	namespace Model;

	class UserMysql extends MysqlModel implements UserInterface {

		protected static $columns = [
			'id' => 'i',
			'name' => 's',
			'email' => 's',
			'password' => 's',
			'created' => 's',
			'updated' => 's'
		];
		public static $autofill;
		//rpk - regex pattern key
		public static $validation = [
			'name' => [
				'rule' => 'required|min:3|max:20|rpk:NAME',
				'message' => 'Name must be between 3-20 character!'
			],
			'email' => [
				'rule' => 'required|min:7|max:20|rpk:EMAIL',
				'message' => 'Must be valid email!'
			],
		];

		public function __construct() 
		{
			parent::__construct('users');
			self::$autofill = [
				'password' => ['insert', md5(uniqid())],
				'created' => ['insert', date("Y-m-d H:i:s")],
				'updated' => ['update', date("Y-m-d H:i:s")],
			];
		}
		
		public static function find($id)
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
		
		public static function add(array $data)
		{
			$action = empty($data['id']) ? 'insert' : 'update';
			return static::$action($data);
		}

		public static function delete($id)
		{
			return static::deleteById($id);
		}
	}

?>