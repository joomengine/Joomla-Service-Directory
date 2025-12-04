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
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Utilities\GetHelper;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Portfolio Admin Model
 *
 * @since  1.6
 */
class PortfolioModel extends AdminModel
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
				'client_name',
				'target_industry'
			),
			'right' => array(
				'services_provided',
				'project_url'
			),
			'fullwidth' => array(
				'description'
			),
			'above' => array(
				'project_title',
				'company'
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
		'administrator/components/com_servicedirectory/assets/css/portfolio.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'administrator/components/com_servicedirectory/assets/js/admin.js',
		'media/com_servicedirectory/js/portfolio.js'
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
	public $typeAlias = 'com_servicedirectory.portfolio';

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
	public function getTable($type = 'portfolio', $prefix = 'Administrator', $config = [])
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
		$form = $this->loadForm('com_servicedirectory.portfolio', 'portfolio', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('portfolio.edit.state', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.state', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('portfolio.edit.created_by', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.created_by', 'com_servicedirectory')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.created', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.created', 'com_servicedirectory')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit Project Title access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.project_title', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.project_title', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('project_title', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('project_title', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('project_title'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('project_title', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('project_title', 'required', 'false');
			}
		}
		// Modify the form based on Edit Company access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.company', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.company', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('company', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('company', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('company'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('company', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('company', 'required', 'false');
			}
		}
		// Modify the form based on Edit Client Name access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.client_name', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.client_name', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('client_name', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('client_name', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('client_name'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('client_name', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('client_name', 'required', 'false');
			}
		}
		// Modify the form based on Edit Target Industry access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.target_industry', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.target_industry', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('target_industry', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('target_industry', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('target_industry'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('target_industry', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('target_industry', 'required', 'false');
			}
		}
		// Modify the form based on Edit Guid access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.guid', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.guid', 'com_servicedirectory')))
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
		// Modify the form based on Edit Description access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.description', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.description', 'com_servicedirectory')))
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
		// Modify the form based on Edit Project Url access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.project_url', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.project_url', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('project_url', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('project_url', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('project_url'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('project_url', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('project_url', 'required', 'false');
			}
		}
		// Modify the form based on Edit Services Provided access controls.
		if ($id != 0 && (!$user->authorise('portfolio.edit.services_provided', 'com_servicedirectory.portfolio.' . (int) $id))
			|| ($id == 0 && !$user->authorise('portfolio.edit.services_provided', 'com_servicedirectory')))
		{
			// Disable field on display.
			$form->setFieldAttribute('services_provided', 'disabled', 'true');
			// Make field readonly on display.
			$form->setFieldAttribute('services_provided', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('services_provided'))
			{
				// Disable field while saving.
				$form->setFieldAttribute('services_provided', 'filter', 'unset');
				// Disable field while saving.
				$form->setFieldAttribute('services_provided', 'required', 'false');
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
		return $this->getCurrentUser()->authorise('portfolio.delete', 'com_servicedirectory.portfolio.' . (int) $record->id);
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
			$permission = $user->authorise('portfolio.edit.state', 'com_servicedirectory.portfolio.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return $user->authorise('portfolio.edit.state', 'com_servicedirectory');
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


		// Access check.
		$access = ($user->authorise('portfolio.access', 'com_servicedirectory.portfolio.' . (int) $recordId) && $user->authorise('portfolio.access', 'com_servicedirectory'));
		if (!$access)
		{
			return false;
		}

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('portfolio.edit', 'com_servicedirectory.portfolio.' . (int) $recordId);
			if (!$permission)
			{
				if ($user->authorise('portfolio.edit.own', 'com_servicedirectory.portfolio.' . $recordId))
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
						if ($user->authorise('portfolio.edit.own', 'com_servicedirectory'))
						{
							return true;
						}
					}
				}
				return false;
			}
		}
		// Since there is no permission, revert to the component permissions.
		return $user->authorise('portfolio.edit', $this->option);
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
					->from($db->quoteName('#__servicedirectory_portfolio'));
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
		$data = Factory::getApplication()->getUserState('com_servicedirectory.edit.portfolio.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the per process of the data
		$this->preprocessData('com_servicedirectory.portfolio', $data);

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
			$data['guid'] = (string) GetHelper::var('portfolio', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "portfolio", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
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
