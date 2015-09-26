<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\App {

	/**
	 * Klasa jądra aplikacji
	 */
	class Kernel {

		/**
		 * Obiekt bootstrap
		 * @var \Mmi\App\BootstrapInterface
		 */
		private $_bootstrap;

		/**
		 * Konstruktor
		 */
		public function __construct($bootstrapName = '\Mmi\App\Bootstrap', $env = 'DEV') {
			//ładownie konfiguracji
			$this->_initConfig($env);
			\Mmi\App\Profiler::event('App\Kernel: application startup');
			//inicjalizacja aplikacji
			$this->_initEventHandler()
				->_initPaths()
				->_initEncoding()
				->_initCache();
			\Mmi\App\Profiler::event('App\Kernel: bootstrap startup');
			//tworzenie instancji bootstrapa
			$this->_bootstrap = new $bootstrapName($env);
			\Mmi\App\Profiler::event('App\Kernel: bootstrap done');
			//bootstrap nie implementuje właściwego interfeace'u
			if (!($this->_bootstrap instanceof \Mmi\App\BootstrapInterface)) {
				throw new Exception('\Mmi\App bootstrap should be implementing \Mmi\App\Bootstrap\Interface');
			}
		}

		/**
		 * Uruchomienie aplikacji
		 * @param \Mmi\Bootstrap $bootstrap
		 */
		public function run() {
			$this->_bootstrap->run();
		}

		/**
		 * Ustawia konfigurację
		 * @param string $env
		 * @return \Mmi\App\Kernel
		 */
		protected function _initConfig($env) {
			//konwencja nazwy konfiguracji
			$configClassName = '\App\KernelConfig' . $env;
			//konfiguracja dla danego środowiska
			\App\Registry::$config = new $configClassName();
			//ustawianie konfiguracji loggera
			\Mmi\Logger\LoggerHelper::setConfig(\App\Registry::$config->logger);
			return $this;
		}

		/**
		 * Ustawia kodowanie na UTF-8
		 * @return \Mmi\App\Kernel
		 */
		protected function _initEncoding() {
			//wewnętrzne kodowanie znaków
			mb_internal_encoding('utf-8');
			//domyślne kodowanie znaków PHP
			ini_set('default_charset', 'utf-8');
			//locale
			setlocale(LC_ALL, 'pl_PL.utf-8');
			setlocale(LC_NUMERIC, 'en_US.UTF-8');
			//ustawienie lokalizacji
			date_default_timezone_set(\App\Registry::$config->timeZone);
			ini_set('default_charset', \App\Registry::$config->charset);
			return $this;
		}

		/**
		 * Definicja ścieżek
		 * @param string $systemPath
		 * @return \Mmi\App\Kernel
		 */
		protected function _initPaths() {
			//zasoby publiczne
			define('PUBLIC_PATH', BASE_PATH . '/web');
			//dane
			define('DATA_PATH', BASE_PATH . '/var/data');
			return $this;
		}

		/**
		 * Ustawianie bufora
		 * @return \Mmi\App\Bootstrap
		 */
		protected function _initCache() {
			\App\Registry::$cache = new \Mmi\Cache\Cache(\App\Registry::$config->cache);
			return $this;
		}

		/**
		 * Ustawia handler zdarzeń PHP
		 * @return \Mmi\App\Kernel
		 */
		protected function _initEventHandler() {
			//funkcja  zamknięcia aplikacji
			register_shutdown_function(['\Mmi\App\EventHandler', 'shutdownHandler']);
			//domyślne przechwycenie wyjątków
			set_exception_handler(['\Mmi\App\EventHandler', 'exceptionHandler']);
			//domyślne przechwycenie błędów
			set_error_handler(['\Mmi\App\EventHandler', 'errorHandler']);
			return $this;
		}

	}

}

namespace {

	/**
	 * Globalna funkcja zrzucająca zmienną
	 * @param mixed $var
	 */
	function dump($var) {
		echo '<pre>' . print_r($var, true) . '</pre>';
	}

}