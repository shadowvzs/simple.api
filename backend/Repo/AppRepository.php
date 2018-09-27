<?php

	namespace Repo;

	class AppRepository implements RepoInterface 
	{
		public function find(int $id)
		{
			
		}
		
		public function all()
		{
			
		}
		
		public function save(array $data)
		{
			
		}
		
		public function insert(array $data)
		{
			
		}
		
		public function update(array $data)
		{
			
		}
		
		public function delete()
		{
			
		}
	}

	interface RepoInterface
	{
		public function find(int $id);
		public function all();
		public function save(array $data);
		public function insert(array $data);
		public function update(array $data);
		public function delete();
	}
?>