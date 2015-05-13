<?php
// File: index.php
// Summary: Register all the routes for the REST server

require_once 'database.php';

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/services/:id/:latitude/:longitude', function($id,$latitude,$longitude) use($app) {
	$max_distance = 8.046;
	
	if ($longitude == null || $latitude == null || $id == null) {
		$app->response->setStatus(400);
		echo json_encode(array("responseText" => "400 Bad Request"));
	} else {
		$records = query('SELECT *, ( 6371 * acos( cos( radians(latitude) ) * cos( radians( %f ) ) * '.
				"cos( radians( %f ) - radians(longitude) ) + ".
				"sin( radians(latitude) ) * sin( radians( %f ) ) ) ) as distance ".
				"FROM services ".
				"WHERE type = '%d' ".
				"HAVING distance < ".$max_distance." ".
				'ORDER BY distance ASC LIMIT 1', array($latitude,$longitude,$latitude,$id));
		
		$app->response()->header('Content-Type', 'application/json');
		echo json_encode($records);
	}
});

$app->get('/:table(/:id)', function($table, $id = null) use($app) {
	if ($id == null) {
		$records = query('SELECT * FROM %s', array($table));
	} else {
		$records = query('SELECT * FROM %s WHERE id = %d', array($table, $id));
	}

	$app->response()->header('Content-Type', 'application/json');
	echo json_encode($records);
});

$app->post('/:table', function($table) use($app) {
	$payload = json_decode($app->request()->getBody(), true);
	
	// Since this code pre-build the query string then we need
	// to be connected to the database first
	$connection = connect();
	
	// Extract the column names and values from the payload
	$cols = '';
	$vals = '';
	foreach ($payload as $col => $val) {
		$cols .= $col . ', ';
		
		$val = mysqli_real_escape_string($connection, $val);
		if (!is_numeric($val)) {
			$vals .= "'" . $val . "', ";
		} else {
			$vals .= $val . ', ';
		}
	}
	
	// Trim the last comma and space
	$cols = substr($cols, 0, strlen($cols) - 2);
	$vals = substr($vals, 0, strlen($vals) - 2);

	if (!empty($payload)) {
		// Pre-build the query
		$qs = vsprintf("INSERT INTO %s (%s) VALUES (%s)", array($table, $cols, $vals));
		
		// ... and execute it
		query($qs);
	}

	$app->response()->header('Content-Type', 'application/json');
	echo json_encode(array('success' => true));
});

$app->put('/:table/:id', function($table, $id) use($app) {
	$payload = json_decode($app->request()->getBody(), true);
	
	// Since this code pre-build the query string then we need
	// to be connected to the database first
	$connection = connect();
	
	// Extract the column names and values from the payload
	$set = '';
	foreach ($payload as $col => $val) {
		$val = mysqli_real_escape_string($connection, $val);
		if (!is_numeric($val)) {
			$set .= $col . "='" . $val . "', ";
		} else {
			$set .= $col . '=' . $val . ', ';
		}
	}
	
	$set = substr($set, 0, strlen($set) - 2);

	if (!empty($payload)) {
		// Pre-build the query
		$qs = vsprintf("UPDATE %s SET %s WHERE id=%d", array($table, $set, $id));
		
		// ... and execute it
		query($qs);
	}

	$app->response()->header('Content-Type', 'application/json');
	echo json_encode(array('success' => true));
});

$app->delete('/:table/:id', function($table, $id) use($app) {
	query("DELETE FROM %s WHERE id=%d", array($table, $id));

	$app->response()->header('Content-Type', 'application/json');
	echo json_encode(array('success' => true));
});

$app->run();
