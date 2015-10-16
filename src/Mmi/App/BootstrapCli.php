<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\App;

/**
 * Bootstrap aplikacji CMD
 */
class BootstrapCli extends \Mmi\App\Bootstrap {

	/**
	 * Uruchamianie bootstrapa - brak front kontrolera
	 */
	public function run() {
		$front = \Mmi\App\FrontController::getInstance();
		$request = new \Mmi\Http\Request;
		//ustawianie domyślnego języka jeśli istnieje
		if (isset(\App\Registry::$config->languages[0])) {
			$request->lang = \App\Registry::$config->languages[0];
		}
		$request->setModuleName('mmi')
			->setControllerName('index')
			->setActionName('index');
		//ustawianie żądania
		$front->setRequest($request);
		\Mmi\App\FrontController::getInstance()->getView()->setRequest($request);
	}

}
