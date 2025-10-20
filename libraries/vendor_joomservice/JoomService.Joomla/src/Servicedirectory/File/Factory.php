<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    4th September, 2022
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JoomService\Joomla\Servicedirectory\File;


use Joomla\DI\Container;
use JoomService\Joomla\Service\Table;
use JoomService\Joomla\Service\Database;
use JoomService\Joomla\Service\Model;
use JoomService\Joomla\Service\Data;
use JoomService\Joomla\Servicedirectory\File\Service\File;
use JoomService\Joomla\Interfaces\FactoryInterface;
use JoomService\Joomla\Abstraction\Factory as ExtendingFactory;


/**
 * File Factory
 * 
 * @since 5.0.2
 */
abstract class Factory extends ExtendingFactory implements FactoryInterface
{
	/**
	 * Package Container
	 *
	 * @var   Container|null
	 * @since 5.0.3
	 **/
	protected static ?Container $container = null;

	/**
	 * Create a container object
	 *
	 * @return  Container
	 * @since 3.2.2
	 */
	protected static function createContainer(): Container
	{
		return (new Container())
			->registerServiceProvider(new Table())
			->registerServiceProvider(new Database())
			->registerServiceProvider(new Model())
			->registerServiceProvider(new Data())
			->registerServiceProvider(new File());
	}
}

