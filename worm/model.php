<?php
namespace worm;

class Model
{
	protected $db;
	protected $table;
	protected $cols = array();
	
	public function __construct(\PDO $db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}
	
	public function __get($property)
	{
		if(!array_key_exists($property, $this->cols))
		{
			throw new \OutOfBoundsException('The '.$this->table.' table doesn\'t have a '.$property.' column.');
		}
		
		return $this->cols[$property];
	}
	
	public function __set($property, $value)
	{
		$this->cols[$property] = $value;
	}
	
	public function save()
	{
		$cols = array();
		$vals = array();
		
		try
		{
			$id = $this->id;
			return $this->update();
		}
		catch(\OutOfBoundsException $e)
		{
			return $this->insert();
		}
	}

	public function delete()
	{
		$sql = 'DELETE FROM '.$this->table.' WHERE ';
		try
		{
			/* If the primary key isn't available, it
			 * means this object was created for the sake
			 * of insertion, and not the result of a select query.
			 * If the user wants to delete 
			 * a database entry, he must use the Finder class.
			 */

			$id = $this->id;
			$sql .= 'id = ?';
			$args = array($id);

			return $this->raw_exec($sql, $args);
		}
		catch(\OutOfBoundsException $e)
		{
			return false;
		}
	}

	public function insert()
	{
		$sql = sprintf('INSERT INTO %s ', $this->table);
		$cols = array();
		$args = array();

		foreach($this->cols as $k => $v)
		{
			$cols[] = $k;
			$args[] = $v;
		}

		$sql .= '('.implode(', ', $cols).')';
		$sql .= ' VALUES ';
		$sql .= '('.implode(', ', str_split(str_repeat('?', sizeof($args)))).')';

		return $this->raw_exec($sql, $args);
	}

	public function update()
	{
		$sql = sprintf('UPDATE %s SET ', $this->table);
		$cols = array();
		$args = array();

		foreach($this->cols as $k => $v)
		{
			if($k == 'id')
			{
				continue;
			}
			
			$cols[] = $k.' = ?';
			$args[] = $v;
		}

		$sql .= implode(', ', $cols);
		$sql .= ' WHERE id = ?';
		$args[] = $this->id;
		
		return $this->raw_exec($sql, $args);
	}

	public function raw_exec($sql, array $args = array())
	{
		$query = $this->db->prepare($sql);
		$query->execute($args);
		
		//var_dump($sql, $args);
		//return;

		return $query->rowCount();
	}
}
