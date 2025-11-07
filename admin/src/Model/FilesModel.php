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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use Joomla\CMS\Helper\TagsHelper;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\ObjectHelper;
use JoomService\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Files List Model
 *
 * @since  1.6
 */
class FilesModel extends ListModel
{
	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_servicedirectory/assets/css/admin.css',
		'administrator/components/com_servicedirectory/assets/css/files.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_servicedirectory/assets/js/admin.js'
 	];

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], ?MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.access','access',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'g.name','file_type',
				'a.entity_type','entity_type',
				'a.name','name',
				'a.size','size'
			);
		}

		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @since   1.7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = $this->app;
		$input = $this->app->getInput();

		// Adjust the context to support modal layouts.
		if ($layout = $input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Check if the form was submitted
		$formSubmited = $input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		if ($formSubmited)
		{
			$access = $input->post->get('access');
			$this->setState('filter.access', $access);
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$file_type = $this->getUserStateFromRequest($this->context . '.filter.file_type', 'filter_file_type');
		if ($formSubmited)
		{
			$file_type = $input->post->get('file_type');
			$this->setState('filter.file_type', $file_type);
		}

		$entity_type = $this->getUserStateFromRequest($this->context . '.filter.entity_type', 'filter_entity_type');
		if ($formSubmited)
		{
			$entity_type = $input->post->get('entity_type');
			$this->setState('filter.entity_type', $entity_type);
		}

		$name = $this->getUserStateFromRequest($this->context . '.filter.name', 'filter_name');
		if ($formSubmited)
		{
			$name = $input->post->get('name');
			$this->setState('filter.name', $name);
		}

		$size = $this->getUserStateFromRequest($this->context . '.filter.size', 'filter_size');
		if ($formSubmited)
		{
			$size = $input->post->get('size');
			$this->setState('filter.size', $size);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		// Check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

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
				$access = ($user->authorise('file.access', 'com_servicedirectory.file.' . (int) $item->id) && $user->authorise('file.access', 'com_servicedirectory'));
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
				// convert entity_type
				$item->entity_type = $this->selectionTranslation($item->entity_type, 'entity_type');
			}
		}


		// return items
		return $items;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return  string   The translatable string.
	 */
	public function selectionTranslation($value,$name)
	{
		// Array of entity_type language strings
		if ($name === 'entity_type')
		{
			$entity_typeArray = array(
				0 => 'COM_SERVICEDIRECTORY_FILE_SELECT_AN_OPTION',
				'extension' => 'COM_SERVICEDIRECTORY_FILE_EXTENSION',
				'company' => 'COM_SERVICEDIRECTORY_FILE_COMPANY',
				'volunteer' => 'COM_SERVICEDIRECTORY_FILE_VOLUNTEER',
				'platform' => 'COM_SERVICEDIRECTORY_FILE_PLATFORM',
				'category' => 'COM_SERVICEDIRECTORY_FILE_CATEGORY',
				'tag' => 'COM_SERVICEDIRECTORY_FILE_TAG',
				'area_of_expertise' => 'COM_SERVICEDIRECTORY_FILE_AREA_OF_EXPERTISE',
				'ticket' => 'COM_SERVICEDIRECTORY_FILE_TICKET'
			);
			// Now check if value is found in this array
			if (isset($entity_typeArray[$value]) && StringHelper::check($entity_typeArray[$value]))
			{
				return $entity_typeArray[$value];
			}
		}
		return $value;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string    An SQL query
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = $this->getCurrentUser();
		// Create a new query object.
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the servicedirectory_item table
		$query->from($db->quoteName('#__servicedirectory_file', 'a'));

		// From the servicedirectory_file_type table.
		$query->select($db->quoteName(['g.name','g.id'],['file_type_name','file_type_id']));
		$query->join('LEFT', $db->quoteName('#__servicedirectory_file_type', 'g') . ' ON (' . $db->quoteName('a.file_type') . ' = ' . $db->quoteName('g.guid') . ')');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
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
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.name LIKE '.$search.' OR a.file_type LIKE '.$search.' OR g.name LIKE '.$search.' OR a.size LIKE '.$search.' OR a.entity_type LIKE '.$search.' OR a.guid LIKE '.$search.' OR a.entity LIKE '.$search.' OR a.file_path LIKE '.$search.' OR a.extension LIKE '.$search.' OR a.mime LIKE '.$search.')');
			}
		}

		// Filter by File_type.
		$_file_type = $this->getState('filter.file_type');
		if (is_numeric($_file_type))
		{
			if (is_float($_file_type))
			{
				$query->where('a.file_type = ' . (float) $_file_type);
			}
			else
			{
				$query->where('a.file_type = ' . (int) $_file_type);
			}
		}
		elseif (StringHelper::check($_file_type))
		{
			$query->where('a.file_type = ' . $db->quote($db->escape($_file_type)));
		}
		// Filter by Entity_type.
		$_entity_type = $this->getState('filter.entity_type');
		if (is_numeric($_entity_type))
		{
			if (is_float($_entity_type))
			{
				$query->where('a.entity_type = ' . (float) $_entity_type);
			}
			else
			{
				$query->where('a.entity_type = ' . (int) $_entity_type);
			}
		}
		elseif (StringHelper::check($_entity_type))
		{
			$query->where('a.entity_type = ' . $db->quote($db->escape($_entity_type)));
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'a.id');
		$orderDirn = $this->getState('list.direction', 'desc');
		if ($orderCol != '')
		{
			// Check that the order direction is valid encase we have a field called direction as part of filers.
			$orderDirn = (is_string($orderDirn) && in_array(strtolower($orderDirn), ['asc', 'desc'])) ? $orderDirn : 'desc';
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get data during an export request.
	 *
	 * @param   array  $pks  The ids of the items to get
	 * @param   JUser  $user  The user making the request
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getPrivacyExport($pks, $user = null)
	{
		// setup the query
		if (($pks_size = UtilitiesArrayHelper::check($pks)) !== false || 'bulk' === $pks)
		{
			// Set a value to know this is privacy method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
			$_privacy = true;
			// Get the user object if not set.
			if (!isset($user) || !ObjectHelper::check($user))
			{
				$user = $this->getCurrentUser();
			}
			// Create a new query object.
			$db = $this->getDatabase();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the servicedirectory_file table
			$query->from($db->quoteName('#__servicedirectory_file', 'a'));
			// The bulk export path
			if ('bulk' === $pks)
			{
				$query->where('a.id > 0');
			}
			// A large array of ID's will not work out well
			elseif ($pks_size > 500)
			{
				// Use lowest ID
				$query->where('a.id >= ' . (int) min($pks));
				// Use highest ID
				$query->where('a.id <= ' . (int) max($pks));
			}
			// The normal default path
			else
			{
				$query->where('a.id IN (' . implode(',',$pks) . ')');
			}
			// Get global switch to activate text only export
			$export_text_only = ComponentHelper::getParams('com_servicedirectory')->get('export_text_only', 0);
			// Add these queries only if text only is required
			if ($export_text_only)
			{

				// From the servicedirectory_file_type table.
				$query->select($db->quoteName(['g.name','g.id'],['file_type','file_type_id']));
				$query->join('LEFT', $db->quoteName('#__servicedirectory_file_type', 'g') . ' ON (' . $db->quoteName('a.file_type') . ' = ' . $db->quoteName('g.guid') . ')');
			}
			// Implement View Level Access
			if (!$user->authorise('core.options', 'com_servicedirectory'))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}

			// Order the results by ordering
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
						$access = ($user->authorise('file.access', 'com_servicedirectory.file.' . (int) $item->id) && $user->authorise('file.access', 'com_servicedirectory'));
						if (!$access)
						{
							unset($items[$nr]);
							continue;
						}

					}
				}
			// Add these translation only if text only is required
			if ($export_text_only)
			{

					// set selection value to a translatable value
					if (UtilitiesArrayHelper::check($items))
					{
						foreach ($items as $nr => &$item)
						{
							// convert entity_type
							$item->entity_type = $this->selectionTranslation($item->entity_type, 'entity_type');
						}
					}

			}
				return json_decode(json_encode($items), true);
			}
		}
		return false;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		// Check if the value is an array
		$_access = $this->getState('filter.access');
		if (UtilitiesArrayHelper::check($_access))
		{
			$id .= ':' . implode(':', $_access);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_access)
		 || StringHelper::check($_access))
		{
			$id .= ':' . $_access;
		}
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.file_type');
		$id .= ':' . $this->getState('filter.entity_type');
		$id .= ':' . $this->getState('filter.name');
		$id .= ':' . $this->getState('filter.size');

		return parent::getStoreId($id);
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
	 * Build an SQL query to check in all items left checked out longer then a set time.
	 *
	 * @return void
	 * @throws \DateMalformedStringException
	 * @since 3.2.0
	 */
	protected function checkInNow(): void
	{
		// Get set check in time
		$time = ComponentHelper::getParams('com_servicedirectory')->get('check_in');

		if ($time)
		{
			// Get a db connection.
			$db = $this->getDatabase();
			// Reset query.
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__servicedirectory_file'));
			// Only select items that are checked out.
			$query->where($db->quoteName('checked_out') . ' >= 0');
			// Query only to see if we have a rows
			$db->setQuery($query, 0, 1);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get target date in the past.
				$date = Factory::getDate()->modify($time)->toSql();
				// Reset query.
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = [
					$db->quoteName('checked_out_time') . ' = NULL',
					$db->quoteName('checked_out') . ' = NULL'
				];

				// Conditions for which records should be updated.
				$conditions = [
					$db->quoteName('checked_out') . ' = 0 OR ' . $db->quoteName('checked_out') . ' > 0',
					$db->quoteName('checked_out_time') . ' < ' . $db->quote($date)
				];

				// Check table.
				$query->update($db->quoteName('#__servicedirectory_file'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
