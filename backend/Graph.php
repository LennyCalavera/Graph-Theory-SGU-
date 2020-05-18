<?php

class Graph {

	private $isDirected;
	private $adjList = [];

	/**
	 * Graph constructor.
	 * @param $data
	 * @throws Exception
	 */
	public function __construct($data) {
		if (isset($data['isDirected'])) {
			$this->isDirected = $data['isDirected'];
		}
		else {
			throw new Exception('Признак ориентированности графа не определен в файле');
		}
		$this->adjList = $data['list'] ?? [];
		$this->selfCheck();
	}

	/**
	 * @throws Exception
	 */
	private function selfCheck() {
		$edgesIndex = [];
		$requiredEdgesIndex = [];
		$vertexes = array_keys($this->adjList);
		if (count(array_unique($vertexes)) != count($vertexes)) {
			throw new Exception('Повторяющиеся вершины в файле');
		}
		foreach ($this->adjList as $fromVertex => $edge) {
			foreach ($edge as $toVertex => $weight) {
				$index = $this->getEdgeIndex($fromVertex, $toVertex);
				if (in_array($index, $edgesIndex)) {
					throw new Exception('Несколько ребер одного направления между узлами');
				}
				if (!in_array($toVertex, $vertexes)) {
					throw new Exception('Ребро к неизвестной вершине: ' . $toVertex);
				}
				if (!is_numeric($weight)) {
					throw new Exception('Вес должен быть целым числом');
				}
				$edgesIndex[] = $index;
				if (!$this->isDirected) {
					$index = $this->getEdgeIndex($toVertex, $fromVertex);
					if (!in_array($index, $requiredEdgesIndex)) {
						$requiredEdgesIndex[] = $index;
					}
				}
			}
		}
		if (!$this->isDirected) {
			$diff = array_diff($requiredEdgesIndex, $edgesIndex);
			if (!empty($diff)) {
				throw new Exception('Hе описаны обратные ребра для неориентированного графа');
			}
		}
	}

	public function getDataToStore() {
		return ['list' => $this->adjList, 'isDirected' => $this->isDirected];
	}

	private function getEdgeIndex($from, $to) {
		return $from . '/' . $to;
	}

	public function getEdgesFromAdjList() {
		$result = [];
		$edgeIndexes = [];
		foreach ($this->adjList as $fromVertex => $edge) {
			foreach ($edge as $toVertex => $weight) {
				if (!$this->isDirected) {
					$index = $this->getEdgeIndex($fromVertex, $toVertex);
					if (in_array($index, $edgeIndexes)) {
						continue;
					}
					$edgeIndexes[] = $index;
					$edgeIndexes[] = $this->getEdgeIndex($toVertex, $fromVertex);
				}
				$result[] = ['from' => $fromVertex, 'to' => $toVertex, 'weight' => $weight];
			}
		}
		return $result;
	}

	public function getResponseData() {
		return [
			'isDirected' => $this->isDirected,
			'vertexes' => array_keys($this->adjList),
			'edges' => $this->getEdgesFromAdjList()
		];
	}

	public function addVertex($name) {
		if (!isset($this->adjList[$name])){
			$this->adjList[$name] = [];
		}
		else {
			throw new Exception('Вершина уже существует');
		}
	}

	public function delVertex($name) {
		if (isset($this->adjList[$name])) {
			unset($this->adjList[$name]);
			foreach ($this->adjList as $fromVertex => $edge) {
				foreach ($edge as $toVertex => $weight) {
					if ($name === $toVertex) {
						unset($this->adjList[$fromVertex][$toVertex]);
					}
				}
			}
		}
		else {
			throw new Exception('Вершины нет в графе');
		}
	}

	public function addEdge($from, $to, $weight = 0) {
		$exist = isset($this->adjList[$from][$to]);
		if (!$exist) {
			$this->adjList[$from][$to] = (int) $weight;
			if (!$this->isDirected) {
				$this->adjList[$to][$from] = (int) $weight;
			}
		}
		else {
			throw new Exception('Ребро уже существует');
		}
	}

	public function delEdge($from, $to) {
		if (isset($this->adjList[$from][$to])) {
			unset($this->adjList[$from][$to]);
			if (!$this->isDirected) {
				unset($this->adjList[$to][$from]);
			}
		}
		else {
			throw new Exception('Ребро не существует');
		}
	}

//  Вывести все изолированные вершины орграфа (степени 0).
	public function task1() {
		$standAloneList = $this->adjList;
		foreach ($this->adjList as $from => $edge) {
			if (!empty($edge)) {
				unset($standAloneList[$from]);
			}
			foreach ($edge as $to => $weight) {
				unset($standAloneList[$to]);
			}
		}
		$this->adjList = $standAloneList;
	}

//	Вывести список смежности орграфа, являющегося пересечением двух заданных.
	public function task2($graph2Data) {
		if (!$this->isDirected || !$graph2Data['isDirected']) {
			throw new Exception('Оба графа должны быть ориентированными');
		}
		//находим общие вершины
		$intersect = array_intersect_key($graph2Data['list'], $this->adjList);
		//пробегаем по ребрам и удаляем ребра к тем вершинам которых нет в пересечении
		foreach ($intersect as $from => $edge) {
			foreach ($edge as $to => $weight) {
				if (!isset($intersect[$to])) {
					unset($intersect[$from][$to]);
				}
			}
		}
		$this->adjList = $intersect;
	}

//	Найти связные компоненты графа.
	public function task3() {
		$components = $used = [];
		foreach ($this->adjList as $from => $edges) {
			if (!in_array($from, $used)) {
				$component = [];
				$components[] = $this->fillComponentDS($from, $component,$used);
			}
		}
		$answer = "Связные компоненты графа: \n";
		foreach ($components as $id => $component) {
			$answer .= "\n" . $id . ':';
			foreach ($component as $vertex) {
				$answer .= ' ' . $vertex;
			}
		}
		return ['answer' => $answer];
	}

	// поиск в глубину, отмечаем $used, заполняем $component
	public function fillComponentDS($from, $component, &$used) {
		$used[] = $from;
		$component[] = $from;
		foreach ($this->adjList[$from] as $to => $weight) {
			if (!in_array($to, $used)) {
				$component = $this->fillComponentDS($to, $component, $used);
			}
		}
		return $component;
	}

//	(Прим, Краскал, Борувки) Дан взвешенный неориентированный граф из N вершин и M ребер.
//	Требуется найти в нем каркас минимального веса.
	public function task4() {
		return ['answer' => 'ok'];
	}

//	(В графе нет рёбер отрицательного веса) (Дейкстра) Найти вершину, сумма длин
// 	кратчайших путей от которой до остальных вершин минимальна.
	public function task5() {
		return ['answer' => 'ok'];
	}

//	(В графе нет циклов отрицательного веса) (Форд-Беллман, Флойд) Найти вершину,
// 	сумма длин кратчайших путей от которой до остальных вершин минимальна.
	public function task6() {
		return ['answer' => 'ok'];
	}

//	(В графе могут быть циклы отрицательного веса.)(Форд-Беллман, Флойд) Найти вершину,
// 	сумма длин кратчайших путей от которой до остальных вершин минимальна.
	public function task7() {
		return ['answer' => 'ok'];
	}

//	Решить задачу на нахождение максимального потока любым алгоритмом.
	public function task8() {
		return ['answer' => 'ok'];
	}
}