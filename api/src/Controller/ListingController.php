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
 * Listing Api Controller
 *
 * The read-only item resource of the listing view, answered by the
 * view's own model and the dynamic get it was built from.
 *
 * @since  4.0.0
 */
class ListingController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'listing';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'listing';

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
		// The site model of the listing view, its request state ignored.
		return parent::getModel('Listing', 'Site', array_merge(['ignore_request' => true], $config));
	}

	/**
	 * Display the item of the listing view.
	 *
	 * The dynamic get expects, as far as it shows:
	 *  - a.id = the id of the request (the :id route segment)
	 *  - yy.access IN the access levels of the calling user
	 *  - where a.published = 1
	 *  - where cc.published = 1
	 *  - where l.published = 1
	 *  - where t.published = 1
	 *  - where ee.published = 1
	 *  - where p.published = 1
	 *  - where yy.published = 1
	 *
	 * @param   integer  $id  The id of the item, when the route carries one.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  NotAllowed
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		if (!$this->allowView())
		{
			throw new NotAllowed(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		return parent::displayItem($id ?? $this->input->getInt('id', 0));
	}

	/**
	 * The item resource does not serve a list.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  \RuntimeException
	 * @since   4.0.0
	 */
	public function displayList()
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
	 * Whether the calling user may read the listing view.
	 *
	 * @return  bool
	 *
	 * @since   4.0.0
	 */
	protected function allowView(): bool
	{
		// Get the calling user.
		$user = Factory::getApplication()->getIdentity();

		// The site.listing.access permission the listing view link asks for.
		return $user->authorise('site.listing.access', 'com_servicedirectory');
	}
}
