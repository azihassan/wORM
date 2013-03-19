####wORM, a frugal ORM for PHP.

### Intro

wORM is a lightweight ORM that uses PDO's faculties to provide an easy object oriented interface to the database schema. All it needs is the PDO instance and the name of the table :

```php
	$pdo = new PDO('mysql:host=localhost; dbname=database', 'user', 'pswd');
	$finder = new worm\QueryHelper($pdo, 'users');
```

The QueryHelper class offers a set of methods to build SQL queries without having to write SQL code and without manually having to write prepared statements :

```php
	$finder = new worm\QueryHelper($pdo, 'users');
	$users = $finder	->where_op('registration_time', '>', time() - 24 * 3600 * 3)
					->select('id', 'username')
					->order_by('registration_time')
					->find()

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

QueryHelper also provides a set of methods to manually write and execute SQL queries :

```php
	PDOStatement QueryHelper::raw_select(string $sql, [array $args])
	int QueryHelper::raw_exec(string $sql, [array $args])
	(Model[] | bool) QueryHelper::build_models(string $sql, [array $args])
```

QueryHelper::raw_select() is just an abstraction for PDO's prepare() and execute() methods.

```php
It returns a PDOStatement object you can iterate over :

	$matches = $finder->raw_select('SELECT username FROM users WHERE username LIKE ?', ['%h%']);
	foreach($matches as $m)
	{
		/* ... */
	}
```
