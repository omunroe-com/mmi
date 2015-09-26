<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\Mvc;

class ActionHelper {

	/**
	 * Obiekt ACL
	 * @var \Mmi\Security\Acl
	 */
	protected $_acl;

	/**
	 * Obiekt Auth
	 * @var \Mmi\Security\Auth
	 */
	protected $_auth;
	
	/**
	 * Instancja helpera akcji
	 * @var \Mmi\Mvc\ActionHelper 
	 */
	protected static $_instance;

	/**
	 * Pobranie instancji
	 * @return \Mmi\Mvc\ActionHelper
	 */
	public static function getInstance() {
		//jeśli nie istnieje instancja tworzenie nowej
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Ustawia obiekt ACL
	 * @param \Mmi\Security\Acl $acl
	 * @return \Mmi\Security\Acl
	 */
	public function setAcl(\Mmi\Security\Acl $acl) {
		$this->_acl = $acl;
		return $this;
	}

	/**
	 * Ustawia obiekt autoryzacji
	 * @param \Mmi\Security\Auth $auth
	 * @return \Mmi\Security\Auth
	 */
	public function setAuth(\Mmi\Security\Auth $auth) {
		$this->_auth = $auth;
		return $this;
	}

	/**
	 * Uruchamia akcję z kontrolera
	 * @param array $params parametry
	 * @return mixed
	 */
	public function action(array $params = []) {
		$frontRequest = \Mmi\App\FrontController::getInstance()->getRequest();
		$controllerRequest = new \Mmi\Http\Request(array_merge($frontRequest->toArray(), $params));
		$actionLabel = $controllerRequest->getModuleName() . ':' . $controllerRequest->getControllerName() . ':' . $controllerRequest->getActionName();
		//sprawdzenie ACL
		if (!$this->_checkAcl($controllerRequest->getModuleName(), $controllerRequest->getControllerName(), $controllerRequest->getActionName())) {
			\Mmi\App\Profiler::event('Mvc\ActionExecuter: ' . $actionLabel . ' blocked');
			return;
		}
		//wywołanie akcji
		$actionContent = $this->_invokeAction($controllerRequest, $actionLabel);
		\Mmi\App\Profiler::event('Mvc\ActionExecuter: ' . $actionLabel . ' done');
		//jeśli akcja zwraca cokolwiek, automatycznie jest to content
		if ($actionContent !== null) {
			\Mmi\App\FrontController::getInstance()->getView()
				->setLayoutDisabled()
				->setRequest($frontRequest);
			return $actionContent;
		}
		//rendering szablonu jeśli akcja zwraca null
		$content = \Mmi\App\FrontController::getInstance()->getView()->renderTemplate($controllerRequest->getModuleName(), $controllerRequest->getControllerName(), $controllerRequest->getActionName());
		//przywrócenie do widoku request'a z front controllera
		\Mmi\App\FrontController::getInstance()->getView()->setRequest($frontRequest);
		return $content;
	}
	
	/**
	 * Wykonuje akcję
	 * @param \Mmi\Http\Request $request
	 * @param string $actionLabel
	 * @return string
	 * @throws \Mmi\Mvc\Exception
	 */
	protected function _invokeAction(\Mmi\Http\Request $request, $actionLabel) {
		$structure = \Mmi\App\FrontController::getInstance()->getStructure('module');
		//brak w strukturze
		if (!isset($structure[$request->getModuleName()][$request->getControllerName()][$request->getActionName()])) {
			throw new NotFoundException('Component not found: ' . $actionLabel);
		}
		//ustawienie requestu w widoku
		\Mmi\App\FrontController::getInstance()->getView()->setRequest($request);
		//powołanie kontrolera
		$controllerParts = explode('-', $request->getControllerName());
		foreach ($controllerParts as $key => $controllerPart) {
			$controllerParts[$key] = ucfirst($controllerPart);
		}
		$controllerClassName = ucfirst($request->getModuleName()) . '\\' . implode('\\', $controllerParts) . 'Controller';
		$actionMethodName = $request->getActionName() . 'Action';
		$controller = new $controllerClassName($request);
		//wywołanie akcji
		return $controller->$actionMethodName();
	}

	/**
	 * Sprawdza uprawnienie do widgetu
	 * @param string $module moduł
	 * @param string $controller kontroler
	 * @param string $action akcja
	 * @return boolean
	 */
	protected function _checkAcl($module, $controller, $action) {
		//jeśli brak - dozwolone
		if ($this->_acl === null || $this->_auth === null) {
			return true;
		}
		return $this->_acl->isAllowed($this->_auth->getRoles(), $module . ':' . $controller . ':' . $action);
	}

}