DROP VIEW IF EXISTS view_content_search;
CREATE VIEW view_content_search AS
-- search into category
SELECT
	1 AS result_strength,
	ART.aid,
	ART.owner_id AS uid,
	ART.catid,
	ART.request_uri,
	ART.title AS article_title,
	CAT.name AS content,
	ART.cre_date,
	ART.publish
FROM article_category AS CAT
	INNER JOIN article AS ART ON CAT.aid = ART.aid
UNION
SELECT
	2 AS result_strength,
	ART.aid,
	ART.owner_id AS uid,
	ART.catid,
	ART.request_uri,
	ART.title AS article_title,
	CAT.label AS content,
	ART.cre_date,
	ART.publish
FROM article_category AS CAT
	INNER JOIN article AS ART ON CAT.aid = ART.aid
UNION
-- search into article
SELECT
	3 AS result_strength,
	aid,
	owner_id AS uid,
	catid,
	request_uri,
	title AS article_title,
	title AS content,
	cre_date,
	publish
FROM article
UNION
SELECT
	4 AS result_strength,
	aid,
	owner_id AS uid,
	catid,
	request_uri,
	title AS article_title,
	fnStripTags(CONCAT(body, ' ', greeting_text, ' ', signature)) AS content,
	cre_date,
	publish
FROM article
UNION
SELECT
	5 AS result_strength,
	aid,
	owner_id AS uid,
	catid,
	request_uri,
	title AS article_title,
	summary AS content,
	cre_date,
	publish
FROM article
-- search into article custom fields
UNION
SELECT
	6 AS result_strength,
	ART.aid,
	ART.owner_id AS uid,
	ART.catid,
	ART.request_uri,
	ART.title AS article_title,
	CASE WHEN CUST.input_type IN ('text', 'html') THEN
		fnStripTags(CONCAT(CUST.field_name, ' ', CUST.field_value))
	ELSE	
		fnStripTags(CUST.field_name)
	END AS content,
	ART.cre_date,
	ART.publish
FROM article_custom_fields AS CUST
	INNER JOIN article AS ART ON ART.aid = CUST.aid
-- search into article comments
UNION
SELECT
	7 AS result_strength,
	ART.aid,
	ART.owner_id AS uid,
	ART.catid,
	ART.request_uri,
	ART.title AS article_title,
	fnStripTags(COM.message) AS content,
	ART.cre_date,
	ART.publish
FROM article_comments AS COM
	INNER JOIN article AS ART ON ART.aid = COM.aid;
