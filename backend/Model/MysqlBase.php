<?php
	namespace Model;

	class MysqlBase {	

		protected static $tableName;
		protected static $DB = null;
		protected static $query = []; 
		protected static $bindParam = [
			'flags' => '',
			'values' => []
		];

		public function __construct($tableName)
		{
			static::$tableName = $tableName;
			static::$DB = new Connection();
		}

		public function __destruct()
		{
			if (self::$DB !== null) {
				self::$DB->close();
			}
		}

		public static function getById(int $id)
		{
			self::$query['conditions'] = [
				'id' => [
					'value' => $id, 
					'operator' => ' = ',	// ' = ' or ' LIKE '
				]
			];
			return self::select();
		}

		public static function get($filter, $order = 'ASC')
		{
			$filters = [];
			foreach ($filter as $column => $value) {
				$filters[$column] = [
					'value' => $value,
					'operator' => (strpos($value, '%') === false) ? ' = ' : ' LIKE '
				];
			}
			self::$query['conditions'] = $filters;
			return self::select($order);
		}

		public static function getAll($order = 'ASC')
		{
			return self::select($order);
		}

		protected static function select($order = 'ASC')
		{
			self::$query['order'] = 'created ' . $order;
			$data = [];
			return self::queryActionWrapper($data, 'select', 'SELECT * FROM ');
		}

		public static function insert(array $data)
		{
			$result = self::queryActionWrapper($data, 'insert', 'INSERT INTO ');
			$data['id'] = self::getConn()->inserted_id();
			return $result ? $data : false;
		}

		public static function update(array $data)
		{
			$id = intval($data['id'] ?? 0);
			self::$query['conditions'] = [
				'id' => [
					'value' => $id, 
					'operator' => ' = ',
				]
			];			
			unset($data['id']);
			$result = self::queryActionWrapper($data, 'update', 'UPDATE ');
			$data['id'] = $id;
			return $result ? $data : false;
		}

		public static function deleteById(int $id)
		{
			$data = [];
			self::$query['conditions'] = [
				'id' => [
					'value' => $id, 
					'operator' => ' = ',
				]
			];			
			return self::queryActionWrapper($data, 'delete', 'DELETE FROM ');
		}



		public static function queryActionWrapper(&$data, $mode, $sql) {
			self::$query['mode'] = $mode;
			self::$query['sql'] = $sql . static::$tableName;
			if (in_array($mode, ['insert', 'update'])) {
				self::$query['values'] = self::$query['columns'] = [];
				self::$query['columns'] = static::autofiller($data);
			}
			return self::queryBuilder();
		}

		protected static function autofiller(&$data) 
		{
			if (!empty(static::$autofill)) {
				foreach (static::$autofill as $key => $values) {
					if ($values[0] == self::$query['mode']) {
						$data[$key] = $values[1];
					}
				}
			}
			return $data;
		}

		protected static function getBindParams($data, $type) {
			$columns = static::$columns;
			$bind = &self::$bindParam;
			$sql = &self::$query['sql'];
			$updateFields = [];
			foreach ($data as $column => $value) {
				if ($type == 'conditions') {
					$sql .= $column . $value['operator'] . '?';
				} elseif($type == 'update') {
					$updateFields[] = $column.' = ?';
				}
				$bind['flags'] .= $columns[$column] ?? 's';
				$bind['values'][] = $type == 'conditions' ? $value['value'] : $value;
			}		

			if ($type === 'insert') {
				$keys = array_keys($data);
				$values = ' VALUES (' . implode(', ', array_fill(0, count($keys), '?')) . ')';
				$columns = ' (' . implode(', ', $keys) . ')';
				$sql .= $columns . $values;

			} elseif ($type === 'update') {
				$sql .= ' SET ' . implode(', ', $updateFields);
			}

		}

		protected static function queryBuilder() {
			$sql = &self::$query['sql'];
			$conditions = self::$query['conditions'] ?? [];
			$bind = &self::$bindParam;
			$columns = self::$query['columns'] ?? false;

			if (!empty($columns)) {
				self::getBindParams($columns, self::$query['mode']);
			}

			if (count($conditions) > 0) {
				$sql .= ' WHERE ';
			}
			
			self::getBindParams($conditions, 'conditions');
			if (!empty(self::$query['order'])) {
				$sql .= ' ORDER BY ' . self::$query['order'];
			}

			$result = self::getConn()->execute(
				$sql, 
				$bind['flags'], 
				$bind['values'],
				self::$query['mode']
			);

			return $result;
		}

		public static function getConn() {
			$DB = &static::$DB;
			if ($DB === null) {
				$DB = new Connection();
			}
			return $DB;
		}

	}

?>