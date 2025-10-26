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
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Site\Helper\RouteHelper;
use Joomla\CMS\Helper\TagsHelper;
use JoomService\Joomla\Data\Factory as DataFactory;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\JsonHelper;
use JoomService\Joomla\Servicedirectory\Markdown\Html;
use JoomService\Joomla\Utilities\StringHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Uri\Uri;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory List Model for Areaofexpertise
 *
 * @since  1.6
 */
class AreaofexpertiseModel extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * The unique identifier of the current user.
	 *
	 * @var   int|null  The ID of the current user.
	 * @since 3.2.0
	 */
	protected ?int $userId;

	/**
	 * Flag indicating whether the current user is a guest.
	 *
	 * @var   int  1 if the user is a guest, 0 otherwise.
	 * @since 3.2.0
	 */
	protected int $guest;

	/**
	 * An array of groups that the current user belongs to.
	 *
	 * @var   array|null  An array of user group IDs.
	 * @since 3.2.0
	 */
	protected ?array $groups;

	/**
	 * An array of view access levels for the current user.
	 *
	 * @var   array|null  An array of access level IDs.
	 * @since 3.2.0
	 */
	protected ?array $levels;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

	/**
	 * The input object, providing access to the request data.
	 *
	 * @var   Input  The input object.
	 * @since 3.2.0
	 */
	protected Input $input;

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'components/com_servicedirectory/assets/css/site.css',
		'components/com_servicedirectory/assets/css/areaofexpertise.css'
 	];

	/**
	 * The scripts array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $scripts = [
		'components/com_servicedirectory/assets/js/site.js'
 	];

	/**
	 * A custom property for UIKit components. (not used unless you load v2)
	 */
	protected $uikitComp;

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->app ??= Factory::getApplication();
		$this->input ??= $this->app->getInput();

		// Set the current user for authorisation checks (for those calling this model directly)
		$this->user ??= $this->getCurrentUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();

		// will be removed
		$this->initSet = true;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return   string  An SQL query
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_company as a
		$query->select('a.*');
		$query->from($db->quoteName('#__servicedirectory_company', 'a'));

		// Get from #__servicedirectory_category as cc
		$query->select($db->quoteName(
			array('cc.id','cc.alias','cc.description','cc.name'),
			array('category_id','category_alias','category_description','category_name')));
		$query->join('LEFT', ($db->quoteName('#__servicedirectory_category', 'cc')) . ' ON (' . $db->quoteName('a.category') . ' = ' . $db->quoteName('cc.guid') . ')');

		// Filtering.

		$this->entity_type = 'area_of_expertise';
		$guidKey = 'guid';
		$joinTable = 'company_area_of_expertise';

		$this->entity = '';
		$pkg = (int) $this->input->getInt('id', 0);
		$targeted = false;
		if (!empty($pkg))
		{
			$this->entity = DataFactory::_('Data.Item')->table($this->entity_type)->value($pkg, 'id', $guidKey);
			$companies = !empty($this->entity) ? DataFactory::_('Data.Items')->table($joinTable)->values(
				[$this->entity], $this->entity_type, 'company') : null;
			if (UtilitiesArrayHelper::check($companies, true))
			{
				$companies = array_map(function($company) use ($db) { return $db->quote($company); }, $companies);
				$query->where('a.guid IN (' . implode(', ', $companies) .')');
				$targeted = true;
			}
		}
		if ($targeted)
		{

			$search = $this->input->get('search', null, 'STRING');
			if (!empty($search))
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(' .
					'a.name LIKE ' . $search .
					' OR a.contactname LIKE ' . $search .
					' OR a.email LIKE ' . $search .
					' OR a.phone LIKE ' . $search .
					' OR a.website LIKE ' . $search .
					' OR a.chamber_of_commerce LIKE ' . $search .
					' OR a.company_type LIKE ' . $search .
					' OR a.companysize LIKE ' . $search .
					' OR a.description LIKE ' . $search .
				')');
			}
		}
		else
		{
			$query->where('a.id = 0'); // empty set
		}
		unset($companies, $search);
		// give us a random ordering here (new order every minute)
		$query->order('RAND(' . $this->getTimeBasedRandomSeed() . ')');
		// Get where a.published is 1
		$query->where('a.published = 1');
		// Get where cc.published is 1
		$query->where('cc.published = 1');
		$query->order('RAND()');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6
	 */
	public function getItems()
	{
		$user = $this->user;
		// check if this user has permission to access item
		if (!$user->authorise('site.areaofexpertise.access', 'com_servicedirectory'))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_SERVICEDIRECTORY_NOT_AUTHORISED_TO_VIEW_AREAOFEXPERTISE'), 'error');
			// redirect away to the default view if no access allowed.
			$app->redirect(Route::_('index.php?option=com_servicedirectory&view=directory'));
			return false;
		}
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_servicedirectory', true);

		// Insure all item fields are adapted where needed.
		if (UtilitiesArrayHelper::check($items))
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
				// Check if item has params, or pass whole item.
				$params = (isset($item->params) && JsonHelper::check($item->params)) ? json_decode($item->params) : $item;
				// Make sure the content prepare plugins fire on category_description
				$_category_description = new \stdClass();
				$_category_description->text =& $item->category_description; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (category_description) to context
				// onContentPrepare Event Trigger
				$this->getDispatcher()->dispatch('onContentPrepare',
					new ContentPrepareEvent(
						'onContentPrepare',
						[
							'context' => 'com_servicedirectory.areaofexpertise.category_description',
							'subject' => $_category_description,
							'params' => $params,
							'page' => 0
						]
					)
				);
				// Check if item has params, or pass whole item.
				$params = (isset($item->params) && JsonHelper::check($item->params)) ? json_decode($item->params) : $item;
				// Make sure the content prepare plugins fire on description
				$_description = new \stdClass();
				$_description->text =& $item->description; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (description) to context
				// onContentPrepare Event Trigger
				$this->getDispatcher()->dispatch('onContentPrepare',
					new ContentPrepareEvent(
						'onContentPrepare',
						[
							'context' => 'com_servicedirectory.areaofexpertise.description',
							'subject' => $_description,
							'params' => $params,
							'page' => 0
						]
					)
				);
				// set guidCompanyCompany_languageL to the $item object.
				$item->guidCompanyCompany_languageL = $this->getGuidCompanyCompany_languageDdfc_L($item->guid);
				// set guidCompanyCompany_tagT to the $item object.
				$item->guidCompanyCompany_tagT = $this->getGuidCompanyCompany_tagDdfc_T($item->guid);
				// set guidCompanyCompany_area_of_expertiseEE to the $item object.
				$item->guidCompanyCompany_area_of_expertiseEE = $this->getGuidCompanyCompany_area_of_expertiseDdfc_EE($item->guid);
			}
		}



		$db = $this->getDatabase();
		$this->companies = [];
		$this->mapper = [
			'guidCompanyCompany_languageL' => [
				'route' => 'getLangRoute',
				'set' => 'languages',
				'escape' => ['name']
			],
			'guidCompanyCompany_tagT' => [
				'route' => 'getTagRoute',
				'set' => 'tags',
				'escape' => ['name']
			],
			'guidCompanyCompany_area_of_expertiseEE' => [
				'route' => 'getAreaofexpertiseRoute',
				'set' => 'areas_of_expertise',
				'escape' => ['name']
			]
		];

		$this->mainEscapeFields = [
			'name', 'contactname', 'email', 'phone', 'website',
			'chamber_of_commerce', 'company_type', 'companysize', 'category_name'
		];

		$this->baseUrl = rtrim(Uri::root(), '/');
		$this->returnUrl = urlencode(base64_encode((string) Uri::getInstance()));

		foreach ($items as &$_item)
		{
			// Register global values
			$this->companies[] = $db->quote($_item->guid);

			// process the item
			$_item = $this->processItem($_item);
		}

		// return items
		return $items;
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
	 * Method to get an array of Company_language Objects.
	 *
	 * @return mixed  An array of Company_language Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanyCompany_languageDdfc_L($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_company_language as l
		$query->select($db->quoteName(
			array('l.language'),
			array('alias')));
		$query->from($db->quoteName('#__servicedirectory_company_language', 'l'));
		$query->where('l.company = ' . $db->quote($guid));

		// Get from #__servicedirectory_language as ll
		$query->select($db->quoteName(
			array('ll.id','ll.name'),
			array('id','name')));
		$query->join('LEFT', ($db->quoteName('#__servicedirectory_language', 'll')) . ' ON (' . $db->quoteName('l.language') . ' = ' . $db->quoteName('ll.langtag') . ')');
		// Get where l.published is 1
		$query->where('l.published = 1');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return false;
	}

	/**
	 * Method to get an array of Company_tag Objects.
	 *
	 * @return mixed  An array of Company_tag Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanyCompany_tagDdfc_T($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_company_tag as t
		$query->select($db->quoteName(
			array('t.tag'),
			array('guid')));
		$query->from($db->quoteName('#__servicedirectory_company_tag', 't'));
		$query->where('t.company = ' . $db->quote($guid));

		// Get from #__servicedirectory_tag as tt
		$query->select($db->quoteName(
			array('tt.id','tt.description','tt.name','tt.alias'),
			array('id','description','name','alias')));
		$query->join('LEFT', ($db->quoteName('#__servicedirectory_tag', 'tt')) . ' ON (' . $db->quoteName('t.tag') . ' = ' . $db->quoteName('tt.guid') . ')');
		// Get where t.published is 1
		$query->where('t.published = 1');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return false;
	}

	/**
	 * Method to get an array of Company_area_of_expertise Objects.
	 *
	 * @return mixed  An array of Company_area_of_expertise Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanyCompany_area_of_expertiseDdfc_EE($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_company_area_of_expertise as ee
		$query->select($db->quoteName(
			array('ee.area_of_expertise'),
			array('guid')));
		$query->from($db->quoteName('#__servicedirectory_company_area_of_expertise', 'ee'));
		$query->where('ee.company = ' . $db->quote($guid));

		// Get from #__servicedirectory_area_of_expertise as aa
		$query->select($db->quoteName(
			array('aa.id','aa.name','aa.alias','aa.description'),
			array('id','name','alias','description')));
		$query->join('LEFT', ($db->quoteName('#__servicedirectory_area_of_expertise', 'aa')) . ' ON (' . $db->quoteName('ee.area_of_expertise') . ' = ' . $db->quoteName('aa.guid') . ')');
		// Get where ee.published is 1
		$query->where('ee.published = 1');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return false;
	}


	/**
	 * Custom Method
	 *
	 * @return mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getLogos()
	{

		// Get the global params
		$globalParams = ComponentHelper::getParams('com_servicedirectory', true);
		$thumb = 'logo__%';
		$category = 'company';
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_file as a
		$query->select($db->quoteName(
			array('a.name','a.entity','a.guid'),
			array('name','entity','guid')));
		$query->from($db->quoteName('#__servicedirectory_file', 'a'));
		// Get where a.name is $thumb
		$query->where('a.name LIKE ' . $db->quote($thumb));
		// Get where a.entity_type is $category
		$query->where('a.entity_type = ' . $db->quote($category));
		if (isset($this->companies) && UtilitiesArrayHelper::check($this->companies))
		{
			// Get where a.entity is $this->companies
			$query->where('a.entity IN (' . implode(',', $this->companies) . ')');
		}
		else
		{
			return false;
		}

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (empty($items))
		{
			return false;
		}

		// Insure all item fields are adapted where needed.
		if (UtilitiesArrayHelper::check($items))
		{
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = ($item->id ?? '0') . (isset($item->alias) ? ':' . $item->alias : '');
			}
		}
		$baseUrl = rtrim(Uri::root(), '/');

		$queryParams = [
			'option'     => 'com_servicedirectory',
			'controller' => 'download',
			'task'       => 'download.image',
		];

		$images = [];

		foreach ($items as $_item)
		{
			$queryParams['file'] = $_item->guid;
			$queryParams['name'] = $_item->name;

			$images[$_item->entity] = $baseUrl . Route::_(
				'index.php?' . http_build_query($queryParams)
			);
		}

		$items = $images;
		// return items
		return $items;
	}

	/**
	 * Custom Method
	 *
	 * @return mixed  item data object on success, false on failure.
	 *
	 */
	public function getBanner()
	{
		$file_type = 'banner__%';

		$baseUrl = rtrim(Uri::root(), '/');

		$queryParams = [
			'option'     => 'com_servicedirectory',
			'controller' => 'download',
			'task'       => 'download.image',
		];
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get data
		// Select from category table
		$query->select($db->quoteName(['a.description', 'a.name'], ['description', 'name']))
		      ->from($db->quoteName('#__servicedirectory_' . $this->entity_type, 'a'));

		// Select from file table
		$query->select($db->quoteName(['b.name', 'b.entity', 'b.guid'], ['file_name', 'entity', 'guid']));

		// Join files so categories without files are still shown
		// THIS IS WHAT THE DYNAMIC GET CAN NOT YET DO, (MULTI JOIN VALUES)
		// HENCE THE CUSTOM CODE
		$query->join(
		    'LEFT',
		    $db->quoteName('#__servicedirectory_file', 'b')
		    . ' ON (' . $db->quoteName('a.guid') . ' = ' . $db->quoteName('b.entity')
		    . ' AND ' . $db->quoteName('b.name') . ' LIKE ' . $db->quote($file_type)
		    . ' AND ' . $db->quoteName('b.entity_type') . ' = ' . $db->quote($this->entity_type)
		    . ')'
		);

		// Filter by the desired category only
		$query->where($db->quoteName('a.guid') . ' = ' . $db->quote($this->entity));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		// Load the results as a stdClass object.
		$data = $db->loadObject();
		if (!empty($data) && !empty($data->file_name))
		{
			$queryParams['file'] = $data->guid;
			$queryParams['name'] = $data->file_name;

			$data->src = $baseUrl . Route::_(
				'index.php?' . http_build_query($queryParams)
			);
		}
		// convert description from markdown to HTML
		if (!empty($data->description))
		{
			$data->description = $this->convertMarkdownToHtml($data->description);
		}

		if (empty($data))
		{
			return false;
		}

		// return data object.
		return $data;
	}


	/**
	 * The entity value.
	 *
	 * @var  string
	 * @since 5.1.3
	 */
	protected string $entity;

	/**
	 * The entity type value.
	 *
	 * @var  string
	 * @since 5.1.3
	 */
	protected string $entity_type;

	/**
	 * The companies.
	 *
	 * @var  array
	 * @since 5.1.3
	 */
	protected array $companies = [];

	/**
	 * The mapper defining dataset relations.
	 *
	 * @var  array
	 * @since 5.1.3
	 */
	protected array $mapper = [];

	/**
	 * The base URL of the site.
	 *
	 * @var  string
	 * @since 5.1.3
	 */
	protected string $baseUrl = 'error';

	/**
	 * The return URL of the page.
	 *
	 * @var  string|null
	 * @since 5.1.3
	 */
	protected ?string $returnUrl = null;

	/**
	 * Default query parameters for image downloads.
	 *
	 * @var  array
	 * @since 5.1.3
	 */
	protected array $urlQueryParams = [
		'option'     => 'com_servicedirectory',
		'controller' => 'download',
		'task'       => 'download.image',
	];

	/**
	 * Escapable fields on the main item.
	 *
	 * @var  array
	 * @since 5.1.3
	 */
	protected array $mainEscapeFields = [];

	/**
	 * Process and update an item.
	 *
	 * @param  object       $item    The item to process.
	 *
	 * @return object  The processed and updated item.
	 * @since  5.1.3
	 */
	protected function processItem(object $item): object
	{
		$item = $this->buildItemLink($item);
		$item = $this->buildCategoryLinks($item);
		$item = $this->convertDescriptions($item);
		$item = $this->escapeFields($item, $this->mainEscapeFields);
		$item = $this->processLinkedDataSets($item, $this->mapper);

		return $item;
	}

	/**
	 * Build the item link if slug available.
	 *
	 * @param  object       $item    The item to modify.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function buildItemLink(object $item): object
	{
		$return = '';
		if (!empty($this->returnUrl))
		{
			$return = "&return={$this->returnUrl}";
		}

		if (!empty($item->slug))
		{
			$item->link = Route::_(
				RouteHelper::getListingRoute($item->slug) . $return
			);
		}

		if ($this->allowCompanyEdit($item))
		{
			$item->edit_link = Route::_(
				"/index.php?option=com_servicedirectory&view=company&task=company.edit&id={$item->id}{$return}"
			);
		}

		return $item;
	}

	/**
	 * Build the category route and link if available.
	 *
	 * @param  object  $item  The item to modify.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function buildCategoryLinks(object $item): object
	{
		if (!empty($item->category_id) && !empty($item->category_alias))
		{
			$item->category_slug = $item->category_id . ':' . $item->category_alias;
			$item->category_link = Route::_(
				RouteHelper::getCategoryRoute($item->category_slug)
			);
		}

		return $item;
	}

	/**
	 * Convert markdown fields to HTML.
	 *
	 * @param  object  $item  The item to modify.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function convertDescriptions(object $item): object
	{
		if (!empty($item->description))
		{
			$item->description = $this->convertMarkdownToHtml($item->description);
		}

		if (!empty($item->category_description))
		{
			$item->category_description = $this->convertMarkdownToHtml($item->category_description);
		}

		return $item;
	}

	/**
	 * Escape given fields safely.
	 *
	 * @param  object  $item     The item to escape fields on.
	 * @param  array   $fields   Field names to escape.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function escapeFields(object $item, array $fields): object
	{
		foreach ($fields as $field)
		{
			if (!empty($item->{$field}))
			{
				$item->{$field} = $this->escape((string) $item->{$field});
			}
		}

		return $item;
	}

	/**
	 * Process all linked datasets defined in the mapper.
	 *
	 * @param  object  $item     The item to update.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function processLinkedDataSets(object $item): object
	{
		foreach ($this->mapper as $pointer => $config)
		{
			$dataSet = $item->{$pointer} ?? null;

			if (!empty($dataSet))
			{
				$dataSet = $this->processDataSet($dataSet, $config, $item);
			}

			$setField = $config['set'] ?? null;
			if ($setField !== null)
			{
				$item->{$setField} = !empty($dataSet) ? $dataSet : null;
				unset($item->{$pointer});
			}
		}

		return $item;
	}

	/**
	 * Process a single dataset mapping.
	 *
	 * @param  array   $dataSet  The dataset to process.
	 * @param  array   $config   The mapper configuration for this dataset.
	 * @param  object  $item     The parent item for cross-field updates.
	 *
	 * @return array|null  The processed dataset or null.
	 * @since  5.1.3
	 */
	protected function processDataSet(array $dataSet, array $config, object &$item): ?array
	{
		foreach ($dataSet as $n => &$value)
		{
			$routeMethod = $config['route'] ?? null;
			$escapeSet = $config['escape'] ?? [];
			$set = $config['set'] ?? '';

			if ($routeMethod)
			{
				$value = $this->setLinkedEntityRoute($value, $routeMethod);
			}

			if (!empty($value->description))
			{
				$value->description = $this->convertMarkdownToHtml($value->description);
			}

			$value = $this->escapeFields($value, $escapeSet);

			if ($set === 'files')
			{
				$this->processFileRecord($value, $item, $dataSet, $n);
			}

			if ($set === 'social_handles')
			{
				if (!$this->processSocialHandle($value))
				{
					unset($dataSet[$n]);
				}
			}
		}
		unset($value);

		return !empty($dataSet) ? $dataSet : null;
	}

	/**
	 * Build route for linked entity.
	 *
	 * @param  object  $value        Entity object to update.
	 * @param  string  $routeMethod  Route method name.
	 *
	 * @return object
	 * @since  5.1.3
	 */
	protected function setLinkedEntityRoute(object $value, string $routeMethod): object
	{
		$value->slug = ($value->id ?? '0') . (isset($value->alias) ? ':' . $value->alias : '');
		$value->link = Route::_(
			RouteHelper::{$routeMethod}($value->slug)
		);

		return $value;
	}

	/**
	 * Process a single file entity, building download URLs.
	 *
	 * @param  object  $value     The file entity.
	 * @param  object  $item      The parent item.
	 * @param  array   &$dataSet  The dataset reference.
	 * @param  int     $n         Current index in dataset.
	 *
	 * @since  5.1.3
	 */
	protected function processFileRecord(object &$value, object &$item, array &$dataSet, int $n): void
	{
		if (empty($value->name))
		{
			return;
		}

		$query = $this->urlQueryParams;

		$query['file'] = $value->guid;
		$query['name'] = $value->name;

		$value->src = $this->baseUrl . Route::_(
			'index.php?' . http_build_query($query)
		);

		if (str_starts_with($value->name, 'logo__'))
		{
			$item->logo = $value->src;
			unset($dataSet[$n]);
		}

		if (str_starts_with($value->name, 'banner__'))
		{
			$item->banner = $value->src;
			unset($dataSet[$n]);
		}
	}

	/**
	 * Process and normalize social media handles or full profile URLs.
	 *
	 * - If the handle is a full URL (starting with http/https), normalize it, strip
	 *   the domain (leaving only the path), and use that as the clean handle.
	 * - If the handle is not a full URL, assume it's a raw handle and build a full
	 *   URL by combining the website and handle.
	 * - Always ensures consistent, lowercase, HTTPS-based URLs.
	 *
	 * @param  object  $value  The handle object to validate and clean.
	 *
	 * @return bool  True if valid and processed, false otherwise.
	 * @since  5.1.2
	 */
	protected function processSocialHandle(object &$value): bool
	{
		$handle  = trim((string) ($value->handle ?? ''));
		$website = trim((string) ($value->website ?? ''));

		// Both fields required
		if ($handle === '' || $website === '')
		{
			return false;
		}

		// If handle is already a full URL
		if (preg_match('#^https?://#i', $handle))
		{
			// Normalize to HTTPS and lowercase for consistency
			$link = preg_replace('#^http://#i', 'https://', strtolower($handle));
			$link = rtrim($link, '/');

			// Parse URL safely and extract the path portion as the clean handle
			$parsed = parse_url($link);
			$cleanHandle = '';

			if (!empty($parsed['path']))
			{
				$cleanHandle = ltrim($parsed['path'], '/');
			}

			if (!empty($parsed['query']))
			{
				// Include query string if present (e.g. ?id=123)
				$cleanHandle .= '?' . $parsed['query'];
			}

			$value->handle = $cleanHandle;
			$value->link   = $link;

			return true;
		}

		// Handle is plain text → build full link
		$cleanWebsite = rtrim(preg_replace('#^https?://#i', '', strtolower($website)), '/');
		$cleanHandle  = ltrim($handle, '/');

		$link = 'https://' . $cleanWebsite . '/' . $cleanHandle;
		$link = rtrim($link, '/');

		$value->handle = $cleanHandle;
		$value->link   = $link;

		return true;
	}

	/**
	 * Check whether the current user is allowed to edit a company record.
	 *
	 * This method validates both the global and ownership-based permissions
	 * for the provided company record, following Joomla's ACL structure.
	 *
	 * @param  object  $item  The company record to check permissions for.
	 *
	 * @return bool  True if editing is allowed, false otherwise.
	 * @since  5.1.3
	 */
	protected function allowCompanyEdit(object $item): bool
	{
		// Ensure we have a valid user and record ID
		if (empty($this->user) || empty($item->id))
		{
			return false;
		}

		$recordId = (int) $item->id;
		$ownerId  = isset($item->created_by) ? (int) $item->created_by : 0;

		// Check global edit permission for this specific company record
		if ($this->user->authorise('core.edit', 'com_servicedirectory.company.' . $recordId))
		{
			return true;
		}

		// Check edit own permissions at record level
		if ($this->user->authorise('core.edit.own', 'com_servicedirectory.company.' . $recordId))
		{
			// Only allow if current user is the record creator
			if ($ownerId === (int) $this->user->id)
			{
				// Also ensure the user has general edit own access in component scope
				return $this->user->authorise('core.edit.own', 'com_servicedirectory');
			}
		}

		return false;
	}

	/**
	 * Convert Markdown text to HTML.
	 *
	 * Uses the Super Power class responsible for Markdown-to-HTML conversion.
	 * The converter instance is cached statically to avoid repeated instantiations
	 * within the same request, improving overall performance.
	 *
	 * Example:
	 * ```php
	 * echo $this->convertMarkdownToHtml('# Hello World');
	 * ```
	 *
	 * @param  string  $string  The Markdown-formatted string to convert.
	 *
	 * @return string  The resulting HTML output or an empty string on failure.
	 * @since  5.1.2
	 */
	protected function convertMarkdownToHtml(string $string): string
	{
		$string = trim($string);
		if ($string === '')
		{
			return '';
		}

		static $Html = null;

		if ($Html === null)
		{
			$Html = new Html();
		}

		try
		{
			// Sanitize: remove all existing HTML to ensure only Markdown is processed
			$string = $this->escape($string);
		}
		catch (\Throwable $e)
		{
			return '';
		}

		if (trim($string) === '')
		{
			return '';
		}

		try
		{
			return $Html->convert($string);
		}
		catch (\Throwable $e)
		{
			// Fail gracefully, return empty string
			return '';
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var     The output to escape.
	 * @param   bool   $shorten The switch to shorten.
	 * @param   int    $length  The shorting length.
	 *
	 * @return  mixed  The escaped value.
	 * @since   5.1.2
	 */
	protected function escape($var, bool $shorten = false, int $length = 40)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}

	/**
	 * Get or regenerate a time-based random seed that stays stable for one minute.
	 *
	 * This ensures consistent random ordering within a short window (e.g. pagination)
	 * while automatically changing after one minute. The seed is stored in the Joomla
	 * session and automatically refreshed when expired.
	 *
	 * @param  string  $sessionKey  Optional unique key to allow multiple independent seeds.
	 *
	 * @return int  The current time-based random seed.
	 * @since  5.1.3
	 */
	protected function getTimeBasedRandomSeed(string $sessionKey = 'servicedirectory_random_seed'): int
	{
		// Get session object
		$session = Factory::getApplication()->getSession();

		// Retrieve the stored seed data
		$seedData = $session->get($sessionKey);

		// Current minute window (rounded down to the current minute)
		$currentMinute = (int) (time() / 60);

		// If no seed exists or it's expired, regenerate it
		if (empty($seedData) || !isset($seedData['time']) || $seedData['time'] < $currentMinute)
		{
			$seed = random_int(1, 999999);
			$session->set($sessionKey, [
				'seed' => $seed,
				'time' => $currentMinute
			]);

			return $seed;
		}

		// Return existing active seed
		return (int) $seedData['seed'];
	}
}
