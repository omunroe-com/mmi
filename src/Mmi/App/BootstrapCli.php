<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2016 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\App;

/**
 * Bootstrap aplikacji CMD
 */
class BootstrapCli extends \Mmi\App\Bootstrap {

	/**
	 * Konstruktor, ustawia ścieżki, ładuje domyślne klasy, ustawia autoloadera
	 */
	public function __construct() {
		\App\Registry::$config->session->name = null;
		parent::__construct();
	}

	/**
	 * Uruchamianie bootstrapa - brak front kontrolera
	 */
	public function run() {
		$request = new \Mmi\Http\Request;
		//ustawianie domyślnego języka jeśli istnieje
		if (isset(\App\Registry::$config->languages[0])) {
			$request->lang = \App\Registry::$config->languages[0];
		}
		$request->setModuleName('mmi')
			->setControllerName('index')
			->setActionName('index');
		//ustawianie żądania
		\Mmi\App\FrontController::getInstance()->setRequest($request)
			->getView()->setRequest($request);
	}

}
