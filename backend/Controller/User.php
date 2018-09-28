<?php

	namespace Controller;

	class User extends App
	{

		public function index()
		{
			$order = $this->dispatchedUrl['sort'] ?? 'ASC';
			static::success( $this->User::all($order));
		}

		public function find(int $id)
		{
			static::success($this->User::getById($id));
		}

		public function search() {
			$urlData = $this->dispatchedUrl;
			$order = $urlData['sort'] ?? 'ASC';
			$name = $urlData['name'] ?? '';
			$filter = [
				'name' => $name . '%'
			];
			static::success($this->User::filter($filter, $order));
		}

		public function add()
		{
			$result = $this->User::add($this->request['data']);
			static::response ($result, !!$result, $result ? 'Executed' : 'Failed');
		}

		public function delete()
		{
			$id = $this->dispatchedUrl['id'] ?? false;
			if (!$id) { return static::error('Need id for delete user'); }
			$result = $this->User::delete($id);
			static::response (['id' => $id], $result, $result ? 'Executed' : 'Failed');
		}


	}
?>