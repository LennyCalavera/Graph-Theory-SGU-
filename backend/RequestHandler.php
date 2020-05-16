<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '\backend\Graph.php');

const EXT_JSON = '.json';
const DATA_PATH  = '/storage/';

$headers = getallheaders();
$action = $headers['graphAction'] ?? null;
$uid = $_COOKIE['uid'] ?? null;
try {
	processUserAction($action, $uid);
}
catch (Exception $ex) {
	echo json_encode(['errors' => [$ex->getMessage()]]);
}

/**
 * @param $action
 * @param $uid
 * @throws Exception
 */
function processUserAction($action, $uid) {
	if (!$uid) {
		throw new Exception('Используйте '. $_SERVER['HTTP_HOST'] . ', не удаляйте куки!');
	}
	$graphData = $action !== 'create' ? getUserData($uid) : [];
	if (function_exists($action)) {
		/** @var Graph $graph */
		$graph = call_user_func($action, $graphData);
	}
	else {
		throw new Exception('Неизвестное действие');
	}
	saveUserData($graph->getDataToStore(), $uid);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
}

function index($graphData) {
	$graph = new Graph($graphData);
	return $graph;
}

function create($graphData) {
	$graphSource = $_POST['graphSource'];
	if ($graphSource === 'fromFile') {
		if (isset($_FILES['fileInput'])) {
			$graphData = json_decode(file_get_contents($_FILES['fileInput']['tmp_name']), true);
			if (!is_array($graphData) || empty($graphData)) {
				throw new Exception('Некорректные данные в файле');
			}
		}
		else {
			throw new Exception('Отсутствует файл');
		}
	}
	$graphData['isDirected'] = $graphData['isDirected'] ?? $_POST['isDirected'] ?? false;
	$graph = new Graph($graphData);
	return $graph;
}


function addVertex( $graphData) {
	$graph = new Graph($graphData);
	$name = $_POST['name'];
	$graph->addVertex($name);
	return $graph;
}


function delVertex( $graphData) {
	$graph = new Graph($graphData);
	$name = $_POST['name'];
	$graph->delVertex($name);
	return $graph;
}

function addEdge( $graphData) {
	$graph = new Graph($graphData);
	$nameStart = $_POST['nameStart'];
	$nameEnd = $_POST['nameEnd'];
	$weight = $_POST['weight'];
	$graph->addEdge($nameStart, $nameEnd, $weight);
	return $graph;
}

function delEdge( $graphData) {
	$graph = new Graph($graphData);
	$nameStart = $_POST['nameStart'];
	$nameEnd = $_POST['nameEnd'];
	$graph->delEdge($nameStart, $nameEnd);
	return $graph;
}

function getUserData($uid) {
	$data = [];
	$filePath = getFilePath($uid);
	if (file_exists($filePath)) {
		$data = json_decode(file_get_contents($filePath), true);
	}
	return $data;
}

/**
 * @param $newGraphData
 * @param $uid
 */
function saveUserData($newGraphData, $uid) {
	$filePath = getFilePath($uid);
	file_put_contents($filePath, json_encode($newGraphData));
}

/**
 * @param $uid
 * @return string
 */
function getFilePath($uid) {
	return $_SERVER['DOCUMENT_ROOT'] . DATA_PATH . $uid . EXT_JSON;
}

