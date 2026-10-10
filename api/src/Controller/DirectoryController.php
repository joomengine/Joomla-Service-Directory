<?php
/**
 * @package    Service Directory
 *
 * @created    4th October, 2025
 * @author     Lemuel van der Merwe <https://github.com/joomengine/Joomla-Service-Directory>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * A professional directory component for listing and showcasing service providers.
 */
namespace JoomService\Component\Servicedirectory\Api\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception\NotAllowed;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Directory Api Controller
 *
 * The read-only list resource of the directory view, answered by the
 * view's own model and the dynamic get it was built from.
 *
 * @since  4.0.0
 */
class DirectoryController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'directory';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'directory';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getModel($name = '', $prefix = '', $config = [])
	{
		// The site model of the directory view, its request state ignored.
		return parent::getModel('Directory', 'Site', array_merge(['ignore_request' => true], $config));
	}

	/**
	 * Display the list of the directory view.
	 *
	 * The dynamic get expects, as far as it shows:
	 *  - where a.published = 1
	 *  - ordered by a.name ASC
	 * Every record is returned, the get does not paginate.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  NotAllowed
	 * @since   4.0.0
	 */
	public function displayList()
	{
		if (!$this->allowView())
		{
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		return parent::displayList();
	}

	/**
	 * The list resource does not serve one item.
	 *
	 * @param   integer  $id  The primary key to display.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function add()
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function edit()
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * The resource is read-only.
	 *
	 * @param   integer  $id  The primary key to delete item.
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function delete($id = null)
	{
		throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 405);
	}

	/**
	 * Whether the calling user may read the directory view.
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	protected function allowView(): bool
	{
		// Get the calling user.
		$user = Factory::getApplication()->getIdentity();

		// The site.directory.access permission the directory view link asks for.
		return $user->authorise('site.directory.access', 'com_servicedirectory');
	}
}
