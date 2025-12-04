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
namespace JoomService\Component\Servicedirectory\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Data\Factory as DataFactory;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Ticket Form Controller
 *
 * @since  1.6
 */
class TicketController extends FormController
{
	use VersionableControllerTrait;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SERVICEDIRECTORY_TICKET';

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 * @since  12.2
	 * @note   Replaces _task.
	 */
	protected $task;

	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'ticket';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $view_item = 'ticket';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $view_list = 'directory';

	/**
	 * Referral value
	 *
	 * @var    string
	 * @since  5.0
	 */
	protected string $ref;

	/**
	 * Referral ID value
	 *
	 * @var    int
	 * @since  5.0
	 */
	protected int $refid;


	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *                           (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		// for modal title key selection (unique key to do mapping)
		$titleKey = $this->input->get('titleKey', 'id', 'word');
		$guid = null;
		$value = null;

 		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$model = $this->getModel();
			$table = $model->getTable();
			$key = $table->getKeyName();
		}

		if ($titleKey === 'guid')
		{
			$guid = $this->input->get('guid', null, 'string');
		}

		if ($guid !== null && GuidHelper::valid($guid))
		{
			$value = GuidHelper::item($guid, 'ticket', 'a.' . $key, 'servicedirectory');
		}

		if ($value !== null)
		{
			$this->input->set($key, $value);
		}

		return parent::edit($key, $urlVar);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = [])
	{
		// Get user object.
		$user = $this->app->getIdentity();
		// Access check.
		$access = $user->authorise('ticket.access', 'com_servicedirectory');
		if (!$access)
		{
			return false;
		}

		// In the absence of better information, revert to the component permissions.
		return $user->authorise('ticket.create', $this->option);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		// get user object.
		$user = $this->app->getIdentity();
		// get record id.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;


		// Access check.
		$access = ($user->authorise('ticket.access', 'com_servicedirectory.ticket.' . (int) $recordId) && $user->authorise('ticket.access', 'com_servicedirectory'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('ticket.edit', 'com_servicedirectory.ticket.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('ticket.edit.own', 'com_servicedirectory.ticket.' . $recordId))
				{
					// Now test the owner is the user.
					$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
					if (empty($ownerId))
					{
						// Need to do a lookup from the model.
						$record = $this->getModel()->getItem($recordId);

						if (empty($record))
						{
							return false;
						}
						$ownerId = $record->created_by;
					}

					// If the owner matches 'me' then allow.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('ticket.edit.own', 'com_servicedirectory'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('ticket.edit', $this->option);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		// get int-defaults (to int new items with default values dynamically)
		$init_defaults = $this->input->get('init_defaults', null, 'STRING');

		// get the referral options (old method use init_defaults or return instead see parent)
		$ref = $this->input->get('ref', 0, 'string');
		$refid = $this->input->get('refid', 0, 'int');

		// get redirect info.
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		// set int-defaults
		if (!empty($init_defaults))
		{
			$append = '&init_defaults='. (string) $init_defaults . $append;
		}

		// set the referral options
		if ($refid && $ref)
		{
			$append = '&ref=' . (string) $ref . '&refid='. (int) $refid . $append;
		}
		elseif ($ref)
		{
			$append = '&ref='. (string) $ref . $append;
		}

		return $append;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   12.2
	 */
	public function cancel($key = null)
	{
		// get the referral options
		$this->ref = $this->input->get('ref', 0, 'word');
		$this->refid = $this->input->get('refid', 0, 'int');

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');

		$cancel = parent::cancel($key);

		if (!is_null($return) && Uri::isInternal(base64_decode($return)))
		{
			$redirect = base64_decode($return);

			// Redirect to the return value.
			$this->setRedirect(
				Route::_(
					$redirect, false
				)
			);
		}
		elseif ($this->refid && $this->ref)
		{
			$redirect = '&view=' . (string) $this->ref . '&layout=edit&id=' . (int) $this->refid;

			// Redirect to the item screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . $redirect, false
				)
			);
		}
		elseif ($this->ref)
		{
			$redirect = '&view=' . (string) $this->ref;

			// Redirect to the list screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . $redirect, false
				)
			);
		}
		return $cancel;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// get the referral options
		$this->ref = $this->input->get('ref', 0, 'word');
		$this->refid = $this->input->get('refid', 0, 'int');

		// Check if there is a return value
		$return = $this->input->get('return', null, 'base64');
		$canReturn = (!is_null($return) && Uri::isInternal(base64_decode($return)));

		if ($this->ref || $this->refid || $canReturn)
		{
			// to make sure the item is checkedin on redirect
			$this->task = 'save';
		}

		$saved = parent::save($key, $urlVar);

		// This is not needed since parent save already does this
		// Due to the ref and refid implementation we need to add this
		if ($canReturn)
		{
			$redirect = base64_decode($return);

			// Redirect to the return value.
			$this->setRedirect(
				Route::_(
					$redirect, false
				)
			);
		}
		elseif ($this->refid && $this->ref)
		{
			$redirect = '&view=' . (string) $this->ref . '&layout=edit&id=' . (int) $this->refid;

			// Redirect to the item screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . $redirect, false
				)
			);
		}
		elseif ($this->ref)
		{
			$redirect = '&view=' . (string) $this->ref;

			// Redirect to the list screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . $redirect, false
				)
			);
		}
		return $saved;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   BaseDatabaseModel  $model     The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
	{
		// current id and state
		$id = $model->getState('ticket.id', 0);
		$published = (int) ($validData['published'] ?? $model->getState('ticket.published', 0));

		// get current user
		$user = $this->app->getIdentity();

		// set the status based on the type of area and user editing this
		$isAdmin = $this->app->isClient('administrator');

		// get the permission
		$permission = $id > 0
			? $user->authorise('ticket.edit.state', 'com_servicedirectory.ticket.' . (int) $id)
			: $user->authorise('ticket.edit.state', 'com_servicedirectory.ticket');

		// if this is a site area, and not staff, set the ticket to open = 1
		if (!$isAdmin && !$permission)
		{
			$published = 1;
		}
		// if this is a admin area or staff, and the ticket was open = 1, set it to hold = 0
		elseif (($permission || $isAdmin) && $published === 1)
		{
			$published = 0;
		}

		// Ensure the ticket is always assigned to (created_by) the company owner,
		// allowing them to manage and respond to the ticket directly.
		// This maintains ownership-based access control - only this owner can edit their own tickets.
		$company = $validData['company'] ?? $model->getState('ticket.company', null);
		$guid = $validData['guid'] ?? $model->getState('ticket.guid', null);
		if (!empty($guid) && !empty($company) && ($owner = GuidHelper::item($company, 'company', 'a.created_by', 'servicedirectory')) !== null)
		{
			$ticket = (object) [
				'created_by' => $owner,
				'guid' => $guid,
				'published' => $published
			];

			DataFactory::_('Data.Item')->table('ticket')->set($ticket, 'guid', 'update');
		}

		return;
	}

}
