<?php

class Graph {

	/*
	 * 0! Структуру для хранения списка смежности графа (не работать с графом через матрицы смежности, если в некоторых
	 * алгоритмах удобнее использовать список ребер - реализовать метод, преобразующий список смежности в список ребер);
	 * 1 Вывести все изолированные вершины орграфа (степени 0).
	 * 2 Вывести список смежности орграфа, являющегося пересечением двух заданных.
	 * 3 Найти связные компоненты графа.
	 * 4 (Прим, Краскал, Борувки) Дан взвешенный неориентированный граф из N вершин и M ребер. Требуется найти в нем каркас минимального веса.
	 * 5 (В графе нет рёбер отрицательного веса) (Дейкстра) Найти вершину, сумма длин кратчайших путей от которой до остальных вершин минимальна.
	 * 6 (В графе нет циклов отрицательного веса) (Форд-Беллман, Флойд) Найти вершину, сумма длин кратчайших путей от которой до остальных вершин минимальна.
	 * 7 (В графе могут быть циклы отрицательного веса.)(Форд-Беллман, Флойд) Найти вершину, сумма длин кратчайших путей от которой до остальных вершин минимальна.
	 * 8 Решить задачу на нахождение максимального потока любым алгоритмом.
	 */

	private $isDirected;
	private $adjList = [];

	/**
	 * Graph constructor.
	 * @param $data
	 * @throws Exception
	 */
	public function __construct($data) {
		$this->isDirected = $data['isDirected'];
		$this->adjList = $data['list'] ?? [];
		$this->selfCheck();
	}

	/**
	 * @throws Exception
	 */
	private function selfCheck() {
		$edgesIndex = [];
		$vertexes = array_keys($this->adjList);
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
					$edgesIndex[] .= $index;
				}
			}
		}
	}

	public function getDataToStore() {
		return ['list' => $this->adjList, 'isDirected' => $this->isDirected];
	}

	private function getEdgeIndex($from, $to, $weight = '') {
		return $from . '/' . $to . '/' . $weight;
	}

	public function getEdgesFromAdjList() {
		$result = [];
		foreach ($this->adjList as $fromVertex => $edge) {
			foreach ($edge as $toVertex => $weight) {
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
		if (!$this->isDirected) {
			$exist = $exist ? $exist : isset($this->adjList[$to][$from]);
		}
		if (!$exist) {
			$this->adjList[$from][$to] = (int) $weight;
		}
		else {
			throw new Exception('Ребро уже существует');
		}
	}

	public function delEdge($from, $to) {
		if (isset($this->adjList[$from][$to])) {
			unset($this->adjList[$from][$to]);
		}
		else {
			throw new Exception('Ребро не существует');
		}
	}

}