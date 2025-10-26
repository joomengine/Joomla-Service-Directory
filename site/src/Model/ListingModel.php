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
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Site\Helper\RouteHelper;
use Joomla\CMS\Helper\TagsHelper;
use JoomService\Joomla\Utilities\JsonHelper;
use JoomService\Joomla\Servicedirectory\Markdown\Html;
use JoomService\Joomla\Utilities\StringHelper;
use Joomla\CMS\Event\Content\ContentPrepareEvent;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Listing Item Model
 *
 * @since  1.6
 */
class ListingModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $_context = 'com_servicedirectory.listing';

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
		'components/com_servicedirectory/assets/css/listing.css'
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
	 * A custom property for UI Kit components.
	 *
	 * @var   array|null  Property for storing UI Kit component-related data or objects.
	 * @since 3.2.0
	 */
	protected ?array $uikitComp;

	/**
	 * @var     object item
	 * @since   1.6
	 */
	protected $item;

	/**
	 * Constructor
	 *
	 * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   ?MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   3.0
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
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function populateState()
	{
		// Get the itme main id
		$id = $this->input->getInt('id', null);
		$this->setState('listing.id', $id);

		// Load the parameters.
		$params = $this->app->getParams();
		$this->setState('params', $params);

		parent::populateState();
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// check if this user has permission to access item
		if (!$this->user->authorise('site.listing.access', 'com_servicedirectory'))
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_SERVICEDIRECTORY_NOT_AUTHORISED_TO_VIEW_LISTING'), 'error');
			// redirect away to the default view if no access allowed.
			$app->redirect(Route::_('index.php?option=com_servicedirectory&view=directory'));
			return false;
		}

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('listing.id');

		if ($this->_item === null)
		{
			$this->_item = [];
		}

		if (!isset($this->_item[$pk]))
		{
			try
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
				$query->where('a.id = ' . (int) $pk);
				// Get where a.published is 1
				$query->where('a.published = 1');
				// Get where cc.published is 1
		$query->where('cc.published = 1');

				// Reset the query using our newly populated query object.
				$db->setQuery($query);
				// Load the results as a stdClass object.
				$data = $db->loadObject();

				if (empty($data))
				{
					$app = Factory::getApplication();
					// If no data is found redirect to default page and show warning.
					$app->enqueueMessage(Text::_('COM_SERVICEDIRECTORY_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
					$app->redirect(Route::_('index.php?option=com_servicedirectory&view=directory'));
					return false;
				}
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
				// Check if item has params, or pass whole item.
				$params = (isset($data->params) && JsonHelper::check($data->params)) ? json_decode($data->params) : $data;
				// Make sure the content prepare plugins fire on category_description
				$_category_description = new \stdClass();
				$_category_description->text =& $data->category_description; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (category_description) to context
				// onContentPrepare Event Trigger
				$this->getDispatcher()->dispatch('onContentPrepare',
					new ContentPrepareEvent(
						'onContentPrepare',
						[
							'context' => 'com_servicedirectory.listing.category_description',
							'subject' => $_category_description,
							'params' => $params,
							'page' => 0
						]
					)
				);
				// Check if item has params, or pass whole item.
				$params = (isset($data->params) && JsonHelper::check($data->params)) ? json_decode($data->params) : $data;
				// Make sure the content prepare plugins fire on description
				$_description = new \stdClass();
				$_description->text =& $data->description; // value must be in text
				// Since all values are now in text (Joomla Limitation), we also add the field name (description) to context
				// onContentPrepare Event Trigger
				$this->getDispatcher()->dispatch('onContentPrepare',
					new ContentPrepareEvent(
						'onContentPrepare',
						[
							'context' => 'com_servicedirectory.listing.description',
							'subject' => $_description,
							'params' => $params,
							'page' => 0
						]
					)
				);
				// set guidCompanyCompany_languageL to the $data object.
				$data->guidCompanyCompany_languageL = $this->getGuidCompanyCompany_languageDaec_L($data->guid);
				// set guidCompanyCompany_tagT to the $data object.
				$data->guidCompanyCompany_tagT = $this->getGuidCompanyCompany_tagDaec_T($data->guid);
				// set guidCompanyCompany_area_of_expertiseEE to the $data object.
				$data->guidCompanyCompany_area_of_expertiseEE = $this->getGuidCompanyCompany_area_of_expertiseDaec_EE($data->guid);
				// set guidCompanySocial_handleS to the $data object.
				$data->guidCompanySocial_handleS = $this->getGuidCompanySocial_handleDaec_S($data->guid);
				// set guidCompanyAddressHH to the $data object.
				$data->guidCompanyAddressHH = $this->getGuidCompanyAddressDaec_HH($data->guid);
				// set guidCompanyPortfolioQQ to the $data object.
				$data->guidCompanyPortfolioQQ = $this->getGuidCompanyPortfolioDaec_QQ($data->guid);
				// set guidEntityFileYY to the $data object.
				$data->guidEntityFileYY = $this->getGuidEntityFileDaec_YY($data->guid);

				// set data object to item.
				$this->_item[$pk] = $data;
			}
			catch (\Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					throw $e;
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		if (!empty($this->_item[$pk]))
		{
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
				],
				'guidCompanySocial_handleS' => [
					'route' => null,
					'set' => 'social_handles',
					'escape' => ['name', 'handle']
				],
				'guidCompanyAddressHH' => [
					'route' => null,
					'set' => 'addresses',
					'escape' => ['line_one', 'line_two', 'postal', 'type', 'country', 'state', 'city']
				],
				'guidCompanyPortfolioQQ' => [
					'route' => null,
					'set' => 'portfolios',
					'escape' => ['client_name', 'target_industry', 'services_provided', 'project_url']
				],
				'guidEntityFileYY' => [
					'route' => null,
					'set' => 'files'
				]
			];

			$this->mainEscapeFields = [
				'name', 'contactname', 'contactname', 'email', 'phone', 'website',
				'chamber_of_commerce', 'company_type', 'companysize', 'category_name'
			];

			$this->baseUrl = rtrim(Uri::root(), '/');
			$this->returnUrl = urlencode(base64_encode((string) Uri::getInstance()));

			$this->_item[$pk] = $this->processItem($this->_item[$pk]);
		}

		return $this->_item[$pk];
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
	public function getGuidCompanyCompany_languageDaec_L($guid)
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
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
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
	public function getGuidCompanyCompany_tagDaec_T($guid)
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
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
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
	public function getGuidCompanyCompany_area_of_expertiseDaec_EE($guid)
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
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			return $db->loadObjectList();
		}
		return false;
	}

	/**
	 * Method to get an array of Social_handle Objects.
	 *
	 * @return mixed  An array of Social_handle Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanySocial_handleDaec_S($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_social_handle as s
		$query->select($db->quoteName(
			array('s.platform','s.handle'),
			array('guid','handle')));
		$query->from($db->quoteName('#__servicedirectory_social_handle', 's'));
		$query->where('s.company = ' . $db->quote($guid));

				// Get from #__servicedirectory_platform as p
				$query->select($db->quoteName(
			array('p.id','p.name','p.website'),
			array('id','name','website')));
				$query->join('LEFT', ($db->quoteName('#__servicedirectory_platform', 'p')) . ' ON (' . $db->quoteName('s.platform') . ' = ' . $db->quoteName('p.guid') . ')');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			return $db->loadObjectList();
		}
		return false;
	}

	/**
	 * Method to get an array of Address Objects.
	 *
	 * @return mixed  An array of Address Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanyAddressDaec_HH($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_address as hh
		$query->select($db->quoteName(
			array('hh.line_one','hh.line_two','hh.postal'),
			array('line_one','line_two','postal')));
		$query->from($db->quoteName('#__servicedirectory_address', 'hh'));
		$query->where('hh.company = ' . $db->quote($guid));

				// Get from #__servicedirectory_address_type as jj
				$query->select($db->quoteName(
			array('jj.name'),
			array('type')));
				$query->join('LEFT', ($db->quoteName('#__servicedirectory_address_type', 'jj')) . ' ON (' . $db->quoteName('hh.type') . ' = ' . $db->quoteName('jj.guid') . ')');

				// Get from #__servicedirectory_country as ii
				$query->select($db->quoteName(
			array('ii.name'),
			array('country')));
				$query->join('LEFT', ($db->quoteName('#__servicedirectory_country', 'ii')) . ' ON (' . $db->quoteName('hh.country') . ' = ' . $db->quoteName('ii.guid') . ')');

				// Get from #__servicedirectory_state as kk
				$query->select($db->quoteName(
			array('kk.name'),
			array('state')));
				$query->join('LEFT', ($db->quoteName('#__servicedirectory_state', 'kk')) . ' ON (' . $db->quoteName('hh.state') . ' = ' . $db->quoteName('kk.guid') . ')');

				// Get from #__servicedirectory_city as uu
				$query->select($db->quoteName(
			array('uu.name'),
			array('city')));
				$query->join('LEFT', ($db->quoteName('#__servicedirectory_city', 'uu')) . ' ON (' . $db->quoteName('hh.city') . ' = ' . $db->quoteName('uu.guid') . ')');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			return $db->loadObjectList();
		}
		return false;
	}

	/**
	 * Method to get an array of Portfolio Objects.
	 *
	 * @return mixed  An array of Portfolio Objects on success, false on failure.
	 *
	 */
	public function getGuidCompanyPortfolioDaec_QQ($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_portfolio as qq
		$query->select($db->quoteName(
			array('qq.client_name','qq.target_industry','qq.services_provided','qq.project_url','qq.description','qq.project_title'),
			array('client_name','target_industry','services_provided','project_url','description','project_title')));
		$query->from($db->quoteName('#__servicedirectory_portfolio', 'qq'));
		$query->where('qq.company = ' . $db->quote($guid));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			$items = $db->loadObjectList();

			// Convert the parameter fields into objects.
			foreach ($items as $nr => &$item)
			{
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
							'context' => 'com_servicedirectory.listing.description',
							'subject' => $_description,
							'params' => $params,
							'page' => 0
						]
					)
				);
			}
			return $items;
		}
		return false;
	}

	/**
	 * Method to get an array of File Objects.
	 *
	 * @return mixed  An array of File Objects on success, false on failure.
	 *
	 */
	public function getGuidEntityFileDaec_YY($guid)
	{
		// Get a db connection.
		$db = $this->getDatabase();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__servicedirectory_file as yy
		$query->select($db->quoteName(
			array('yy.name','yy.size','yy.mime','yy.file_type','yy.extension','yy.file_path','yy.entity_type','yy.entity','yy.guid'),
			array('name','size','mime','file_type','extension','file_path','entity_type','entity','guid')));
		$query->from($db->quoteName('#__servicedirectory_file', 'yy'));
		$query->where('yy.entity = ' . $db->quote($guid));
				$query->where('yy.access IN (' . implode(',', $this->levels) . ')');
		// Get where yy.published is 1
		$query->where('yy.published = 1');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		$db->execute();

		// check if there was data returned
		if ($db->getNumRows())
		{
			// Load the Event Dispatcher
			PluginHelper::importPlugin('content');
			return $db->loadObjectList();
		}
		return false;
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
