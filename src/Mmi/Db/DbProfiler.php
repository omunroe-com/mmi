<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\Db;

/**
 * Klasa profilera aplikacji
 */
class DbProfiler {

	/**
	 * Dane profilera
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Rejestruje zdarzeni
	 * @param PDOStatement $statement
	 * @param array $bind
	 * @param float $elapsed
	 */
	public function event(\PDOStatement $statement, array $bind, $elapsed = null) {
		//zapytanie SQL bez bindów
		$sql = $statement->queryString;
		//ustalanie kluczy i wartości
		$keys = array_keys($bind);
		$values = array_values($bind);
		array_walk($values, function (&$v) {
			$v = '\'' . $v . '\'';
		});
		//iteracja po kluczach
		foreach ($keys as $key => $value) {
			//zamiana kluczy 
			if (is_int($value)) {
				$sql = preg_replace('/\?/', $values[$key], $sql, 1);
				continue;
			}
			$sql = str_replace(':' . trim($value, ':'), $values[$key], $sql);
		}
		//zapis rekordu
		$this->_data[] = [
			'sql' => $sql,
			'elapsed' => $elapsed,
		];
		return $this;
	}

	/**
	 * Pobiera dane z profilera
	 * @return array
	 */
	public function get() {
		$elapsed = $this->elapsed();
		//iteracja po danych
		foreach ($this->_data as $key => $item) {
			if ($elapsed == 0) {
				$this->_data[$key]['percent'] = 0;
				continue;
			}
			$this->_data[$key]['percent'] = 100 * $item['elapsed'] / $elapsed;
		}
		return $this->_data;
	}

	/**
	 * Zwraca ilość zapytań w profilerze
	 * @return int
	 */
	public function count() {
		return count($this->_data);
	}

	/**
	 * Pobiera sumaryczny czas wszystkich zapytań
	 * @return float
	 */
	public function elapsed() {
		$elapsed = 0;
		foreach ($this->_data as $record) {
			$elapsed += $record['elapsed'];
		}
		return $elapsed;
	}

}
