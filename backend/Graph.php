<?php

class Graph {

	private $isDirected = false;
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
		else if (!empty($data)) {
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

	private function getEdgesFromAdjList() {
		$result = [];
		foreach ($this->adjList as $fromVertex => $edge) {
			foreach ($edge as $toVertex => $weight) {
				$result[] = ['from' => $fromVertex, 'to' => $toVertex, 'weight' => $weight];
			}
		}
		return $result;
	}

	//если граф неориентированый то у между всеми связанными вершинами удаляется по ребру 1 направления
	//требуется для визуальной части
	public function prettifyEdges($edgesList) {
		$pretty = $edgeIndexes = [];
		foreach ($edgesList as $edge) {
			$index = $this->getEdgeIndex($edge['from'], $edge['to']);
			if (in_array($index, $edgeIndexes)) {
				continue;
			}
			$edgeIndexes[] = $index;
			$edgeIndexes[] = $this->getEdgeIndex($edge['to'], $edge['from']);
			$pretty[] = $edge;
		}
		return $pretty;
	}

	public function getResponseData() {
		$edges = $this->getEdgesFromAdjList();
		if (!$this->isDirected) {
			$edges = $this->prettifyEdges($edges);
		}
		return ['isDirected' => $this->isDirected, 'vertexes' => array_keys($this->adjList), 'edges' => $edges];
	}

	public function addVertex($name) {
		if (!isset($this->adjList[$name])) {
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
			$this->adjList[$from][$to] = (int)$weight;
			if (!$this->isDirected) {
				$this->adjList[$to][$from] = (int)$weight;
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
				$components[] = $this->fillComponentDS($from, $component, $used);
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
	private function fillComponentDS($from, $component, &$used) {
		$used[] = $from;
		$component[] = $from;
		foreach ($this->adjList[$from] as $to => $weight) {
			if (!in_array($to, $used)) {
				$component = $this->fillComponentDS($to, $component, $used);
			}
		}
		return $component;
	}

	/**
	 * @return Closure
	 */
	private function getSortFn() {
		return function ($a, $b) {
			if ($a['weight'] == $b['weight']) {
				return 0;
			}
			return ($a['weight'] < $b['weight']) ? -1 : 1;
		};
	}

//	(Прим, Краскал, Борувки) Дан взвешенный неориентированный граф из N вершин и M ребер.
//	Требуется найти в нем каркас минимального веса.
// 	Выбран алгоритм Крускала
	public function task4() {
		if ($this->isDirected) {
			throw new Exception('Граф ориентирован');
		}
		$result = [];
		$cost = 0; //сумма весов
		$edgeList = $this->getEdgesFromAdjList(); //формируем список ребер из списка смежности
		usort($edgeList, $this->getSortFn()); //сортируем по весам ребра
		$treeId = [];
		$vertexes = array_keys($this->adjList);
		for ($i = 0; $i < count($vertexes); $i++) {
			$treeId[$vertexes[$i]] = $i; //пишем каждой вершине свой ид
		}
		foreach ($edgeList as $edge) {
			$from = $edge['from'];
			$to = $edge['to'];
			$weight = $edge['weight'];
			if ($treeId[$from] !== $treeId[$to]) {
				$cost += $weight;
				$result[] = ['from' => $from, 'to' => $to, 'weight' => $weight];
				$oldId = $treeId[$to];
				$newId = $treeId[$from];
				$treeId[$to] = $treeId[$from];
				foreach ($treeId as $vertex => $id) {
					if ($id === $oldId) {
						$treeId[$vertex] = $newId;
					}
				}
			}
		}
		if (!$this->isDirected) {
			$result = $this->prettifyEdges($result);
		}
		return ['isDirected' => $this->isDirected, 'vertexes' => array_keys($this->adjList), 'edges' => $result];
	}

//	(В графе нет рёбер отрицательного веса) (Дейкстра) Найти вершину, сумма длин
// 	кратчайших путей от которой до остальных вершин минимальна.
	public function task5() {
		$minCosts = [];
		foreach ($this->adjList as $vertex => $edges) {
			$minCosts[$vertex] = 0;
			$dist = $this->dejkstra($vertex);
			foreach ($dist as $vertexDist) {
				$minCosts[$vertex] += $vertexDist;
			}
		}
		$answer = 'Ответ: ' . array_keys($minCosts, min($minCosts))[0];
		return ['answer' => $answer];
	}

	private function dejkstra($start) {
		$inf = PHP_INT_MAX;
		$dist = $used = [];
		foreach ($this->adjList as $vertex => $edges) {
			$dist[$vertex] = $inf;
			$used[$vertex] = false;
		}
		$dist[$start] = $min_dist = $result = 0;
		while ($min_dist < $inf) {
			$i = $start;
			$used[$i] = true;
			foreach ($this->adjList[$i] as $to => $weight) {
				if ($dist[$i] + $weight < $dist[$to]) {
					$dist[$to] = $dist[$i] + $weight;
				}
			}
			$min_dist = $inf;
			foreach ($this->adjList as $vertex => $edges) {
				if (!$used[$vertex] && $dist[$vertex] < $min_dist) {
					$min_dist = $dist[$vertex];
					$start = $vertex;
				}
			}
		}
		return $dist;
	}

//	(В графе нет циклов отрицательного веса) (Форд-Беллман, Флойд) Найти вершину,
// 	сумма длин кратчайших путей от которой до остальных вершин минимальна.
	public function task6() {
		$minCosts = [];
		foreach ($this->adjList as $vertex => $edges) {
			$minCosts[$vertex] = 0;
			$dist = $this->fordBellman($vertex);
			foreach ($dist as $vertexDist) {
				$minCosts[$vertex] += $vertexDist;
			}
		}
		$answer = 'Ответ: ' . array_keys($minCosts, min($minCosts))[0];
		return ['answer' => $answer];
	}

	public function fordBellman($start) {
		$inf = 1000000000;
		$edgeList = $this->getEdgesFromAdjList();
		$dist = [];
		foreach ($this->adjList as $vertex => $edges) {
			$dist[$vertex] = $inf;
		}
		$dist[$start] = 0;
		for ($i = 1; $i < count($dist); ++$i) {
			foreach ($edgeList as $edge) {
				if ($dist[$edge['from']] + $edge['weight'] < $dist[$edge['to']]) {
					$dist[$edge['to']] = $dist[$edge['from']] + $edge['weight'];
				}
			}
		}
		foreach ($edgeList as $edge) {
			if ($dist[$edge['from']] + $edge['weight'] < $dist[$edge['to']]) {
				throw new \Exception('Есть цикл отрицательного веса!');
			}
		}
		return $dist;
	}

//	(В графе могут быть циклы отрицательного веса.)(Форд-Беллман, Флойд) Найти вершину,
// 	сумма длин кратчайших путей от которой до остальных вершин минимальна.
//  проверка на отрицательные циклы уже реализована в прошлом задании
	public function task7() {
		return $this->task6();
	}

//	Решить задачу на нахождение максимального потока любым алгоритмом.
// выбран алгоритм Форда-Фалкерсона
	public function task8() {
		return ['answer' => 'ok'];
	}
}