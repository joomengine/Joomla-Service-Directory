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
use JoomService\Joomla\Data\Factory as DataFactory;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use JoomService\Joomla\Utilities\GetHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory State Admin Model
 *
 * @since  1.6
 */
class StateModel extends AdminModel
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
				'country',
				'type',
				'longitude',
				'latitude'
			),
			'right' => array(
				'iso2',
				'fips_code'
			),
			'above' => array(
				'name',
				'wikidataid'
			)
		),
		'cities' => array(
			'fullwidth' => array(
				'cities'
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
		'administrator/components/com_servicedirectory/assets/css/state.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_servicedirectory/assets/js/admin.js',
		'media/com_servicedirectory/js/state.js'
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
	public $typeAlias = 'com_servicedirectory.state';

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
	public function getTable($type = 'state', $prefix = 'Administrator', $config = [])
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
			if (!empty($item->params) && !is_array($item->params))
			{
				// Convert the params field to an array.
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry->toArray();
			}

			if (!empty($item->metadata))
			{
				// Convert the metadata field to an array.
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}
			$item->cities = DataFactory::_('Data.Subform')->table('city')->get($item->guid ?? '' ,'state', 'cities', ['guid','name','latitude','longitude','wikidataid']);
		}

		return $item;
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
		$form = $this->loadForm('com_servicedirectory.state', 'state', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('state.edit.state', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.state', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('state.edit.created_by', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.created_by', 'com_servicedirectory')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('state.edit.created', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.created', 'com_servicedirectory')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit Name access controls.
		if ($id != 0 && (!$user->authorise('state.edit.name', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.name', 'com_servicedirectory')))
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
		// Modify the from the form based on Name access controls.
		if ($id != 0 && (!$user->authorise('state.access.name', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.access.name', 'com_servicedirectory')))
		{
			// Remove the field
			$form->removeField('name');
		}
		// Modify the form based on View Name access controls.
		if ($id != 0 && (!$user->authorise('state.view.name', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.view.name', 'com_servicedirectory')))
		{
			// Make the field hidded.
			$form->setFieldAttribute('name', 'type', 'hidden');
			// If there is no value continue.
			if (!($val = $form->getValue('name')))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('name', 'required', 'false');
				// Make sure
				$form->setValue('name', null, '');
			}
			elseif (UtilitiesArrayHelper::check($val))
			{
				// We have to unset then (TODO)
				// Hiddend field can not handel array value
				// Even if we convert to json we get an error
				$form->removeField('name');
			}
		}
		// Modify the form based on Edit Country access controls.
		if ($id != 0 && (!$user->authorise('state.edit.country', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.country', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('country', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('country', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('country'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('country', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('country', 'required', 'false');
			}
		}
		// Modify the form based on Edit Type access controls.
		if ($id != 0 && (!$user->authorise('state.edit.type', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.type', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('type', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('type', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('type'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('type', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('type', 'required', 'false');
			}
		}
		// Modify the form based on Edit Cities access controls.
		if ($id != 0 && (!$user->authorise('state.edit.cities', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.cities', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('cities', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('cities', 'readonly', 'true');
			// Disable the buttons form being clickable.
			$class = $form->getFieldAttribute('cities', 'class', '');
			$form->setFieldAttribute('cities', 'class', $class . ' disabled no-click');
			// If there is no value continue.
			if (!$form->getValue('cities'))
			{
				// Remove the field
				$form->removeField('cities');
			}
		}
		// Modify the form based on Edit Wikidataid access controls.
		if ($id != 0 && (!$user->authorise('state.edit.wikidataid', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.wikidataid', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('wikidataid', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('wikidataid', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('wikidataid'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('wikidataid', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('wikidataid', 'required', 'false');
			}
		}
		// Modify the form based on Edit Fips Code access controls.
		if ($id != 0 && (!$user->authorise('state.edit.fips_code', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.fips_code', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('fips_code', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('fips_code', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('fips_code'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('fips_code', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('fips_code', 'required', 'false');
			}
		}
		// Modify the form based on Edit Isotwo access controls.
		if ($id != 0 && (!$user->authorise('state.edit.iso2', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.iso2', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('iso2', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('iso2', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('iso2'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('iso2', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('iso2', 'required', 'false');
			}
		}
		// Modify the form based on Edit Longitude access controls.
		if ($id != 0 && (!$user->authorise('state.edit.longitude', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.longitude', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('longitude', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('longitude', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('longitude'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('longitude', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('longitude', 'required', 'false');
			}
		}
		// Modify the form based on Edit Latitude access controls.
		if ($id != 0 && (!$user->authorise('state.edit.latitude', 'com_servicedirectory.state.' . (int) $id))
			|| ($id == 0 && !$user->authorise('state.edit.latitude', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('latitude', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('latitude', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('latitude'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('latitude', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('latitude', 'required', 'false');
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
		return $this->getCurrentUser()->authorise('state.delete', 'com_servicedirectory.state.' . (int) $record->id);
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
			$permission = $user->authorise('state.edit.state', 'com_servicedirectory.state.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('state.edit.state', 'com_servicedirectory');
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param    array    $data   An array of input data.
	 * @param    string   $key    The name of the key for the primary key.
	 *
	 * @return   boolean
	 * @since    2.5
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		$user = Factory::getApplication()->getIdentity();

		return $user->authorise('state.edit', 'com_servicedirectory.state.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or $user->authorise('state.edit',  'com_servicedirectory');
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
					->from($db->quoteName('#__servicedirectory_state'));
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
		$data = Factory::getApplication()->getUserState('com_servicedirectory.edit.state.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the perprocess of the data
		$this->preprocessData('com_servicedirectory.state', $data);

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
		$this->canDo = ServicedirectoryHelper::getActions('state');
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
			$this->canDo		= ServicedirectoryHelper::getActions('state');
		}

		if (!$this->canDo->get('state.create') && !$this->canDo->get('state.batch'))
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
		elseif (isset($values['published']) && !$this->canDo->get('state.edit.state'))
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
			if (!$this->user->authorise('state.edit', $contexts[$pk]))
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

			// Only for strings
			if (UtilitiesStringHelper::check($this->table->name) && !is_numeric($this->table->name))
			{
				$this->table->name = $this->generateUnique('name',$this->table->name);
			}

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
			$this->canDo		= ServicedirectoryHelper::getActions('state');
		}

		if (!$this->canDo->get('state.edit') && !$this->canDo->get('state.batch'))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('state.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('state.edit', $contexts[$pk]))
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
			$data['guid'] = (string) GetHelper::var('state', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "state", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
		}
		if (isset($data['cities']))
		{
			DataFactory::_('Data.Subform')->table('city')->set($data['cities'] ?? [], 'guid', 'state', $data['guid'] ?? '');
		}

		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new Registry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
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
	 * Method to change the title
	 *
	 * @param   string   $title   The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 */
	protected function _generateNewTitle($title)
	{

		// Alter the title
		$table = $this->getTable();

		while ($table->load(['title' => $title]))
		{
			$title = StringHelper::increment($title);
		}

		return $title;
	}
}
