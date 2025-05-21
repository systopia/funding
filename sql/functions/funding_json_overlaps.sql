-- Inspired by https://stackoverflow.com/a/48157400
CREATE FUNCTION `FUNDING_JSON_OVERLAPS`(json_doc1 JSON, json_doc2 JSON) RETURNS TINYINT(1)
BEGIN
  DECLARE x INT UNSIGNED;
  DECLARE val JSON;
  SET x = 0;
  IF JSON_LENGTH(json_doc2) < JSON_LENGTH(json_doc1) THEN
    SET val = json_doc2;
    SET json_doc2 = json_doc1;
    SET json_doc1 = val;
  END IF;
  WHILE x < JSON_LENGTH(json_doc1) DO
      SET val = JSON_EXTRACT(json_doc1, CONCAT('$[',x,']'));
      IF JSON_CONTAINS(json_doc2, val) THEN
        RETURN 1;
      END IF;
      SET x = x + 1;
    END WHILE;
  RETURN 0;
END;
