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
namespace JoomService\Component\Servicedirectory\Site\Helper;

// The power autoloader for this project (JPATH_SITE) area.
$power_autoloader = JPATH_SITE . '/components/com_servicedirectory/src/Helper/PowerloaderHelper.php';
if (file_exists($power_autoloader))
{
	require_once $power_autoloader;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules as AccessRules;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use JoomService\Joomla\Data\Factory as DataFactory;
use JoomService\Joomla\Utilities\Component\Helper;
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\StringHelper as UtilitiesStringHelper;
use JoomService\Joomla\Utilities\JsonHelper;
use JoomService\Joomla\Utilities\ObjectHelper;
use JoomService\Joomla\Utilities\FormHelper;
use JoomService\Joomla\Utilities\GetHelper;
use JoomService\Joomla\Servicedirectory\Utilities\Permitted\Actions;
use Joomla\CMS\User\User;
use Joomla\CMS\Table\User as TableUser;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory component helper
 *
 * @since  3.0
 */
abstract class ServicedirectoryHelper
{
	/**
	 * Composer Switch
	 *
	 * @var      array
	 */
	protected static $composer = [];

	/**
	 * The Main Active Language
	 *
	 * @var      string
	 */
	public static $langTag;


	/**
	 * Check whether the user is allowed to add or update an item.
	 *
	 * Enforces a per-user item limit while still allowing updates to existing
	 * records owned by the user.
	 *
	 * @param  User|null   $user  The current user.
	 * @param  array|null  $data  The submitted data (may include 'id' for updates).
	 *
	 * @return bool  True to allow the action, false otherwise.
	 * @since  5.1.4
	 */
	public static function allowAddListing(?User $user, ?array $data): bool
	{
		// User validation
		if (!$user || empty($user->id))
		{
			return false;
		}

		// Normalize incoming item ID
		$itemId = (isset($data['id']) && is_numeric($data['id']) && (int) $data['id'] > 0)
			? (int) $data['id']
			: null;

		// Fetch existing item IDs for user
		$existing = DataFactory::_('Data.Items')
			->table('company')
			->values([$user->id], 'created_by', 'id');

		// Normalize DB result to array
		if (!is_array($existing))
		{
			$existing = [];
		}

		// Clean + normalize to integers
		$existing = array_values(array_filter(array_map('intval', $existing)));

		// Count existing items
		$count = count($existing);

		// Fetch maximum allowed listings
		$max = (int) Helper::getParams('com_servicedirectory')
			->get('max_listings', 1);

		// Ensure sane configuration
		if ($max < 1)
		{
			return false;
		}

		// ---------- UPDATE MODE ----------
		// Item exists and belongs to user → always allow
		if ($itemId !== null && in_array($itemId, $existing, true))
		{
			return true;
		}

		// ---------- CREATE MODE ----------
		// No ID in request OR ID not owned by user = new item

		// Already at or above max → deny
		if ($count >= $max)
		{
			return false;
		}

		// Below max → allow new item
		return true;
	}


	// <<<=== Privacy integration with Joomla Privacy suite ===>>>

	/**
	 * Performs validation to determine if the data associated with a remove information request can be processed
	 *
	 * @param   PrivacyPlugin  $plugin  The plugin being processed
	 * @param   PrivacyRemovalStatus  $status  The status being set
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyRemovalStatus
	 */
	public static function onPrivacyCanRemoveData(&$plugin, &$status, &$request, &$user)
	{
		// Bucket to get all reasons why removal not allowed
		$reasons = array();
		// Check if user has permission to delete Companies
		if (!$user->authorise('core.delete', 'com_servicedirectory') && !$user->authorise('core.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_COMPANIES');
		}
		// Check if user has permission to delete Portfolios
		if (!$user->authorise('portfolio.delete', 'com_servicedirectory') && !$user->authorise('portfolio.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_PORTFOLIOS');
		}
		// Check if user has permission to delete Social Handles
		if (!$user->authorise('social_handle.delete', 'com_servicedirectory') && !$user->authorise('social_handle.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_SOCIAL_HANDLES');
		}
		// Check if user has permission to delete Files
		if (!$user->authorise('file.delete', 'com_servicedirectory') && !$user->authorise('file.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_FILES');
		}
		// Check if user has permission to delete Addresses
		if (!$user->authorise('address.delete', 'com_servicedirectory') && !$user->authorise('address.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_ADDRESSES');
		}
		// Check if user has permission to delete Company Languages
		if (!$user->authorise('company_language.delete', 'com_servicedirectory') && !$user->authorise('company_language.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_COMPANY_LANGUAGES');
		}
		// Check if user has permission to delete Company Tags
		if (!$user->authorise('company_tag.delete', 'com_servicedirectory') && !$user->authorise('company_tag.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_COMPANY_TAGS');
		}
		// Check if user has permission to delete Company Areas of Expertise
		if (!$user->authorise('company_area_of_expertise.delete', 'com_servicedirectory') && !$user->authorise('company_area_of_expertise.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_COMPANY_AREAS_OF_EXPERTISE');
		}
		// Check if user has permission to delete Review Company Updates
		if (!$user->authorise('core.delete', 'com_servicedirectory') && !$user->authorise('core.privacy.delete', 'com_servicedirectory'))
		{
			$reasons[] = Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_REVIEW_COMPANY_UPDATES');
		}
		// Check if any reasons were found not to allow removal
		if (UtilitiesArrayHelper::check($reasons))
		{
			$status->canRemove = false;
			$status->reason = implode(' ' . PHP_EOL, $reasons) . ' ' . PHP_EOL . Text::_('COM_SERVICEDIRECTORY_PRIVACY_CANT_REMOVE_CONTACT_SUPPORT');
		}
		return $status;
	}

	/**
	 * Processes an export request for Joomla core user data
	 *
	 * @param   PrivacyPlugin  $plugin  The plugin being processed
	 * @param   DomainArray  $domains  The array of domains
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                $user     The user account associated with this request if available
	 *
	 * @return  PrivacyExportDomain[]
	 */
	public static function onPrivacyExportRequest(&$plugin, &$domains, &$request, &$user)
	{
		// Check if user has permission to access Companies
		if ($user->authorise('company.access', 'com_servicedirectory') || $user->authorise('core.privacy.access', 'com_servicedirectory'))
		{
			// Get Company domain
			$domains[] = self::createCompaniesDomain($plugin, $user);
		}
		// Check if user has permission to access Portfolios
		if ($user->authorise('portfolio.access', 'com_servicedirectory') || $user->authorise('portfolio.privacy.access', 'com_servicedirectory'))
		{
			// Get Portfolio domain
			$domains[] = self::createPortfoliosDomain($plugin, $user);
		}
		// Check if user has permission to access Social Handles
		if ($user->authorise('social_handle.access', 'com_servicedirectory') || $user->authorise('social_handle.privacy.access', 'com_servicedirectory'))
		{
			// Get Social Handle domain
			$domains[] = self::createSocial_handlesDomain($plugin, $user);
		}
		// Check if user has permission to access Files
		if ($user->authorise('file.access', 'com_servicedirectory') || $user->authorise('file.privacy.access', 'com_servicedirectory'))
		{
			// Get File domain
			$domains[] = self::createFilesDomain($plugin, $user);
		}
		// Check if user has permission to access Addresses
		if ($user->authorise('address.access', 'com_servicedirectory') || $user->authorise('address.privacy.access', 'com_servicedirectory'))
		{
			// Get Address domain
			$domains[] = self::createAddressesDomain($plugin, $user);
		}
		// Check if user has permission to access Company Languages
		if ($user->authorise('company_language.access', 'com_servicedirectory') || $user->authorise('company_language.privacy.access', 'com_servicedirectory'))
		{
			// Get Company Language domain
			$domains[] = self::createCompany_languagesDomain($plugin, $user);
		}
		// Check if user has permission to access Company Tags
		if ($user->authorise('company_tag.access', 'com_servicedirectory') || $user->authorise('company_tag.privacy.access', 'com_servicedirectory'))
		{
			// Get Company Tag domain
			$domains[] = self::createCompany_tagsDomain($plugin, $user);
		}
		// Check if user has permission to access Company Areas of Expertise
		if ($user->authorise('company_area_of_expertise.access', 'com_servicedirectory') || $user->authorise('company_area_of_expertise.privacy.access', 'com_servicedirectory'))
		{
			// Get Company Area of Expertise domain
			$domains[] = self::createCompany_areas_of_expertiseDomain($plugin, $user);
		}
		// Check if user has permission to access Review Company Updates
		if ($user->authorise('review_company_update.access', 'com_servicedirectory') || $user->authorise('core.privacy.access', 'com_servicedirectory'))
		{
			// Get Review Company Update domain
			$domains[] = self::createReview_company_updatesDomain($plugin, $user);
		}
		return $domains;
	}

	/**
	 * Create the domain for the Company
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createCompaniesDomain(&$plugin, &$user)
	{
		// create Companies domain
		$domain = self::createDomain('company', 'servicedirectory_company_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Companies that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Companies domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Companies model
		$model = self::getModel('companies');
		// Get all item details of Companies that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Company default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Portfolio
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createPortfoliosDomain(&$plugin, &$user)
	{
		// create Portfolios domain
		$domain = self::createDomain('portfolio', 'servicedirectory_portfolio_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Portfolios that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_portfolio'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Portfolios domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Portfolios model
		$model = self::getModel('portfolios');
		// Get all item details of Portfolios that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Portfolio default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Social Handle
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createSocial_handlesDomain(&$plugin, &$user)
	{
		// create Social Handles domain
		$domain = self::createDomain('social_handle', 'servicedirectory_social_handle_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Social Handles that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_social_handle'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Social Handles domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Social Handles model
		$model = self::getModel('social_handles');
		// Get all item details of Social Handles that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Social Handle default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the File
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createFilesDomain(&$plugin, &$user)
	{
		// create Files domain
		$domain = self::createDomain('file', 'servicedirectory_file_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Files that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_file'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Files domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Files model
		$model = self::getModel('files');
		// Get all item details of Files that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove File default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Address
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createAddressesDomain(&$plugin, &$user)
	{
		// create Addresses domain
		$domain = self::createDomain('address', 'servicedirectory_address_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Addresses that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_address'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Addresses domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Addresses model
		$model = self::getModel('addresses');
		// Get all item details of Addresses that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Address default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Company Language
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createCompany_languagesDomain(&$plugin, &$user)
	{
		// create Company Languages domain
		$domain = self::createDomain('company_language', 'servicedirectory_company_language_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Languages that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_language'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Languages domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Company Languages model
		$model = self::getModel('company_languages');
		// Get all item details of Company Languages that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Company Language default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Company Tag
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createCompany_tagsDomain(&$plugin, &$user)
	{
		// create Company Tags domain
		$domain = self::createDomain('company_tag', 'servicedirectory_company_tag_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Tags that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_tag'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Tags domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Company Tags model
		$model = self::getModel('company_tags');
		// Get all item details of Company Tags that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Company Tag default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Company Area of Expertise
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createCompany_areas_of_expertiseDomain(&$plugin, &$user)
	{
		// create Company Areas of Expertise domain
		$domain = self::createDomain('company_area_of_expertise', 'servicedirectory_company_area_of_expertise_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Areas of Expertise that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_area_of_expertise'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Areas of Expertise domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Company Areas of Expertise model
		$model = self::getModel('company_areas_of_expertise');
		// Get all item details of Company Areas of Expertise that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Company Area of Expertise default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create the domain for the Review Company Update
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 */
	protected static function createReview_company_updatesDomain(&$plugin, &$user)
	{
		// create Review Company Updates domain
		$domain = self::createDomain('review_company_update', 'servicedirectory_review_company_update_data');
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Review Company Updates that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_review_company_update'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Review Company Updates domain
		$pks = $db->setQuery($query)->loadColumn();
		// get the Review Company Updates model
		$model = self::getModel('review_company_updates');
		// Get all item details of Review Company Updates that belong to this user
		$items = $model->getPrivacyExport($pks, $user);
		// check if we have items since permissions could block the request
		if (UtilitiesArrayHelper::check($items))
		{
			// Remove Review Company Update default columns
			foreach (array('params', 'asset_id', 'checked_out', 'checked_out_time', 'created', 'created_by', 'modified', 'modified_by', 'published', 'ordering', 'access', 'version', 'hits') as $column)
			{
				$items = ArrayHelper::dropColumn($items, $column);
			}
			// load the items into the domain object
			foreach ($items as $item)
			{
				$domain->addItem(self::createItemFromArray($item, $item['id']));
			}
		}
		return $domain;
	}

	/**
	 * Create a new domain object
	 *
	 * @param   string  $name         The domain's name
	 * @param   string  $description  The domain's description
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	protected static function createDomain($name, $description = '')
	{
		$domain              = new PrivacyExportDomain;
		$domain->name        = $name;
		$domain->description = $description;

		return $domain;
	}

	/**
	 * Create an item object for an array
	 *
	 * @param   array         $data    The array data to convert
	 * @param   integer|null  $itemId  The ID of this item
	 *
	 * @return  PrivacyExportItem
	 *
	 * @since   3.9.0
	 */
	protected static function createItemFromArray(array $data, $itemId = null)
	{
		$item = new PrivacyExportItem;
		$item->id = $itemId;

		foreach ($data as $key => $value)
		{
			if (is_object($value))
			{
				$value = (array) $value;
			}

			if (is_array($value))
			{
				$value = print_r($value, true);
			}

			$field        = new PrivacyExportField;
			$field->name  = $key;
			$field->value = $value;

			$item->addField($field);
		}

		return $item;
	}

	/**
	 * Removes the data associated with a remove information request
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 * @param   User                $user     The user account associated with this request if available
	 *
	 * @return  void
	 */
	public static function onPrivacyRemoveData(&$plugin, &$request, &$user)
	{
		// Check if user has permission to delet Companies
		if ($user->authorise('core.delete', 'com_servicedirectory') || $user->authorise('core.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Company data
			self::removeCompaniesData($plugin, $user);
		}
		// Check if user has permission to delet Portfolios
		if ($user->authorise('portfolio.delete', 'com_servicedirectory') || $user->authorise('portfolio.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Portfolio data
			self::removePortfoliosData($plugin, $user);
		}
		// Check if user has permission to delet Social Handles
		if ($user->authorise('social_handle.delete', 'com_servicedirectory') || $user->authorise('social_handle.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Social Handle data
			self::removeSocial_handlesData($plugin, $user);
		}
		// Check if user has permission to delet Files
		if ($user->authorise('file.delete', 'com_servicedirectory') || $user->authorise('file.privacy.delete', 'com_servicedirectory'))
		{
			// Remove File data
			self::removeFilesData($plugin, $user);
		}
		// Check if user has permission to delet Addresses
		if ($user->authorise('address.delete', 'com_servicedirectory') || $user->authorise('address.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Address data
			self::removeAddressesData($plugin, $user);
		}
		// Check if user has permission to delet Company Languages
		if ($user->authorise('company_language.delete', 'com_servicedirectory') || $user->authorise('company_language.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Company Language data
			self::removeCompany_languagesData($plugin, $user);
		}
		// Check if user has permission to delet Company Tags
		if ($user->authorise('company_tag.delete', 'com_servicedirectory') || $user->authorise('company_tag.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Company Tag data
			self::removeCompany_tagsData($plugin, $user);
		}
		// Check if user has permission to delet Company Areas of Expertise
		if ($user->authorise('company_area_of_expertise.delete', 'com_servicedirectory') || $user->authorise('company_area_of_expertise.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Company Area of Expertise data
			self::removeCompany_areas_of_expertiseData($plugin, $user);
		}
		// Check if user has permission to delet Review Company Updates
		if ($user->authorise('core.delete', 'com_servicedirectory') || $user->authorise('core.privacy.delete', 'com_servicedirectory'))
		{
			// Remove Review Company Update data
			self::removeReview_company_updatesData($plugin, $user);
		}
	}

	/**
	 * Remove the Company data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeCompaniesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Companies that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Companies table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the company model
			$model = self::getModel('company');
			// get the Companies table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Portfolio data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removePortfoliosData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Portfolios that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_portfolio'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Portfolios table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the portfolio model
			$model = self::getModel('portfolio');
			// get the Portfolios table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Social Handle data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeSocial_handlesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Social Handles that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_social_handle'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Social Handles table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the social_handle model
			$model = self::getModel('social_handle');
			// get the Social Handles table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the File data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeFilesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Files that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_file'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Files table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the file model
			$model = self::getModel('file');
			// get the Files table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Address data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeAddressesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Addresses that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_address'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Addresses table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the address model
			$model = self::getModel('address');
			// get the Addresses table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Company Language data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeCompany_languagesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Languages that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_language'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Languages table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the company_language model
			$model = self::getModel('company_language');
			// get the Company Languages table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Company Tag data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeCompany_tagsData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Tags that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_tag'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Tags table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the company_tag model
			$model = self::getModel('company_tag');
			// get the Company Tags table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Company Area of Expertise data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeCompany_areas_of_expertiseData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Company Areas of Expertise that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_company_area_of_expertise'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Company Areas of Expertise table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the company_area_of_expertise model
			$model = self::getModel('company_area_of_expertise');
			// get the Company Areas of Expertise table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}

	/**
	 * Remove the Review Company Update data
	 *
	 * @param   TableUser  $user  The TableUser object to process
	 *
	 * @return  void
	 */
	protected static function removeReview_company_updatesData(&$plugin, &$user)
	{
		// get database object
		$db = Factory::getDbo();
		// get all item ids of Review Company Updates that belong to this user
		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__servicedirectory_review_company_update'));
		$query->where($db->quoteName('created_by') . ' = ' . $db->quote($user->id));
		// get all items for the Review Company Updates table that belong to this user
		$pks = $db->setQuery($query)->loadColumn();

		if (UtilitiesArrayHelper::check($pks))
		{
			// get the review_company_update model
			$model = self::getModel('review_company_update');
			// get the Review Company Updates table
			$table = $model->getTable();
			// Iterate the items to delete each one.
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk))
				{
					$table->delete($pk);
				}
			}
		}
	}


	/**
	 * Load the Composer Vendors
	 */
	public static function composerAutoload($target)
	{
		// insure we load the composer vendor only once
		if (!isset(self::$composer[$target]))
		{
			// get the function name
			$functionName = UtilitiesStringHelper::safe('compose' . $target);
			// check if method exist
			if (method_exists(__CLASS__, $functionName))
			{
				return self::{$functionName}();
			}
			return false;
		}
		return self::$composer[$target];
	}

	/**
	 * Convert a json object to a string
	 *
	 * @input    string  $value  The json string to convert
	 *
	 * @returns a string
	 * @deprecated 3.3 Use JsonHelper::string(...);
	 */
	public static function jsonToString($value, $sperator = ", ", $table = null, $id = 'id', $name = 'name')
	{
		return JsonHelper::string(
			$value,
			$sperator,
			$table,
			$id,
			$name
		);
	}

	/**
	 * Load the Component xml manifest.
	 */
	public static function manifest()
	{
		$manifestUrl = JPATH_ADMINISTRATOR."/components/com_servicedirectory/servicedirectory.xml";
		return simplexml_load_file($manifestUrl);
	}

	/**
	 * Joomla version object
	 */
	protected static $JVersion;

	/**
	 * set/get Joomla version
	 */
	public static function jVersion()
	{
		// check if set
		if (!ObjectHelper::check(self::$JVersion))
		{
			self::$JVersion = new Version();
		}
		return self::$JVersion;
	}

	/**
	 * Load the Contributors details.
	 */
	public static function getContributors()
	{
		// get params
		$params    = ComponentHelper::getParams('com_servicedirectory');
		// start contributors array
		$contributors = [];
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
		{
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 2 || $params->get("showContributor".$nr) == 3))
			{
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
				{
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				elseif($params->get("useContributor".$nr) == 2)
				{
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
				else
				{
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title'] = UtilitiesStringHelper::html($params->get("titleContributor".$nr));
				$contributors[$nr]['name']  = $link_front.UtilitiesStringHelper::html($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}

	/**
	 *	Can be used to build help urls.
	 **/
	public static function getHelpUrl($view)
	{
		return false;
	}

	/**
	 * Returns any Model object.
	 *
	 * @param   string  $type       The model type to instantiate
	 * @param   string  $prefix     Prefix for the model class name. Optional.
	 * @param   string  $component  Component name the model belongs to. Optional.
	 * @param   array   $config     Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @throws \Exception
	 * @since   4.4
	 */
	public static function getModel(string $type, string $prefix = 'Site',
		string $component = 'servicedirectory', array $config = [])
	{
		// make sure the name is correct
		$type = UtilitiesStringHelper::safe($type, 'F');
		$component = strtolower($component);

		if ($prefix !== 'Site' && $prefix !== 'Administrator')
		{
			$prefix = self::getPrefixFromModelPath($prefix);
		}

		// Get the model through the MVCFactory
		return Factory::getApplication()->bootComponent('com_' . $component)->getMVCFactory()->createModel($type, $prefix, $config);
	}

	/**
	 * Get the prefix from the model path
	 *
	 * @param   string  $path    The model path
	 *
	 * @return  string  The prefix value
	 * @since    4.4
	 */
	protected static function getPrefixFromModelPath(string $path): string
	{
		// Check if $path starts with JPATH_ADMINISTRATOR path
		if (str_starts_with($path, JPATH_ADMINISTRATOR . '/components/'))
		{
			return 'Administrator';
		}
		// Check if $path starts with JPATH_SITE path
		elseif (str_starts_with($path, JPATH_SITE . '/components/'))
		{
			return 'Site';
		}

		return 'Site';
	}

	/**
	 * Add to asset Table
	 */
	public static function setAsset($id, $table, $inherit = true)
	{
		$parent = Table::getInstance('Asset');
		$parent->loadByName('com_servicedirectory');

		$parentId = $parent->id;
		$name     = 'com_servicedirectory.'.$table.'.'.$id;
		$title    = '';

		$asset = Table::getInstance('Asset');
		$asset->loadByName($name);

		// Check for an error.
		$error = $asset->getError();

		if ($error)
		{
			return false;
		}
		else
		{
			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;
			// get the default asset rules
			$rules = self::getDefaultAssetRules('com_servicedirectory', $table, $inherit);
			if ($rules instanceof AccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				Factory::getApplication()->enqueueMessage($asset->getError(), 'warning');
				return false;
			}
			else
			{
				// Create an asset_id or heal one that is corrupted.
				$object = new \StdClass();

				// Must be a valid primary key value.
				$object->id = $id;
				$object->asset_id = (int) $asset->id;

				// Update their asset_id to link to the asset table.
				return Factory::getDbo()->updateObject('#__servicedirectory_'.$table, $object, 'id');
			}
		}
		return false;
	}

	/**
	 * Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component, $view, $inherit = true)
	{
		// if new or inherited
		$assetId = 0;
		// Only get the actual item rules if not inheriting
		if (!$inherit)
		{
			// Need to find the asset id by the name of the component.
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . ' = ' . $db->quote($component));
			$db->setQuery($query);
			$db->execute();
			// check that there is a value
			if ($db->getNumRows())
			{
				// asset already set so use saved rules
				$assetId = (int) $db->loadResult();
			}
		}
		// get asset rules
		$result =  Access::getAssetRules($assetId);
		if ($result instanceof AccessRules)
		{
			$_result = (string) $result;
			$_result = json_decode($_result);
			foreach ($_result as $name => &$rule)
			{
				$v = explode('.', $name);
				if ($view !== $v[0])
				{
					// remove since it is not part of this view
					unset($_result->$name);
				}
				elseif ($inherit)
				{
					// clear the value since we inherit
					$rule = [];
				}
			}
			// check if there are any view values remaining
			if (count((array) $_result))
			{
				$_result = json_encode($_result);
				$_result = array($_result);
				// Instantiate and return the AccessRules object for the asset rules.
				$rules = new AccessRules($_result);
				// return filtered rules
				return $rules;
			}
		}
		return $result;
	}

	/**
	 * xmlAppend
	 *
	 * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
	 * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::append($xml, $node);
	 */
	public static function xmlAppend(&$xml, $node)
	{
		FormHelper::append($xml, $node);
	}

	/**
	 * xmlComment
	 *
	 * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
	 * @param   string             $comment    The comment to inject
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::comment($xml, $comment);
	 */
	public static function xmlComment(&$xml, $comment)
	{
		FormHelper::comment($xml, $comment);
	}

	/**
	 * xmlAddAttributes
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $attributes   The attributes to apply to the XML element
	 *
	 * @return  null
	 * @deprecated 3.3 Use FormHelper::attributes($xml, $attributes);
	 */
	public static function xmlAddAttributes(&$xml, $attributes = [])
	{
		FormHelper::attributes($xml, $attributes);
	}

	/**
	 * xmlAddOptions
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $options      The options to apply to the XML element
	 *
	 * @return  void
	 * @deprecated 3.3 Use FormHelper::options($xml, $options);
	 */
	public static function xmlAddOptions(&$xml, $options = [])
	{
		FormHelper::options($xml, $options);
	}

	/**
	 * get the field object
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   string     $default      The default of the field
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use FormHelper::field($attributes, $default, $options);
	 */
	public static function getFieldObject(&$attributes, $default = '', $options = null)
	{
		return FormHelper::field($attributes, $default, $options);
	}

	/**
	 * get the field xml
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 * @deprecated 3.3 Use FormHelper::xml($attributes, $options);
	 */
	public static function getFieldXML(&$attributes, $options = null)
	{
		return FormHelper::xml($attributes, $options);
	}

	/**
	 * Render Bool Button
	 *
	 * @param   array   $args   All the args for the button
	 *                             0) name
	 *                             1) additional (options class) // not used at this time
	 *                             2) default
	 *                             3) yes (name)
	 *                             4) no (name)
	 *
	 * @return  string    The input html of the button
	 *
	 */
	public static function renderBoolButton()
	{
		$args = func_get_args();
		// check if there is additional button class
		$additional = isset($args[1]) ? (string) $args[1] : ''; // not used at this time
		// button attributes
		$buttonAttributes = array(
			'type' => 'radio',
			'name' => isset($args[0]) ? UtilitiesStringHelper::html($args[0]) : 'bool_button',
			'label' => isset($args[0]) ? UtilitiesStringHelper::safe(UtilitiesStringHelper::html($args[0]), 'Ww') : 'Bool Button', // not seen anyway
			'class' => 'btn-group',
			'filter' => 'INT',
			'default' => isset($args[2]) ? (int) $args[2] : 0);
		// set the button options
		$buttonOptions = array(
			'1' => isset($args[3]) ? UtilitiesStringHelper::html($args[3]) : 'JYES',
			'0' => isset($args[4]) ? UtilitiesStringHelper::html($args[4]) : 'JNO');
		// return the input
		return FormHelper::field($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
	}

	/**
	 * Get a variable
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  mix string/int/float
	 * @deprecated 3.3 Use GetHelper::var(...);
	 */
	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'servicedirectory')
	{
		return GetHelper::var(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main
		);
	}

	/**
	 * Get array of variables
	 *
	 * @param   string   $table        The table from which to get the variables
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 * @param   bool     $unique       The switch to return a unique array
	 *
	 * @return  array
	 * @deprecated 3.3 Use GetHelper::vars(...);
	 */
	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'servicedirectory', $unique = true)
	{
		return GetHelper::vars(
			$table,
			$where,
			$whereString,
			$what,
			$operator,
			$main,
			$unique
		);
	}

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
		{
			$type = 'item';
		}
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select(array('a.published'));
		$query->from('#__servicedirectory_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return $db->loadResult();
		}
		return $id;
	}

	/**
	 * Get the permitted actions of a user.
	 *
	 * @param  string   $view        The related view name
	 * @param  ?object  $record      The item to act upon
	 * @param  ?string  $views       The related list view name
	 * @param  mixed    $target      Only get this permission (like edit, create, delete)
	 * @param  string   $component   The target component
	 * @param  object   $user        The user whose permissions we are loading
	 *
	 * @return  object   The Registry of permission/authorised actions
	 * @since   2.5.0
	 *
	 * @deprecated 5.1.4 Use Actions::get(...);
	 */
	public static function getActions($view, &$record = null, $views = null, $target = null, $component = 'servicedirectory', $user = 'null')
	{
		return Actions::get($view, $record, $views, $target, $component, $user);
	}

	/**
	 * Check if have an json string
	 *
	 * @input    string   The json string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use JsonHelper::check($string);
	 */
	public static function checkJson($string)
	{
		return JsonHelper::check($string);
	}

	/**
	 * Check if have an object with a length
	 *
	 * @input    object   The object to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use ObjectHelper::check($object);
	 */
	public static function checkObject($object)
	{
		return ObjectHelper::check($object);
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input    array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 * @deprecated 3.3 Use UtilitiesArrayHelper::check($array, $removeEmptyString);
	 */
	public static function checkArray($array, $removeEmptyString = false)
	{
		return UtilitiesArrayHelper::check($array, $removeEmptyString);
	}

	/**
	 * Check if have a string with a length
	 *
	 * @input    string   The string to check
	 *
	 * @returns bool true on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::check($string);
	 */
	public static function checkString($string)
	{
		return UtilitiesStringHelper::check($string);
	}

	/**
	 * Check if we are connected
	 * Thanks https://stackoverflow.com/a/4860432/1429677
	 *
	 * @returns bool true on success
	 */
	public static function isConnected()
	{
		// If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
		$connected = @fsockopen("www.example.com", 80);
		// website, port  (try 80 or 443)
		if ($connected)
		{
			//action when connected
			$is_conn = true;
			fclose($connected);
		}
		else
		{
			//action in connection failure
			$is_conn = false;
		}
		return $is_conn;
	}

	/**
	 * Merge an array of array's
	 *
	 * @input    array   The arrays you would like to merge
	 *
	 * @returns array on success
	 * @deprecated 3.3 Use UtilitiesArrayHelper::merge($arrays);
	 */
	public static function mergeArrays($arrays)
	{
		return UtilitiesArrayHelper::merge($arrays);
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	/**
	 * Shorten a string
	 *
	 * @input    string   The you would like to shorten
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::shorten(...);
	 */
	public static function shorten($string, $length = 40, $addTip = true)
	{
		return UtilitiesStringHelper::shorten($string, $length, $addTip);
	}

	/**
	 * Making strings safe (various ways)
	 *
	 * @input    string   The you would like to make safe
	 *
	 * @returns string on success
	 * @deprecated 3.3 Use UtilitiesStringHelper::safe(...);
	 */
	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true, $keepOnlyCharacters = true)
	{
		return UtilitiesStringHelper::safe(
			$string,
			$type,
			$spacer,
			$replaceNumbers,
			$keepOnlyCharacters
		);
	}

	/**
	 * Convert none English strings to code usable string
	 *
	 * @input    an string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::transliterate($string);
	 */
	public static function transliterate($string)
	{
		return UtilitiesStringHelper::transliterate($string);
	}

	/**
	 * make sure a string is HTML save
	 *
	 * @input    an html string
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::html(...);
	 */
	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		return UtilitiesStringHelper::html(
			$var,
			$charset,
			$shorten,
			$length
		);
	}

	/**
	 * Convert all int in a string to an English word string
	 *
	 * @input    an string with numbers
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::numbers($string);
	 */
	public static function replaceNumbers($string)
	{
		return UtilitiesStringHelper::numbers($string);
	}

	/**
	 * Convert an integer into an English word string
	 * Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	 *
	 * @input    an int
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::number($x);
	 */
	public static function numberToString($x)
	{
		return UtilitiesStringHelper::number($x);
	}

	/**
	 * Random Key
	 *
	 * @returns a string
	 * @deprecated 3.3 Use UtilitiesStringHelper::random($size);
	 */
	public static function randomkey($size)
	{
		return UtilitiesStringHelper::random($size);
	}
}
