<?php
return array(
	'driver'	=>'psql',
	'host'		=>getenv('HOST'),
	'port'		=>getenv('PORT'),
	'db_name'	=>getenv('DATABASE'),
	'user'		=>getenv('USER'),
	'pwd'		=>getenv('PASSWORD'),
	'charset'	=>'utf8',
	'prefix'	=>null
);
