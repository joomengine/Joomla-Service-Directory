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
namespace JoomService\Component\Servicedirectory\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use Joomla\CMS\Helper\TagsHelper;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\ObjectHelper;
use JoomService\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Servicedirectory\Utilities\Permitted\Actions;
use JoomService\Joomla\Utilities\GetHelper;
use JoomService\Joomla\Utilities\Component\Helper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Review_company_update Admin Model
 *
 * @since  1.6
 */
class Review_company_updateModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'left' => array(
				'category',
				'tags',
				'areas_of_expertise'
			),
			'right' => array(
				'company_type',
				'companysize',
				'chamber_of_commerce'
			),
			'fullwidth' => array(
				'description'
			),
			'above' => array(
				'name',
				'alias',
				'review_status'
			)
		),
		'contact_info' => array(
			'left' => array(
				'contactname',
				'email',
				'phone',
				'website',
				'social_handles'
			),
			'right' => array(
				'languages',
				'addresses'
			)
		),
		'media' => array(
			'fullwidth' => array(
				'file_type',
				'note_file_vdm_uploader',
				'note_file_vdm_display'
			)
		),
		'portfolio' => array(
			'fullwidth' => array(
				'note_portfolio',
				'portfolios'
			)
		)
	);

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_servicedirectory/assets/css/admin.css',
		'administrator/components/com_servicedirectory/assets/css/review_company_update.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_servicedirectory/assets/js/admin.js',
		'media/com_servicedirectory/js/review_company_update.js'
 	];

	/**
	 * @var     string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_SERVICEDIRECTORY';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_servicedirectory.review_company_update';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($type = 'review_company_update', $prefix = 'Administrator', $config = [])
	{
		// get instance of the table
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (property_exists($item, 'metadata') && !is_array($item->metadata))
			{
				// Convert the metadata field to an array.
				$metadata       = new Registry($item->metadata);
				$item->metadata = $metadata->toArray();
			}

			// check edit access permissions
			if (!empty($item->id) && !$this->allowEdit((array) $item))
			{
 				$app = Factory::getApplication();
  				$app->enqueueMessage(Text::_('Not authorised!'), 'error');
				$app->redirect('index.php?option=com_servicedirectory');
				return false;
			}

			if (!empty($item->addresses))
			{
				// Convert the addresses field to an array.
				$addresses = new Registry;
				$addresses->loadString($item->addresses);
				$item->addresses = $addresses->toArray();
			}

			if (!empty($item->languages))
			{
				// Convert the languages field to an array.
				$languages = new Registry;
				$languages->loadString($item->languages);
				$item->languages = $languages->toArray();
			}

			if (!empty($item->tags))
			{
				// Convert the tags field to an array.
				$tags = new Registry;
				$tags->loadString($item->tags);
				$item->tags = $tags->toArray();
			}

			if (!empty($item->areas_of_expertise))
			{
				// Convert the areas_of_expertise field to an array.
				$areas_of_expertise = new Registry;
				$areas_of_expertise->loadString($item->areas_of_expertise);
				$item->areas_of_expertise = $areas_of_expertise->toArray();
			}

			if (!empty($item->portfolios))
			{
				// Convert the portfolios field to an array.
				$portfolios = new Registry;
				$portfolios->loadString($item->portfolios);
				$item->portfolios = $portfolios->toArray();
			}

			if (!empty($item->social_handles))
			{
				// Convert the social_handles field to an array.
				$social_handles = new Registry;
				$social_handles->loadString($item->social_handles);
				$item->social_handles = $social_handles->toArray();
			}
		}
		$this->companyvvvw = $item->guid;

		return $item;
	}

	/**
	 * Method to get list data.
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getVvwsupport()
	{
		// Get the user object.
		$user = Factory::getApplication()->getIdentity();
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the servicedirectory_ticket table
		$query->from($db->quoteName('#__servicedirectory_ticket', 'a'));

		// From the servicedirectory_company table.
		$query->select($db->quoteName(['g.name','g.id'],['company_name','company_id']));
		$query->join('LEFT', $db->quoteName('#__servicedirectory_company', 'g') . ' ON (' . $db->quoteName('a.company') . ' = ' . $db->quoteName('g.guid') . ')');

		// Filter by companyvvvw global.
		$companyvvvw = $this->companyvvvw;
		if (is_numeric($companyvvvw ))
		{
			$query->where('a.company = ' . (int) $companyvvvw );
		}
		elseif (is_string($companyvvvw))
		{
			$query->where('a.company = ' . $db->quote($companyvvvw));
		}
		else
		{
			$query->where('a.company = -5');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		$_access = $this->getState('filter.access');
		if ($_access && is_numeric($_access))
		{
			$query->where('a.access = ' . (int) $_access);
		}
		elseif (UtilitiesArrayHelper::check($_access))
		{
			// Secure the array for the query
			$_access = ArrayHelper::toInteger($_access);
			// Filter by the Access Array.
			$query->where('a.access IN (' . implode(',', $_access) . ')');
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_servicedirectory'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Order the results by ordering
		$query->order('a.published  ASC');
		$query->order('a.ordering  ASC');

		// Load the items
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			$items = $db->loadObjectList();

			// Set values to display correctly.
			if (UtilitiesArrayHelper::check($items))
			{
				// Get the user object if not set.
				if (!isset($user) || !ObjectHelper::check($user))
				{
					$user = $this->getCurrentUser();
				}
				foreach ($items as $nr => &$item)
				{
					// Remove items the user can't access.
					$access = ($user->authorise('ticket.access', 'com_servicedirectory.ticket.' . (int) $item->id) && $user->authorise('ticket.access', 'com_servicedirectory'));
					if (!$access)
					{
						unset($items[$nr]);
						continue;
					}

				}
			}

			// set selection value to a translatable value
			if (UtilitiesArrayHelper::check($items))
			{
				foreach ($items as $nr => &$item)
				{
					// convert priority
					$item->priority = $this->selectionTranslationVvwsupport($item->priority, 'priority');
					// convert published
					$item->published = $this->selectionTranslationVvwsupport($item->published, 'published');
				}
			}

			return $items;
		}
		return false;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return  string   The translatable string.
	 */
	public function selectionTranslationVvwsupport($value,$name)
	{
		// Array of priority language strings
		if ($name === 'priority')
		{
			$priorityArray = array(
				0 => 'COM_SERVICEDIRECTORY_TICKET_SELECT_A_PRIORITY',
				1 => 'COM_SERVICEDIRECTORY_TICKET_LOW',
				2 => 'COM_SERVICEDIRECTORY_TICKET_NORMAL',
				3 => 'COM_SERVICEDIRECTORY_TICKET_HIGH'
			);
			// Now check if value is found in this array
			if (isset($priorityArray[$value]) && UtilitiesStringHelper::check($priorityArray[$value]))
			{
				return $priorityArray[$value];
			}
		}
		// Array of published language strings
		if ($name === 'published')
		{
			$publishedArray = array(
				1 => 'COM_SERVICEDIRECTORY_TICKET_OPEN',
				0 => 'COM_SERVICEDIRECTORY_TICKET_ONHOLD',
				2 => 'COM_SERVICEDIRECTORY_TICKET_CLOSED',
				-2 => 'COM_SERVICEDIRECTORY_TICKET_TRASHED'
			);
			// Now check if value is found in this array
			if (isset($publishedArray[$value]) && UtilitiesStringHelper::check($publishedArray[$value]))
			{
				return $publishedArray[$value];
			}
		}
		return $value;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true, $options = ['control' => 'jform'])
	{
		// set load data option
		$options['load_data'] = $loadData;
		// check if xpath was set in options
		$xpath = false;
		if (isset($options['xpath']))
		{
			$xpath = $options['xpath'];
			unset($options['xpath']);
		}
		// check if clear form was set in options
		$clear = false;
		if (isset($options['clear']))
		{
			$clear = $options['clear'];
			unset($options['clear']);
		}

		// Get the form.
		$form = $this->loadForm('com_servicedirectory.review_company_update', 'review_company_update', $options, $clear, $xpath);

		if (empty($form))
		{
			return false;
		}

		$app = Factory::getApplication();

		$jinput = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = Factory::getApplication()->getIdentity();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_servicedirectory')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if (!$user->authorise('core.edit.created_by', 'com_servicedirectory'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if (!$user->authorise('core.edit.created', 'com_servicedirectory'))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit Name access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.name', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.name', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('name', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('name', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('name'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('name', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('name', 'required', 'false');
			}
		}
		// Modify the form based on Edit Contactname access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.contactname', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.contactname', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('contactname', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('contactname', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('contactname'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('contactname', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('contactname', 'required', 'false');
			}
		}
		// Modify the form based on Edit Category access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.category', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.category', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('category', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('category', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('category'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('category', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('category', 'required', 'false');
			}
		}
		// Modify the form based on Edit Review Status access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.review_status', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.review_status', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('review_status', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('review_status', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('review_status'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('review_status', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('review_status', 'required', 'false');
			}
		}
		// Modify the form based on Edit File Type access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.file_type', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.file_type', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('file_type', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('file_type', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('file_type'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('file_type', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('file_type', 'required', 'false');
			}
		}
		// Modify the form based on Edit Addresses access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.addresses', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.addresses', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('addresses', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('addresses', 'readonly', 'true');
			// Disable the buttons form being clickable.
			$class = $form->getFieldAttribute('addresses', 'class', '');
			$form->setFieldAttribute('addresses', 'class', $class . ' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('addresses'))
			{
				// Remove the field
				$form->removeField('addresses');
			}
		}
		// Modify the form based on Edit Languages access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.languages', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.languages', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('languages', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('languages', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('languages'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('languages', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('languages', 'required', 'false');
			}
		}
		// Modify the form based on Edit Chamber Of Commerce access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.chamber_of_commerce', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.chamber_of_commerce', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('chamber_of_commerce', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('chamber_of_commerce', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('chamber_of_commerce'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('chamber_of_commerce', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('chamber_of_commerce', 'required', 'false');
			}
		}
		// Modify the form based on Edit Guid access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.guid', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.guid', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('guid', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('guid', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('guid'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('guid', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('guid', 'required', 'false');
			}
		}
		// Modify the form based on Edit Tags access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.tags', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.tags', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('tags', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('tags', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('tags'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('tags', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('tags', 'required', 'false');
			}
		}
		// Modify the form based on Edit Email access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.email', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.email', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('email', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('email', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('email'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('email', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('email', 'required', 'false');
			}
		}
		// Modify the form based on Edit Areas Of Expertise access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.areas_of_expertise', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.areas_of_expertise', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('areas_of_expertise', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('areas_of_expertise', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('areas_of_expertise'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('areas_of_expertise', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('areas_of_expertise', 'required', 'false');
			}
		}
		// Modify the form based on Edit Alias access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.alias', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.alias', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('alias', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('alias', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('alias'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('alias', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('alias', 'required', 'false');
			}
		}
		// Modify the form based on Edit Company Type access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.company_type', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.company_type', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('company_type', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('company_type', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('company_type'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('company_type', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('company_type', 'required', 'false');
			}
		}
		// Modify the form based on Edit Description access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.description', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.description', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('description', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('description', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('description'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('description', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('description', 'required', 'false');
			}
		}
		// Modify the form based on Edit Website access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.website', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.website', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('website', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('website', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('website'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('website', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('website', 'required', 'false');
			}
		}
		// Modify the form based on Edit Portfolios access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.portfolios', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.portfolios', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('portfolios', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('portfolios', 'readonly', 'true');
			// Disable the buttons form being clickable.
			$class = $form->getFieldAttribute('portfolios', 'class', '');
			$form->setFieldAttribute('portfolios', 'class', $class . ' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('portfolios'))
			{
				// Remove the field
				$form->removeField('portfolios');
			}
		}
		// Modify the form based on Edit Social Handles access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.social_handles', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.social_handles', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('social_handles', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('social_handles', 'readonly', 'true');
			// Disable the buttons form being clickable.
			$class = $form->getFieldAttribute('social_handles', 'class', '');
			$form->setFieldAttribute('social_handles', 'class', $class . ' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('social_handles'))
			{
				// Remove the field
				$form->removeField('social_handles');
			}
		}
		// Modify the form based on Edit Phone access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.phone', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.phone', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('phone', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('phone', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('phone'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('phone', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('phone', 'required', 'false');
			}
		}
		// Modify the form based on Edit Companysize access controls.
		if ($id != 0 && (!$user->authorise('review_company_update.edit.companysize', 'com_servicedirectory.review_company_update.' . (int) $id))
			|| ($id == 0 && !$user->authorise('review_company_update.edit.companysize', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('companysize', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('companysize', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('companysize'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('companysize', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('companysize', 'required', 'false');
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
			$initDefaults = $jinput->get('init_defaults', null, 'STRING');
			if (!empty($initDefaults))
			{
				// Now check if this json values are valid
				$initDefaults = json_decode(urldecode($initDefaults), true);
				if (is_array($initDefaults))
				{
					foreach ($initDefaults as $field => $value)
					{
						$form->setValue($field, null, $value);
					}
				}
			}
		}

		// Only load the GUID if new item (or empty)
		if (0 == $id || !($val = $form->getValue('guid')))
		{
			$form->setValue('guid', null, GuidHelper::get());
		}

		return $form;
	}

	/**
	 * Method to get the styles that have to be included on the view
	 *
	 * @return  array    styles files
	 * @since   4.3
	 */
	public function getStyles(): array
	{
		return $this->styles;
	}

	/**
	 * Method to set the styles that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setStyles(string $path): void
	{
		$this->styles[] = $path;
	}

	/**
	 * Method to get the script that have to be included on the view
	 *
	 * @return  array    script files
	 * @since   4.3
	 */
	public function getScripts(): array
	{
		return $this->scripts;
	}

	/**
	 * Method to set the script that have to be included on the view
	 *
	 * @return  void
	 * @since   4.3
	 */
	public function setScript(string $path): void
	{
		$this->scripts[] = $path;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || ($record->published != -2))
		{
			return false;
		}

		// The record has been set. Check the record permissions.
		return $this->getCurrentUser()->authorise('core.delete', 'com_servicedirectory.review_company_update.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = $this->getCurrentUser();
		$recordId = $record->id ?? 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit.state', 'com_servicedirectory.review_company_update.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return parent::canEditState($record);
	}

	/**
	 * Method to check if you can edit an existing record.
	 *   We know this is a double access check (Controller already does an allowEdit check)
	 *   But when the item is directly accessed the controller is skipped (2025_).
	 *
	 * @param    array    $data   An array of input data.
	 * @param    string   $key    The name of the key for the primary key.
	 *
	 * @return   boolean  True if allowed to edit the record. Defaults to the permission set in the component.
	 * @since    2.5
	 */
	protected function allowEdit(array $data = [], string $key = 'id'): bool
	{
		// get user object.
		$user = $this->getCurrentUser();
		// get record id.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;


		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit', 'com_servicedirectory.review_company_update.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('core.edit.own', 'com_servicedirectory.review_company_update.' . $recordId))
				{
					// Now test the owner is the user.
					$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
					if (empty($ownerId))
					{
						return false;
					}

					// If the owner matches 'me' then allow.
					if ($ownerId == $user->id)
					{
						if ($user->authorise('core.edit.own', 'com_servicedirectory'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission given, core edit must be checked.
		return $user->authorise('core.edit', $this->option);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = $this->getCurrentUser();

		if (isset($table->name))
		{
			$table->name = \htmlspecialchars_decode($table->name, ENT_QUOTES);
		}

		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}

		if (empty($table->id))
		{
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__servicedirectory_review_company_update'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
			$table->modified_by = $user->id;
		}

		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_servicedirectory.edit.review_company_update.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the perprocess of the data
		$this->preprocessData('com_servicedirectory.review_company_update', $data);

		return $data;
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqueFields()
	{
		return array('guid');
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(Text::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user ??= $this->getCurrentUser();
		$this->table = $this->getTable();
		$this->tableClassName = get_class($this->table);
		$this->contentType = new UCMType;
		$this->type = $this->contentType->getTypeByTable($this->tableClassName);
		$this->canDo = ServicedirectoryHelper::getActions('review_company_update');
		$this->batchSet = true;

		if (!$this->canDo->get('core.batch'))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		if ($this->type == false)
		{
			$type = new UCMType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['move_copy']))
		{
			$cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands, $pks, $contexts);

				if (is_array($result))
				{
					foreach ($result as $old => $new)
					{
						$contexts[$new] = $contexts[$old];
					}
					$pks = array_values($result);
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands, $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $values    The new values.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since 12.2
	 */
	protected function batchCopy($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user 		= Factory::getApplication()->getIdentity();
			$this->table 		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= Actions::get('review_company_update');
		}

		if (!$this->canDo->get('core.create') && !$this->canDo->get('review_company_update.batch'))
		{
			return false;
		}

		// get list of unique fields
		$uniqueFields = $this->getUniqueFields();
		// remove move_copy from array
		unset($values['move_copy']);

		// make sure published is set
		if (!isset($values['published']))
		{
			$values['published'] = 0;
		}
		elseif (isset($values['published']) && !$this->canDo->get('core.edit.state'))
		{
				$values['published'] = 0;
		}

		$newIds = [];
		// Parent exists so let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// only allow copy if user may edit this item.
			if (!$this->user->authorise('core.edit', $contexts[$pk]))
			{
				// Not fatal error
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
				continue;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}
			list($this->table->name, $this->table->alias) = $this->_generateNewTitle($this->table->alias, $this->table->name);

			// insert all set values
			if (UtilitiesArrayHelper::check($values))
			{
				foreach ($values as $key => $value)
				{
					if (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}

			// update all unique fields
			if (UtilitiesArrayHelper::check($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$this->table->$uniqueField = $this->generateUnique($uniqueField,$this->table->$uniqueField);
				}
			}

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// TODO: Deal with ordering?
			// $this->table->ordering = 1;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch move items to a new category
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since 12.2
	 */
	protected function batchMove($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user		= Factory::getApplication()->getIdentity();
			$this->table		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= Actions::get('review_company_update');
		}

		if (!$this->canDo->get('core.edit') && !$this->canDo->get('review_company_update.batch'))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('core.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('core.edit', $contexts[$pk]))
			{
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// insert all set values.
			if (UtilitiesArrayHelper::check($values))
			{
				foreach ($values as $key => $value)
				{
					// Do special action for access.
					if ('access' === $key && strlen($value) > 0)
					{
						$this->table->$key = $value;
					}
					elseif (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}


			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   1.6
	 */
	public function save($data)
	{
		$input    = Factory::getApplication()->getInput();
		$filter   = InputFilter::getInstance();

		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');

			$metadata = new Registry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}


		// Set the GUID if empty or not valid
		if (empty($data['guid']) && $data['id'] > 0)
		{
			// get the existing one
			$data['guid'] = (string) GetHelper::var('review_company_update', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "review_company_update", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
		}
		$user      = $this->getCurrentUser();
		$companyId = (int) (GuidHelper::item($data['guid'], 'company') ?? 0);
		$reviewId  = (int) ($data['id'] ?? 0);
		$reviewStatus = (int) ($data['review_status'] ?? 0);

		// Check if the user has permission to manage company review state
		$canReview = $user->authorise('core.edit.state', 'com_servicedirectory.review_company_update') &&
		             $user->authorise('core.edit.state', 'com_servicedirectory.company') &&
		             (
			             ($companyId > 0 && $user->authorise('core.edit', 'com_servicedirectory.company.' . $companyId)) ||
			             $user->authorise('core.create', 'com_servicedirectory.company')
		             );

		if ($canReview)
		{
			// Handle approved review
			if ($reviewStatus === 1)
			{
				unset($data['review_status']); // Not in the company table
				$data['published'] = 1;
				$data['id']        = $companyId;

				$success = false;

				try
				{
					$companyModel = Helper::getModel('Company');
					$success      = $companyModel->save($data);
				}
				catch (\Throwable $e)
				{
					// Optionally log the error if needed
					// $app->getLogger()->error($e->getMessage());
				}

				// Restore review context and set state based on result
				$data['id']            = $reviewId;
				$data['review_status'] = $success ? $reviewStatus : 2; // 2 = on hold
				$data['published']     = $success ? 2 : 0;             // 2 = archived, 0 = unpublished
			}
			// Handle declined review (status 3)
			elseif ($reviewStatus === 3)
			{
				$data['published'] = 2;
			}
		}
		else
		{
			// User cannot review — reset to pending
			$data['review_status'] = 0;
			$data['published']     = 1;
		}

		// Set the addresses items to data.
		if (isset($data['addresses']) && is_array($data['addresses']))
		{
			$addresses = new Registry;
			$addresses->loadArray($data['addresses']);
			$data['addresses'] = (string) $addresses;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty addresses
		elseif (!isset($data['addresses'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.addresses', 'com_servicedirectory'))
		{
			// Set the empty addresses to data
			$data['addresses'] = '';
		}

		// Set the languages items to data.
		if (isset($data['languages']) && is_array($data['languages']))
		{
			$languages = new Registry;
			$languages->loadArray($data['languages']);
			$data['languages'] = (string) $languages;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty languages
		elseif (!isset($data['languages'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.languages', 'com_servicedirectory'))
		{
			// Set the empty languages to data
			$data['languages'] = '';
		}

		// Set the tags items to data.
		if (isset($data['tags']) && is_array($data['tags']))
		{
			$tags = new Registry;
			$tags->loadArray($data['tags']);
			$data['tags'] = (string) $tags;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty tags
		elseif (!isset($data['tags'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.tags', 'com_servicedirectory'))
		{
			// Set the empty tags to data
			$data['tags'] = '';
		}

		// Set the areas_of_expertise items to data.
		if (isset($data['areas_of_expertise']) && is_array($data['areas_of_expertise']))
		{
			$areas_of_expertise = new Registry;
			$areas_of_expertise->loadArray($data['areas_of_expertise']);
			$data['areas_of_expertise'] = (string) $areas_of_expertise;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty areas_of_expertise
		elseif (!isset($data['areas_of_expertise'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.areas_of_expertise', 'com_servicedirectory'))
		{
			// Set the empty areas_of_expertise to data
			$data['areas_of_expertise'] = '';
		}

		// Set the portfolios items to data.
		if (isset($data['portfolios']) && is_array($data['portfolios']))
		{
			$portfolios = new Registry;
			$portfolios->loadArray($data['portfolios']);
			$data['portfolios'] = (string) $portfolios;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty portfolios
		elseif (!isset($data['portfolios'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.portfolios', 'com_servicedirectory'))
		{
			// Set the empty portfolios to data
			$data['portfolios'] = '';
		}

		// Set the social_handles items to data.
		if (isset($data['social_handles']) && is_array($data['social_handles']))
		{
			$social_handles = new Registry;
			$social_handles->loadArray($data['social_handles']);
			$data['social_handles'] = (string) $social_handles;
		}
		// Also check permission since the value may be removed due to permissions
		// Then we do not want to clear it out, but simple ignore the empty social_handles
		elseif (!isset($data['social_handles'])
			&& Factory::getApplication()->getIdentity()->authorise('review_company_update.edit.social_handles', 'com_servicedirectory'))
		{
			// Set the empty social_handles to data
			$data['social_handles'] = '';
		}

		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new Registry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the name for save as copy
		if ($input->get('task') === 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name)
			{
				list($name, $alias) = $this->_generateNewTitle($data['alias'], $data['name']);
				$data['name'] = $name;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['published'] = 0;
		}

		// Automatic handling of alias for empty fields
		if (in_array($input->get('task'), array('apply', 'save', 'save2new')) && (int) $input->get('id') == 0)
		{
			if ($data['alias'] == null || empty($data['alias']))
			{
				if (Factory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = OutputFilter::stringURLUnicodeSlug($data['name']);
				}
				else
				{
					$data['alias'] = OutputFilter::stringURLSafe($data['name']);
				}

				$table = clone $this->getTable();

				if ($table->load(array('alias' => $data['alias'])) && ($table->id != $data['id'] || $data['id'] == 0))
				{
					$msg = Text::_('COM_SERVICEDIRECTORY_REVIEW_COMPANY_UPDATE_SAVE_WARNING');
				}

				$data['alias'] = $this->_generateNewTitle($data['alias']);

				if (isset($msg))
				{
					Factory::getApplication()->enqueueMessage($msg, 'warning');
				}
			}
		}

		// Alter the unique field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other unique fields
			$uniqueFields = $this->getUniqueFields();
			if (UtilitiesArrayHelper::check($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$data[$uniqueField] = $this->generateUnique($uniqueField,$data[$uniqueField]);
				}
			}
		}

		if (parent::save($data))
		{
			return true;
		}
		return false;
	}

	/**
	 * Method to generate a unique value.
	 *
	 * @param   string  $field name.
	 * @param   string  $value data.
	 *
	 * @return  string  New value.
	 * @since   3.0
	 */
	protected function generateUnique($field, $value)
	{
		// set field value unique
		$table = $this->getTable();

		while ($table->load([$field => $value]))
		{
			$value = StringHelper::increment($value);
		}

		return $value;
	}

	/**
	 * Method to change the title/s & alias.
	 *
	 * @param   string         $alias        The alias.
	 * @param   string/array   $title        The title.
	 *
	 * @return	array/string  Contains the modified title/s and/or alias.
	 *
	 */
	protected function _generateNewTitle($alias, $title = null)
	{

		// Alter the title/s & alias
		$table = $this->getTable();

		while ($table->load(['alias' => $alias]))
		{
			// Check if this is an array of titles
			if (UtilitiesArrayHelper::check($title))
			{
				foreach($title as $nr => &$_title)
				{
					$_title = StringHelper::increment($_title);
				}
			}
			// Make sure we have a title
			elseif ($title)
			{
				$title = StringHelper::increment($title);
			}
			$alias = StringHelper::increment($alias, 'dash');
		}
		// Check if this is an array of titles
		if (UtilitiesArrayHelper::check($title))
		{
			$title[] = $alias;
			return $title;
		}
		// Make sure we have a title
		elseif ($title)
		{
			return array($title, $alias);
		}
		// We only had an alias
		return $alias;
	}
}
