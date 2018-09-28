<?php

	namespace Model;

	interface UserInterface
	{
		public static function find($id);
		public static function all($order);
		public static function filter($filter, $order);
		public static function add(array $data);
		public static function delete($id);
	}
?>