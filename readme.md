####wORM, a frugal ORM for PHP.

### Intro

wORM is a lightweight PHP 5.3+ ORM that uses PDO's faculties to provide an easy object oriented interface to the database schema. All it needs is the PDO instance and the name of the table :

```php
	$pdo = new PDO('mysql:host=localhost; dbname=database', 'user', 'pswd');
	$finder = new worm\QueryHelper($pdo, 'users');
```


The QueryHelper class offers a set of methods to build SQL queries without having to write SQL code and without worrying about security :

```php
	$finder = new worm\QueryHelper($pdo, 'users');
	$users = $finder	->where_op('registration_time', '>', time() - 24 * 3600 * 3)
					->select('id', 'username')
					->order_by('registration_time')
					->find();

	foreach($users as $u)
	{
		echo $u->username.' registered at '.date('Y-m-d H:is', $u->registration_time).PHP_EOL;
	}
```

The find() method accepts an optional parameter, if set to TRUE then it will only retrieve one single row :

```php
	$user = $finder->by_id(3)->find(true);
	echo $user->username;
```

QueryHelper has a delete() method to quickly delete specific entries from the database :

```php

	$douchebagCount = $finder->where('comments', 'I hate this dude')->delete();
	echo 'Removed '.$douchebagCount.' douchebags.';
```

### Manual queries

QueryHelper also provides a set of methods to manually write and execute SQL queries :

```php
	PDOStatement QueryHelper::raw_select(string $sql, [array $args])
	int QueryHelper::raw_exec(string $sql, [array $args])
	(Model[] | bool) QueryHelper::build_models(string $sql, [array $args])
```

QueryHelper::raw_select() is just an abstraction for PDO's prepare() and execute() methods.
It returns a PDOStatement object you can iterate over :

```php

	$matches = $finder->raw_select('SELECT username FROM users WHERE username LIKE ?', ['h%']);
	foreach($matches as $m)
	{
		/* accessing $m's entries depends on your PDO fetch mode setting */
	}
```

QueryHelper::build_models() is the same as raw_select(), except it will return an array of Model objects :

```php

	$matches = $finder->build_models('SELECT username FROM users');
	$pdo->beginTransaction();
	foreach($matches as $m)
	{
		if($m->registration_time < 3600 * 24 * 5)
		{
			$m->status = 'Old member';
			$m->save();
		}
	}
	$pdo->commit();
```

QueryHelper::raw_exec() is for queries that affect the database, it returns the number of affected rows :

```php

	$count = $finder->raw_exec('DELETE FROM users WHERE last_login < ?', [time() - 365 * 24 * 3600]);
	echo 'Removed '.$count.' inactive users.';
```

### Models

Model is class that represents a single row of a specific table :

```php

	$user = new worm\Model($pdo, 'users');
	$user->name = $_POST['username'];
	$user->pswd = superSecureHash($_POST['password']);
	$user->registration_time = time();
	try
	{
		$user->save();
	}
	catch(PDOException $e)
	{
		echo 'Sorry bro, something went wrong. Why don\'t you try it again later ? Hopefully it will have magically fixed itself by then cause I sure as hell won\'t fix it.';
		log_error($e);
	}
```

The save() method will insert a new row if no id is provided, otherwise it will perform an update :

```php

	$user = new worm\Model($pdo, 'users');
	$user->id = $id;
	$user->name = 'new_username';
	$user->save();

	/* is the equivalent of */

	$query = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
	$query->execute(['new_username', $id]);
```


