<?php
return array(
	'driver'	=>'pdo_psql',
	'host'		=>getenv('HOST'),
	'port'		=>getenv('DBPORT'),
	'db_name'	=>getenv('DATABASE'),
	'user'		=>getenv('USERNAME'),
	'pwd'		=>getenv('PASSWORD'),
	'charset'	=>'utf8',
	'prefix'	=>null
);
