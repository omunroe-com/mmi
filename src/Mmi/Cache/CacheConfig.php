<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\Cache;

class CacheConfig {

	/**
	 * Buforowanie włączone
	 * @var boolean
	 */
	public $active = true;

	/**
	 * Czas życia bufora
	 * @var integer
	 */
	public $lifetime = 300;
	
	/**
	 * Nazwa backendu obsługującego bufor:
	 * apc | file | memcache
	 * @var string
	 */
	public $handler = 'file';

	/**
	 * Ścieżka dla handlerów plikowych i memcache
	 * @var string
	 */
	public $path = BASE_PATH . '/var/cache';

}