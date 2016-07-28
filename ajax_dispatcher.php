<?php
include 'core/init.php';

$functions = array('blogpost_get_by_id', 'blogpost_db_update', 'blogpost_db_insert', 'blogpost_get_ids_desc', 'blogpost_db_delete');

if (isset($_GET['function'])) {
	if (in_array($_GET['function'], $functions)) {
		$function = $_GET['function'];
		if (isset($_GET['data'])) {
			$data = explode("/-/", $_GET['data']);

			if ($function == "blogpost_get_by_id") {
				$recieved = blogpost_get_by_id($data[0]);
				if ($recieved === false) {
					echo "{\"status\": 0}";
				} else {
					echo '{"status": 1, "title": "' . text_db_get_by_id($recieved['title']) . '", "date": "' . text_db_get_by_id($recieved['date']) . '", "author": "' . text_db_get_by_id($recieved['author']) . '", "posttext": "' . text_db_get_by_id($recieved['posttext']) . '"}';
				}
			}

			if ($function == 'blogpost_db_update') {
				$values = array(
					'title' => htmlentities($data[0]),
					'date' => htmlentities($data[1]),
					'author' => htmlentities($data[2]),
					'posttext' => htmlentities($data[3])
				);
				$id = $data[4];
				blogpost_db_update($values, $id);
			}

			if ($function == 'blogpost_db_insert') {
				$values = array(
					'title' => htmlentities($data[0]),
					'date' => htmlentities($data[1]),
					'author' => htmlentities($data[2]),
					'posttext' => htmlentities($data[3])
				);
				blogpost_db_insert($values);
			}

			if ($function == 'blogpost_get_ids_desc') {
				$ids = blogpost_get_ids_desc();
				if ($ids === false) {
					echo "{\"status\": 0}";
				} else {
					$returnText = '{"ids" : [';
					foreach ($ids as $id) {
						$returnText .= '{"id": ' . $id . '},';
					}
					$returnText = rtrim($returnText,  ",");
					$returnText .= ']}';
					echo $returnText;
				}
			}


			if ($function == 'blogpost_db_delete') {
				blogpost_db_delete($data[0]);
			}
		}
	}
}
?>