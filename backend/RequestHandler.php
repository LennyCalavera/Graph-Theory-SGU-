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
	$graphData = getUserData($uid);
	if (function_exists($action)) {
		/** @var Graph $graph */
		$graph = call_user_func($action, $graphData, $uid);
	}
	else {
		throw new Exception('Неизвестное действие');
	}
	$saveOn = ['addVertex', 'delVertex', 'addEdge', 'delEdge', 'create', 'index'];
	if (in_array($action, $saveOn)) {
		saveUserData($graph->getDataToStore(), $uid);
	}
}

function task8($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task8();
	echo json_encode($answer);
}

function task7($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task7();
	echo json_encode($answer);
}

function task6($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task6();
	echo json_encode($answer);
}

function task5($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task5();
	echo json_encode($answer);
}

function task4($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task4();
	echo json_encode($answer);
}

function task3($graphData) {
	$graph = new Graph($graphData);
	$answer = $graph->task3();
	echo json_encode($answer);
}

function task2($graphData) {
	if (count($_FILES) === 2) {
		foreach ($_FILES as $file) {
			$fileData = json_decode(file_get_contents($file['tmp_name']), true);
			if (!is_array($fileData) || empty($fileData)) {
				throw new Exception('Некорректные данные в файле');
			}
			$graphData[] = $fileData;
		}
	}
	else {
		throw new Exception('Отсутствуют файлы');
	}
	$graph = new Graph($graphData[0]);
	$graph2 = new Graph($graphData[1]);
	$graph->task2($graph2->getDataToStore());
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function task1($graphData) {
	$graph = new Graph($graphData);
	$graph->task1();
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function index($graphData) {
	$graph = new Graph($graphData);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function create($graphData) {
	$graphData = [];
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
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function addVertex($graphData) {
	$graph = new Graph($graphData);
	$name = $_POST['name'];
	$graph->addVertex($name);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function delVertex($graphData) {
	$graph = new Graph($graphData);
	$name = $_POST['name'];
	$graph->delVertex($name);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function addEdge($graphData) {
	$graph = new Graph($graphData);
	$nameStart = $_POST['nameStart'];
	$nameEnd = $_POST['nameEnd'];
	$weight = $_POST['weight'];
	$graph->addEdge($nameStart, $nameEnd, $weight);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
	return $graph;
}

function delEdge($graphData) {
	$graph = new Graph($graphData);
	$nameStart = $_POST['nameStart'];
	$nameEnd = $_POST['nameEnd'];
	$graph->delEdge($nameStart, $nameEnd);
	$formattedData = $graph->getResponseData();
	echo json_encode($formattedData);
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