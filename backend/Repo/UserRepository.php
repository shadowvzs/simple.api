<?php

	namespace Repo;

	use \Model\App;

	class UserRepository extends AppRepository
	{
		
		private static $ds;		// datasource
	
		public function __construct(string $ds) 
		{
			static::$ds = new $ds;
		}
		
		public function find(int $id)
		{
			App::success(static::$ds::getById($id));
		}

		public function all()
		{
			$order = App::$dispatchedUrl['sort'] ?? 'ASC';
			App::success( static::$ds::all($order));
		}

		public function search() {
			$urlData = App::$dispatchedUrl;
			$order = $urlData['sort'] ?? 'ASC';
			$name = $urlData['name'] ?? '';
			$filter = [
				'name' => $name . '%'
			];
			App::success(static::$ds::filter($filter, $order));
		}

		public function save(array $data = [])
		{
			if (empty($data)) {
				if (empty($_POST['User'])) {
					App::error('Nothing to save');
				}
				$data = $_POST['User'];
			}

			$result = static::$ds::saveData($data);
			App::response (false, $result, $result ? 'Executed' : 'Failed');
		}

		public function delete()
		{
			$id = App::$dispatchedUrl['id'] ?? false;
			if (!$id) { return App::error('Need id for delete user'); }
			$result = static::$ds::remove($id);
			App::response (false, $result, $result ? 'Executed' : 'Failed');
		}


	}
?>