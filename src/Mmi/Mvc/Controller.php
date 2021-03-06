<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2017 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\Mvc;

use Mmi\App\FrontController;

/**
 * Klasa kontrolera akcji
 */
class Controller
{

    /**
     * Żądanie
     * @var \Mmi\Http\Request
     */
    protected $_request;

    /**
     * Referencja do odpowiedzi z Front controllera
     * @var \Mmi\Http\Response
     */
    protected $_response;

    /**
     * Widok
     * @var \Mmi\Mvc\View
     */
    public $view;

    /**
     * Konstruktor
     */
    public function __construct(\Mmi\Http\Request $request)
    {
        //request
        $this->_request = $request;
        //response
        $this->_response = \Mmi\App\FrontController::getInstance()->getResponse();
        //inicjalizacja domyślna
        $this->_init();
        //inicjacja programisty kontrolera
        $this->init();
    }

    /**
     * Magiczne pobranie zmiennej z requestu
     * @param string $name nazwa zmiennej
     */
    public final function __get($name)
    {
        //pobiera zmienną z requestu po nazwie zmiennej
        return $this->_request->__get($name);
    }

    /**
     * Magiczne sprawczenie istnienia pola w request
     * @param string $key klucz
     * @return bool
     */
    public function __isset($key)
    {
        //sprawdzenie istenia zmiennej w requescie
        return $this->_request->__isset($key);
    }

    /**
     * Magiczne pobranie zmiennej z requestu
     * @param string $name nazwa zmiennej
     * @param mixed $value wartość
     */
    public final function __set($name, $value)
    {
        //ustawienie zmiennej w requescie
        return $this->_request->__set($name, $value);
    }

    /**
     * Magiczne usunięcie zmiennej z requestu
     * @param string $name nazwa zmiennej
     */
    public final function __unset($name)
    {
        //usunięcie zmiennej z requestu
        return $this->_request->__unset($name);
    }

    /**
     * Funkcja dla użytkownika ładowana na końcu konstruktora
     */
    public function init()
    {
        
    }

    /**
     * Pobiera request
     * @return \Mmi\Http\Request
     */
    public final function getRequest()
    {
        return $this->_request;
    }

    /**
     * Zwraca dane post z requesta
     * @return \Mmi\Http\RequestPost
     */
    public final function getPost()
    {
        return $this->getRequest()->getPost();
    }

    /**
     * Zwraca pliki z requesta
     */
    public final function getFiles()
    {
        return $this->getRequest()->getFiles();
    }

    /**
     * Pobiera response
     * @return \Mmi\Http\Response
     */
    public final function getResponse()
    {
        return $this->_response;
    }

    /**
     * Pobiera helper messengera
     * @return \Mmi\Messenger\Messenger
     */
    public final function getMessenger()
    {
        return \Mmi\Message\MessengerHelper::getMessenger();
    }

    /**
     * Pobiera helper akcji
     * @return \Mmi\Mvc\ActionHelper
     */
    public final function getActionHelper()
    {
        return ActionHelper::getInstance();
    }

    /**
     * Pobiera helper logowania
     * @return \Psr\Log\LoggerInterface
     */
    public final function getLogger()
    {
        return FrontController::getInstance()->getLogger();
    }

    /**
     * Pobiera profiler
     * @return \Mmi\App\KernelProfiler
     */
    public final function getProfiler()
    {
        return FrontController::getInstance()->getProfiler();
    }

    /**
     * Konfiguruje kontroler akcji
     */
    private function _init()
    {
        //przypięcie widoku
        $this->view = FrontController::getInstance()->getView();

        //inicjalizacja tłumaczeń
        $this->_initTranslaction($this->_request->__get('module'), $this->_request->__get('lang'));
    }

    /**
     * Inicjalizacja tłumaczeń
     * @param string $module nazwa modułu
     * @param string $lang język
     * @return mixed wartość
     */
    private function _initTranslaction($module, $lang)
    {
        //pobranie struktury translatora
        $structure = FrontController::getInstance()->getStructure('translate');
        //brak tłumaczenia w strukturze
        if (!isset($structure[$module][$lang])) {
            return;
        }
        //brak tłumaczenia, lub domyślny język
        if ($lang === null || $lang == $this->view->getTranslate()->getDefaultLocale()) {
            return;
        }
        //ładowanie zbuforowanego translatora
        $cache = $this->view->getCache();
        //klucz buforowania
        $key = 'mmi-translate-' . $lang . '-' . '-' . $module;
        //próba załadowania z bufora
        if ($cache !== null && (null !== ($cachedTranslate = $cache->load($key)))) {
            //wstrzyknięcie zbuforowanego translatora do widoku
            $this->view->setTranslate($cachedTranslate->setLocale($lang));
            return FrontController::getProfiler()->event('Mvc\Controller: translate cache [' . $lang . '] ' . $module);
        }
        //dodawanie tłumaczeń do translatora
        $this->view->getTranslate()->addTranslation(is_array($structure[$module][$lang]) ? $structure[$module][$lang][0] : $structure[$module][$lang], $lang)
            ->setLocale($lang);
        //zapis do cache
        if ($cache !== null) {
            $cache->save($this->view->getTranslate(), $key, 0);
        }
        //event profilera
        FrontController::getProfiler()->event('Mvc\Controller: translate cache [' . $lang . '] ' . $module);
    }

}
