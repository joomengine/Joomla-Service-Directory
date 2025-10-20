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
namespace JoomService\Component\Servicedirectory\Site\Model;

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
use JoomService\Joomla\Utilities\ObjectHelper;
use JoomService\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Utilities\Component\Helper;
use JoomService\Joomla\Utilities\GetHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Company Admin Model
 *
 * @since  1.6
 */
class CompanyModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * The tab layout fields array.
	 *
	 * @var      array
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
				'alias'
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
		'components/com_servicedirectory/assets/css/site.css',
		'components/com_servicedirectory/assets/css/company.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'components/com_servicedirectory/assets/js/site.js',
		'media/com_servicedirectory/js/company.js'
 	];

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_SERVICEDIRECTORY';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_servicedirectory.company';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A database object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($type = 'company', $prefix = 'Administrator', $config = [])
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
	 *
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
			$item->addresses = DataFactory::_('Data.Subform')->table('address')->get($item->guid ?? '' ,'company', 'addresses', ['guid','type','line_one','line_two','country','state','city','postal']);
			$item->languages = DataFactory::_('Data.Items')->table('company_language')->values([$item->guid], 'company', 'language');
			$item->tags = DataFactory::_('Data.Items')->table('company_tag')->values([$item->guid], 'company', 'tag');
			$item->areas_of_expertise = DataFactory::_('Data.Items')->table('company_area_of_expertise')->values([$item->guid], 'company', 'area_of_expertise');
			$item->portfolios = DataFactory::_('Data.Subform')->table('portfolio')->get($item->guid ?? '' ,'company', 'portfolios', ['guid','project_title','client_name','target_industry','services_provided','project_url','description']);
			$item->social_handles = DataFactory::_('Data.Subform')->table('social_handle')->get($item->guid ?? '' ,'company', 'social_handles', ['guid','platform','handle']);
		}
		$this->companyvvvv = $item->guid;

		return $item;
	}

	/**
	 * Method to get list data.
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getVvvsupport()
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

		// Filter by companyvvvv global.
		$companyvvvv = $this->companyvvvv;
		if (is_numeric($companyvvvv ))
		{
			$query->where('a.company = ' . (int) $companyvvvv );
		}
		elseif (is_string($companyvvvv))
		{
			$query->where('a.company = ' . $db->quote($companyvvvv));
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
					// convert published
					$item->published = $this->selectionTranslationVvvsupport($item->published, 'published');
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
	public function selectionTranslationVvvsupport($value,$name)
	{
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
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = [], $loadData = true, $options = array('control' => 'jform'))
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
		$form = $this->loadForm('com_servicedirectory.company', 'company', $options, $clear, $xpath);

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
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_servicedirectory.company.' . (int) $id))
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
		if ($id != 0 && (!$user->authorise('company.edit.name', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.name', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.contactname', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.contactname', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.category', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.category', 'com_servicedirectory')))
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
		// Modify the form based on Edit Addresses access controls.
		if ($id != 0 && (!$user->authorise('company.edit.addresses', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.addresses', 'com_servicedirectory')))
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
		// Modify the form based on Edit File Type access controls.
		if ($id != 0 && (!$user->authorise('company.edit.file_type', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.file_type', 'com_servicedirectory')))
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
		// Modify the form based on Edit Chamber Of Commerce access controls.
		if ($id != 0 && (!$user->authorise('company.edit.chamber_of_commerce', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.chamber_of_commerce', 'com_servicedirectory')))
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
		// Modify the form based on Edit Languages access controls.
		if ($id != 0 && (!$user->authorise('company.edit.languages', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.languages', 'com_servicedirectory')))
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
		// Modify the form based on Edit Tags access controls.
		if ($id != 0 && (!$user->authorise('company.edit.tags', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.tags', 'com_servicedirectory')))
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
		// Modify the form based on Edit Guid access controls.
		if ($id != 0 && (!$user->authorise('company.edit.guid', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.guid', 'com_servicedirectory')))
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
		// Modify the form based on Edit Areas Of Expertise access controls.
		if ($id != 0 && (!$user->authorise('company.edit.areas_of_expertise', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.areas_of_expertise', 'com_servicedirectory')))
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
		// Modify the form based on Edit Email access controls.
		if ($id != 0 && (!$user->authorise('company.edit.email', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.email', 'com_servicedirectory')))
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
		// Modify the form based on Edit Alias access controls.
		if ($id != 0 && (!$user->authorise('company.edit.alias', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.alias', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.company_type', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.company_type', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.description', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.description', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.website', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.website', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.portfolios', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.portfolios', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.social_handles', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.social_handles', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.phone', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.phone', 'com_servicedirectory')))
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
		if ($id != 0 && (!$user->authorise('company.edit.companysize', 'com_servicedirectory.company.' . (int) $id))
			|| ($id == 0 && !$user->authorise('company.edit.companysize', 'com_servicedirectory')))
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
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || ($record->published != -2))
		{
			return false;
		}

		// The record has been set. Check the record permissions.
		return $this->getCurrentUser()->authorise('core.delete', 'com_servicedirectory.company.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = $this->getCurrentUser();
		$recordId = $record->id ?? 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('core.edit.state', 'com_servicedirectory.company.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absence of better information, revert to the component permissions.
		return parent::canEditState($record);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array    $data   An array of input data.
	 * @param   string   $key    The name of the key for the primary key.
	 *
	 * @return    boolean
	 * @since    2.5
	 */
	protected function allowEdit($data = [], $key = 'id')
	{
		// Check specific edit permission then general edit permission.

		return Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_servicedirectory.company.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A Table object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = $this->getCurrentUser();

		if (isset($table->name))
		{
			$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
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
					->from($db->quoteName('#__servicedirectory_company'));
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
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_servicedirectory.edit.company.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		// run the perprocess of the data
		$this->preprocessData('com_servicedirectory.company', $data);

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
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}

		// linked tables to update
		$_tables_array = [
			'file' => 'entity',
			'address' => 'company',
			'portfolio' => 'company',
			'social_handle' => 'company',
			'company_tag' => 'company',
			'company_language' => 'company',
			'company_area_of_expertise' => 'company'
		];

		// we must also update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			Helper::setOption('com_servicedirectory');
			foreach($_tables_array as $_delete_table => $_field_name)
			{
				// get the company guid's
				$_guids = DataFactory::_('Load')->values(
					['a.guid' => 'guid'], // select
					['a' => 'company'], // tables
					['a.id' =>
						['value' => $pks, 'operator' => 'IN']
					] // where
				);

				// get the linked IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // select
					['a' => $_delete_table], // tables
					['a.' . $_field_name =>
						['value' => $_guids, 'operator' => 'IN']
					] // where
				);

				if ($_pks !== null)
				{
					// load the model
					$_Model = Helper::getModel($_delete_table);

					// change publish state to trash (in-case the state was not changed in sync with the parent)
					$_Model->publish($_pks, -2);

					// delete the items
					$_Model->delete($_pks);
				}
			}
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
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}

		// linked tables to update
		$_tables_array = [
			'file' => 'entity',
			'address' => 'company',
			'portfolio' => 'company',
			'social_handle' => 'company',
			'company_tag' => 'company',
			'company_language' => 'company',
			'company_area_of_expertise' => 'company'
		];

		// we must also update all linked tables
		if (!empty($_tables_array) && UtilitiesArrayHelper::check($pks))
		{
			Helper::setOption('com_servicedirectory');
			foreach($_tables_array as $_update_table => $_field_name)
			{
				// get the admin guid's
				$_guids = DataFactory::_('Load')->values(
					['a.guid' => 'guid'], // select
					['a' => 'company'], // tables
					['a.id' =>
						['value' => $pks, 'operator' => 'IN']
					] // where
				);

				// get the linked IDs
				$_pks = DataFactory::_('Load')->values(
					['a.id' => 'id'], // select
					['a' => $_update_table], // tables
					['a.' . $_field_name =>
						['value' => $_guids, 'operator' => 'IN']
					] // where
				);

				if ($_pks !== null)
				{
					// load the model
					$_Model = Helper::getModel($_update_table);

					// change publish state
					$_Model->publish($_pks, $value);
				}
			}
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
	 *
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
		$this->canDo = ServicedirectoryHelper::getActions('company');
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
			$this->canDo		= ServicedirectoryHelper::getActions('company');
		}

		if (!$this->canDo->get('core.create') && !$this->canDo->get('company.batch'))
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
			$this->canDo		= ServicedirectoryHelper::getActions('company');
		}

		if (!$this->canDo->get('core.edit') && !$this->canDo->get('company.batch'))
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
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$app = Factory::getApplication();
		$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
		$filter = InputFilter::getInstance();

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
			$data['guid'] = (string) GetHelper::var('company', $data['id'], 'id', 'guid');
		}

		// Set the GUID if empty or not valid
		while (!GuidHelper::valid($data['guid'], "company", $data['id']))
		{
			// must always be set
			$data['guid'] = (string) GuidHelper::get();
		}
		if (isset($data['addresses']))
		{
			DataFactory::_('Data.Subform')->table('address')->set($data['addresses'] ?? [], 'guid', 'company', $data['guid'] ?? '');
		}
		if (!empty($data['languages']) && is_array($data['languages']))
		{
			$langSub = [];
			$companyGuid = $data['guid'] ?? 'error';
		
			// Retrieve existing language records for this company
			$existing = DataFactory::_('Data.Items')
				->table('company_language')
				->get([$companyGuid], 'company');
		
			// Index existing records by language for quick lookup
			$existingMap = [];
			if (!empty($existing))
			{
				foreach ($existing as $row)
				{
					if (!empty($row->language))
					{
						$existingMap[$row->language] = $row->guid ?? '';
					}
				}
			}
		
			// get max allowed number of items
			$max = Helper::getParams()->get('max_languages', 5);
			$m = 0;
		
			// Build subform data entries
			foreach ($data['languages'] as $n => $lang)
			{
				if (!is_string($lang) || $lang === '')
				{
					continue; // Skip invalid entries
				}
		
				if ($m >= $max)
				{
					break;
				}
		
				$langSub["language{$n}"] = [
					'guid'     => $existingMap[$lang] ?? '',
					'language' => $lang
				];
		
				$m++;
			}
		
			DataFactory::_('Data.Subform')
				->table('company_language')
				->set($langSub, 'guid', 'company', $companyGuid);
				unset($data['languages']);
		}
		if (!empty($data['tags']) && is_array($data['tags']))
		{
			$tagSub = [];
			$companyGuid = $data['guid'] ?? 'error';
		
			// Retrieve existing tag records for this company
			$existing = DataFactory::_('Data.Items')
				->table('company_tag')
				->get([$companyGuid], 'company');
		
			// Index existing records by tag for quick lookup
			$existingMap = [];
			if (!empty($existing))
			{
				foreach ($existing as $row)
				{
					if (!empty($row->tag))
					{
						$existingMap[$row->tag] = $row->guid ?? '';
					}
				}
			}
		
			// get max allowed number of items
			$max = Helper::getParams()->get('max_expertise', 5);
			$m = 0;
		
			// Build subform data entries
			foreach ($data['tags'] as $n => $tag)
			{
				if (!is_string($tag) || $tag === '')
				{
					continue; // Skip invalid entries
				}
		
				if ($m >= $max)
				{
					break;
				}
		
				$tagSub["tag{$n}"] = [
					'guid'     => $existingMap[$tag] ?? '',
					'tag' => $tag
				];
		
				$m++;
			}
		
			DataFactory::_('Data.Subform')
				->table('company_tag')
				->set($tagSub, 'guid', 'company', $companyGuid);
				unset($data['tags']);
		}
		if (!empty($data['areas_of_expertise']) && is_array($data['areas_of_expertise']))
		{
			$area_of_expertiseSub = [];
			$companyGuid = $data['guid'] ?? 'error';
		
			// Retrieve existing area_of_expertise records for this company
			$existing = DataFactory::_('Data.Items')
				->table('company_area_of_expertise')
				->get([$companyGuid], 'company');
		
			// Index existing records by area_of_expertise for quick lookup
			$existingMap = [];
			if (!empty($existing))
			{
				foreach ($existing as $row)
				{
					if (!empty($row->area_of_expertise))
					{
						$existingMap[$row->area_of_expertise] = $row->guid ?? '';
					}
				}
			}
		
			// get max allowed number of items
			$max = Helper::getParams()->get('max_expertise', 5);
			$m = 0;
		
			// Build subform data entries
			foreach ($data['areas_of_expertise'] as $n => $area_of_expertise)
			{
				if (!is_string($area_of_expertise) || $area_of_expertise === '')
				{
					continue; // Skip invalid entries
				}
		
				if ($m >= $max)
				{
					break;
				}
		
				$area_of_expertiseSub["area_of_expertise{$n}"] = [
					'guid'     => $existingMap[$area_of_expertise] ?? '',
					'area_of_expertise' => $area_of_expertise
				];
		
				$m++;
			}
		
			DataFactory::_('Data.Subform')
				->table('company_area_of_expertise')
				->set($area_of_expertiseSub, 'guid', 'company', $companyGuid);
				unset($data['areas_of_expertise']);
		}
		if (isset($data['portfolios']))
		{
			DataFactory::_('Data.Subform')->table('portfolio')->set($data['portfolios'] ?? [], 'guid', 'company', $data['guid'] ?? '');
		}
		if (isset($data['social_handles']))
		{
			DataFactory::_('Data.Subform')->table('social_handle')->set($data['social_handles'] ?? [], 'guid', 'company', $data['guid'] ?? '');
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
					$msg = Text::_('COM_SERVICEDIRECTORY_COMPANY_SAVE_WARNING');
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
	 *
	 * @since   3.0
	 */
	protected function generateUnique($field, $value)
	{
		// set field value unique
		$table = $this->getTable();

		while ($table->load(array($field => $value)))
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
