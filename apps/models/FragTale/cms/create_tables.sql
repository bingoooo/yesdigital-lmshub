SET foreign_key_checks = 0; 
DROP TABLE IF EXISTS
  user,role,user_roles,files,article_category,article,article_history,article_comments,article_file_type,
  article_files,parameters,message,ads,user_files,article_custom_fields,reaction_type,article_files_user_reactions,
  article_users;
CREATE TABLE user(
	uid INT PRIMARY KEY AUTO_INCREMENT,
	active TINYINT NOT NULL DEFAULT 1,
	login VARCHAR(50) UNIQUE NOT NULL,
	email VARCHAR(100) UNIQUE NOT NULL,
	password VARCHAR(32) NOT NULL, -- MD5
	firstname VARCHAR(50),
	lastname VARCHAR(50),
	bir_date DATETIME, -- birthday
	phone VARCHAR(25),
	address VARCHAR(255),
	zip_code VARCHAR(10),
	city VARCHAR(128),
	region VARCHAR(128),
	state VARCHAR(128),
	country VARCHAR(128),
	cre_uid INT NULL,
	upd_uid INT NULL,
	cre_date DATETIME NOT NULL,
	upd_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (cre_uid) REFERENCES user(uid) ON DELETE SET NULL,
FOREIGN KEY (upd_uid) REFERENCES user(uid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE role(
	rid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL UNIQUE,
	summary VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE user_roles(
	uid INT NOT NULL,
	rid INT NOT NULL,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE CASCADE,
FOREIGN KEY (rid) REFERENCES role(rid) ON DELETE CASCADE,
PRIMARY KEY(uid, rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE files(
	fid INT PRIMARY KEY AUTO_INCREMENT,
	path VARCHAR(255) NOT NULL UNIQUE,
	filename VARCHAR(128) NOT NULL,
	mime_type VARCHAR(25) NOT NULL,
	size int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_category(
	catid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128) NOT NULL UNIQUE,
	label VARCHAR(128) NOT NULL,
	fid INT NULL,
	parent_catid INT NULL,
	cre_uid INT NOT NULL,
	upd_uid INT NOT NULL,
	cre_date DATETIME NOT NULL,
	upd_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (parent_catid) REFERENCES article_category(catid),
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE SET NULL,
FOREIGN KEY (cre_uid) REFERENCES user(uid),
FOREIGN KEY (upd_uid) REFERENCES user(uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article(
	aid INT PRIMARY KEY AUTO_INCREMENT,
	uid INT NULL,
	owner_id INT NOT NULL,
	catid INT NULL,
	view VARCHAR(255) NOT NULL DEFAULT 'default', -- Must point to a specific view placed in the application
	access SMALLINT, -- degre of accessibility: 1=Only for super-admin, 2=For administrators
	request_uri VARCHAR(255) NOT NULL UNIQUE,
	fid INT NULL,
	title VARCHAR(128),
	summary VARCHAR(255),
	body TEXT,
	greeting_text VARCHAR(200),
	signature VARCHAR(100), -- if null, get the author
	publish TINYINT DEFAULT 0,
	position INT,
	edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	cre_date TIMESTAMP NOT NULL,
FOREIGN KEY (owner_id) REFERENCES user(uid),
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE SET NULL,
FOREIGN KEY (catid) REFERENCES article_category(catid) ON DELETE SET NULL,
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE article_category ADD aid INT NULL UNIQUE AFTER catid;
ALTER TABLE article_category ADD CONSTRAINT FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE SET NULL;

CREATE TABLE article_history(
	ahid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	uid INT NOT NULL,
	catid INT,
	view VARCHAR(255) NOT NULL DEFAULT 'default', -- Must point to a specific view placed in the application
	access SMALLINT, -- degre of accessibility: 1=Only for super-admin, 2=For administrators
	request_uri VARCHAR(255),
	title VARCHAR(128),
	summary VARCHAR(255),
	body TEXT,
	greeting_text VARCHAR(200),
	signature VARCHAR(100), -- if null, get the author
	edit_date TIMESTAMP,
FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE CASCADE,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE article_comments(
	acid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	uid INT NULL,
	message VARCHAR(510) NOT NULL,
	blocked TINYINT(1) NULL,
	edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE CASCADE,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE article_file_type(
	aftid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_files(
	afid INT PRIMARY KEY AUTO_INCREMENT,
	aid INT NOT NULL,
	fid INT NOT NULL,
	uid INT NULL,
	aftid INT NULL,
	width INT NULL,
	height INT NULL,
FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE CASCADE,
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE CASCADE,
FOREIGN KEY (aftid) REFERENCES article_file_type(aftid) ON DELETE SET NULL,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_custom_fields(
	aid INT NOT NULL,
	field_key VARCHAR(128) NOT NULL,
	field_name VARCHAR(510),
	input_type VARCHAR(45),
	field_value TEXT,
	position INT,
FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE CASCADE,
PRIMARY KEY (aid, field_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE parameters(
	param_key VARCHAR(255) PRIMARY KEY,
	param_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE message(
	mid INT PRIMARY KEY AUTO_INCREMENT,
	send_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	sender_id INT NOT NULL,
	recipient_id INT NOT NULL,
	body TEXT,
	opened TINYINT,
FOREIGN KEY (sender_id) REFERENCES user(uid) ON DELETE CASCADE,
FOREIGN KEY (recipient_id) REFERENCES user(uid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*
CREATE TABLE user_file_type(
	uftid INT PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128) NOT NULL UNIQUE,
	description VARCHAR(510)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE user_files(
	uid INT NOT NULL,
	fid INT NOT NULL,
	uftid INT,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE CASCADE,
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE CASCADE,
FOREIGN KEY (uftid) REFERENCES user_file_type(uftid) ON DELETE CASCADE,
PRIMARY KEY (uid, fid, uftid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

CREATE TABLE user_files(
	uid INT NOT NULL,
	fid INT NOT NULL,
	is_profile TINYINT(1),
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE CASCADE,
FOREIGN KEY (fid) REFERENCES files(fid) ON DELETE CASCADE,
PRIMARY KEY (uid, fid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE reaction_type(
	reacid INT PRIMARY KEY AUTO_INCREMENT,
	reacname VARCHAR(255) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_files_user_reactions(
	afuid INT PRIMARY KEY AUTO_INCREMENT,
	afid INT NOT NULL,
	uid INT NULL,
	reacid INT NOT NULL,
	content VARCHAR(2040),
	date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (afid) REFERENCES article_files(afid) ON DELETE CASCADE,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE SET NULL,
FOREIGN KEY (reacid) REFERENCES reaction_type(reacid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE article_users(
	aid INT NOT NULL,
	uid INT NOT NULL,
	type VARCHAR(45),
FOREIGN KEY (aid) REFERENCES article(aid) ON DELETE CASCADE,
FOREIGN KEY (uid) REFERENCES user(uid) ON DELETE CASCADE,
PRIMARY KEY (aid, uid, type)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;
-- INSERTS

-- users
INSERT INTO `user`(uid,active,login,email,password,cre_uid,upd_uid,cre_date,upd_date)
VALUES
(1,1,'SuperAdmin','admin@bandofnuts.fr','c7f86e8cf8f01309853411133e764fe9',1, 1, NOW(),NOW());
-- Password is "bon"

-- Roles
INSERT INTO role(name, summary) VALUES
('super-admin', 'The owner. Most powerful role.'), 
('admin', 'Common administrator.'),
('frontend-user', 'Common register user'),
('public', 'Any registered user with lower access.');

-- User roles
INSERT INTO user_roles(uid, rid) VALUES (1, 1);

-- article file types
INSERT INTO article_file_type(name) VALUES('logo'), ('vignette'), ('image'), ('video'), ('downloadable'), ('any');

INSERT INTO reaction_type(reacname) VALUES('Picture comments'),('Picture rate');