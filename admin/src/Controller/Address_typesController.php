<?php
/**
 * @package    Service Directory
 *
 * @created    15th October, 2025
 * @author     Lemuel van der Merwe <https://github.com/joomengine/Joomla-Service-Directory>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * A professional directory component for listing and showcasing service providers.
 */
namespace JoomService\Component\Servicedirectory\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Address_types Admin Controller
 *
 * @since  1.6
 */
class Address_typesController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SERVICEDIRECTORY_ADDRESS_TYPES';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Address_type', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}