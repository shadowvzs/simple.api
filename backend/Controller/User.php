<?php

	namespace Controller;

	class User extends App
	{
		
		public function index()
		{
			$order = static::$dispatchedUrl['sort'] ?? 'ASC';
			static::success( $this->User::all($order));
		}

		public function find(int $id)
		{
			static::success($this->User::getById($id));
		}

		public function search() {
			$urlData = static::$dispatchedUrl;
			$order = $urlData['sort'] ?? 'ASC';
			$name = $urlData['name'] ?? '';
			$filter = [
				'name' => $name . '%'
			];
			static::success($this->User::filter($filter, $order));
		}

		public function add(array $data = [])
		{
			if (empty($data)) {
				if (empty($_POST['User'])) {
					static::error('Nothing to save');
				}
				$data = $_POST['User'];
			}

			$result = $this->User::add($data);
			static::response (false, $result, $result ? 'Executed' : 'Failed');
		}

		public function delete()
		{
			$id = static::$dispatchedUrl['id'] ?? false;
			if (!$id) { return static::error('Need id for delete user'); }
			$result = $this->User::delete($id);
			static::response (false, $result, $result ? 'Executed' : 'Failed');
		}


	}
?>