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
 * Tickets Admin Controller
 *
 * @since  1.6
 */
class TicketsController extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SERVICEDIRECTORY_TICKETS';

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
	public function getModel($name = 'Ticket', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Redirect the request to the companies.
	 *
	 * @return bool True on successful initialization, false on failure.
	 * @since  5.2.4
	 */
	public function gotoCompanies()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// check if user has the right
		$user = $this->app->getIdentity();

		// set default error message
		$message = '<h1>' . Text::_('COM_SERVICEDIRECTORY_PERMISSION_DENIED') . '</h1>';
		$message .= '<p>' . Text::_('COM_SERVICEDIRECTORY_YOU_DO_NOT_HAVE_ACCESS_PERMISSION_TO_COMPANIES') . '</p>';
		$status = 'error';
		$success = false;

		if($user->authorise('company.access', 'com_servicedirectory'))
		{
			// set success message
			$message = null;

			$status = null;
			$success = true;

			// set redirect
			$redirect_url = Route::_('index.php?option=com_servicedirectory&view=companies', false);
		}
		else
		{
			// set redirect
			$redirect_url = Route::_('index.php?option=com_servicedirectory&view=tickets', false);
		}
		$this->setRedirect($redirect_url, $message, $status);

		return $success;
	}

	/**
	 * Redirect the request to the company reviews.
	 *
	 * @return bool True on successful initialization, false on failure.
	 * @since  5.2.4
	 */
	public function gotoCompanyReviews()
	{
		// Check for request forgeries
		Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

		// check if user has the right
		$user = $this->app->getIdentity();

		// set default error message
		$message = '<h1>' . Text::_('COM_SERVICEDIRECTORY_PERMISSION_DENIED') . '</h1>';
		$message .= '<p>' . Text::_('COM_SERVICEDIRECTORY_YOU_DO_NOT_HAVE_ACCESS_PERMISSION_TO_COMPANY_REVIEWS') . '</p>';
		$status = 'error';
		$success = false;

		if($user->authorise('review_company_update.access', 'com_servicedirectory'))
		{
			// set success message
			$message = null;

			$status = null;
			$success = true;

			// set redirect
			$redirect_url = Route::_('index.php?option=com_servicedirectory&view=review_company_updates', false);
		}
		else
		{
			// set redirect
			$redirect_url = Route::_('index.php?option=com_servicedirectory&view=tickets', false);
		}
		$this->setRedirect($redirect_url, $message, $status);

		return $success;
	}
}