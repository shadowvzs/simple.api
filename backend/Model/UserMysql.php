<?php
	namespace Model;

	class UserMysql extends MysqlBase implements UserInterface {

		protected static $columns = [
			'id' => 'i',
			'username' => 's',
			'name' => 's',
			'email' => 's',
			'password' => 's',
			'created' => 's',
			'updated' => 's'
		];
		public static $autofill;
		public static $validation = [
			'username' => [
				'rule' => 'required|min:3|max:20|rpk:ALPHA_NUM',	//rpk = regex pattern key
				'message' => 'Name must be between 3-20 alpha-numeric character!'
			],
			'name' => [
				'rule' => 'required|min:3|max:30|rpk:NAME',
				'message' => 'Name must be between 3-20 character!'
			],
			'email' => [
				'rule' => 'required|min:7|max:50|rpk:EMAIL',
				'message' => 'Must be valid email!'
			],
		];

		public function __construct() 
		{
			parent::__construct('users');
			self::$autofill = [
				'password' => ['insert', substr(md5(uniqid()), 0, 20)],
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