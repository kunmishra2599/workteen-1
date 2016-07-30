<?php
function getConfig($config) {
	return $GLOBALS['config'][$config];
}

function get_db() {
    return new PDO('mysql:host=' . getConfig('host') . ';dbname=' . getConfig('dbname'), getConfig('username'), getConfig('password'));
}

function db_create_table($name, $fields) {
	$sql = "CREATE TABLE IF NOT EXISTS `$name` (";
    $pk  = '';

    foreach($fields as $field => $type) {
      $sql.= "`{$field}` {$type},";
      if (preg_match('/AUTO_INCREMENT/i', $type)) {
        $pk = $field;
      }
    }
    $sql .= ' PRIMARY KEY (`'.$pk.'`)';
    $sql .= ") CHARACTER SET utf8 COLLATE utf8_general_ci";
    if(get_db()->exec($sql) !== false) {
    	return true;
    }
    return false;
}

function db_get_table($tableName) {
	$tables = array(
		'texts' => array(
			'texts' =>  array(
				'id' => 'INT AUTO_INCREMENT',
				'text' => 'varchar(10000)'
			)
		),
		'blogposts' => array(
			'blogposts' => array(
				'id' => 'INT AUTO_INCREMENT',
				'title' => 'INT',
				'date' => 'INT',
				'author' => 'INT',
				'posttext' => 'INT'
			)
		),
		'admins' => array(
			'admins' => array(
				'id' => 'INT AUTO_INCREMENT',
				'admin' => 'varchar(10)',
				'password' => 'varchar(12)'
			)	
		)
	);
	db_create_table($tableName, db_get_table_fields($tables[$tableName]));
	return $tables[$tableName];
}

function db_get_table_name($table) {
	$tableName = array_keys($table)[0];
	return $tableName;
}

function db_get_table_fields($table) {
	return $table[db_get_table_name($table)];

}

function db_insert($table, $values) {
	$db = get_db();
	$questring = "";
	foreach ($values as $value) {
		$questring .= "?,";
	}
	$questring = rtrim($questring, ",");
	$sql = "INSERT INTO `" . db_get_table_name($table) . "` VALUES ('', " . $questring . ")";
	$dbh = $db->prepare($sql);
	print_r($sql);
	$i = 1;
	foreach ($values as $value) {
		$dbh->bindParam($i, $values[$i-1]);
		$i++;
	}
	$dbh->execute();
	return $db->lastInsertId();
}

function db_search($table, $condition, $values) {
	$db = get_db();
	$sql = "SELECT * FROM `" . db_get_table_name($table) . "` " . $condition;
	$dbh = $db->prepare($sql);
	if (count($values) > 0) {
		$i = 1;
		foreach ($values as $value) {
			$dbh->bindParam($i, $values[$i-1]);
			$i++;
		}
	}
	$dbh->execute();
	$outData = array();
	$results = $dbh->fetchAll(PDO::FETCH_ASSOC);
	foreach ($results as $row) {
		$elem = array();
		foreach (db_get_table_fields($table) as $fname => $type) {//fname is field name
			$elem[$fname] = $row[$fname];
		}
		$outData[] = $elem;
	}
	return $outData;
}

function db_delete($table, $id) {
	$sql = "DELETE FROM `" . db_get_table_name($table) . "` WHERE `id`=?";
	$dbh = get_db()->prepare($sql);
	$dbh->bindParam(1, $id);
	$dbh->execute();
}

function db_update($table, $fieldvalues, $condition, $values) {
	/*$fieldvalues = array(
		'field1' => 'value1',
		'field2' => 'value2'
	)*/
	$string = "";
	foreach ($fieldvalues as $field => $value) {
		$string .= "`" . $field . "`=?,";
	}
	$string = rtrim($string,  ",");

	$sql = "UPDATE `" . db_get_table_name($table) . "` SET " . $string . " " . $condition;
	$dbh = get_db()->prepare($sql);
	$i = 1;
	foreach ($fieldvalues as $field => $value) {
		$dbh->bindParam($i, $value);
		$i++;
	}
	foreach ($values as $value) {
		$dbh->bindParam($i, $values[$i-1]);
		$i++;
	}
	$dbh->execute();
}

function db_get_by_id($table, $id) {
	$row = db_search($table, "WHERE `id`=? LIMIT 1", array($id));
	if (count($row) == 1) {
		return $row[0];
	}
	return false;
}

/* data base fucntions on texts table*/

function text_db_insert($value) {
	$table = db_get_table('texts');
	if (count(text_db_search($value)) > 0) {
		return text_db_search($value)[0];
	}
	$id = db_insert($table, array($value));
	return $id;
}

function text_db_get_by_id($id) {
	$table = db_get_table('texts');
	return db_get_by_id($table, $id)['text'];
}

function text_db_search($value) {
	$table = db_get_table('texts');
	$outData = db_search($table, "WHERE `text`=? ", array($value));
	$ids = array();
	foreach ($outData as $row) {
		$ids[] = $row['id'];
	}
	return $ids;
}



function blogpost_db_insert($values) {
	/* $values = array(
		'title' => 'string',
		'date' => 'string',
		'author' => 'string',
		'posttext' => 'string'
	)*/
	$table = db_get_table('blogposts');
	$titleId = text_db_insert($values['title']);
	$dateId = text_db_insert($values['date']);
	$authorId = text_db_insert($values['author']);
	$posttextId = text_db_insert($values['posttext']);
	print_r($titleId);

	$invalues = array(
		$titleId,
		$dateId,
		$authorId,
		$posttextId
	);
	print_r($invalues);

	$id = db_insert($table, $invalues);
	return $id;
}

function blogpost_db_update($values, $id) {
	/* $values = array(
		'title' => 'string',
		'date' => 'string',
		'author' => 'string',
		'posttext' => 'string'
	)*/
	$table = db_get_table('blogposts');
	$in_values = array();
	foreach ($values as $fname => $string) {
		$in_values[$fname] = text_db_insert($string);
	}
	$condition = "WHERE `id`=? ";
	$condvalues = array($id);
	db_update($table, $id_values, $condition, $condvalues);
}

function blogpost_get_by_id($id) {
	$table = db_get_table('blogposts');
	return db_get_by_id($table, $id);
}

function blogpost_get_ids_desc() {
	$table = db_get_table('blogposts');
	$condition = "ORDER BY `id` DESC";
	$values = array();

	$rows = db_search($table, $condition, $values);
	if (count($rows) < 1) {
		return false;
	}
	$ids = array();
	foreach ($rows as $value) {
		$ids[] = $value['id'];
	}
	return $ids;
}

function blogpost_db_delete($id) {
	$table = db_get_table('blogposts');
	db_delete($table, $id);
}

/* admin functions */
function admin_login($admin, $password) {
	$table = db_get_table('admins');
	$condition = "WHERE `admin`=? AND `password`=? LIMIT 1";
	$values = array($admin, $password);
	$result = db_search($table, $condition, $values);
	if (count($result) == 1) {
		return $result[0]['id'];
	}
	return false;
}
?>