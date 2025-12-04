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
namespace JoomService\Component\Servicedirectory\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Component Route Helper
 *
 * @since       1.5
 */
abstract class RouteHelper
{
	/**
	 * Registry to hold the servicedirectory params
	 *
	 * @var    Registry
	 * @since  5.1.3
	 */
	protected static Registry $params;

	/**
	 * Get the URL route for directory
	 *
	 * @param   integer  $id     The id of the directory
	 *
	 * @return  string  The link to the directory
	 *
	 * @since   1.5
	 */
	public static function getDirectoryRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=directory&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=directory';
		}

		return $link;
	}

	/**
	 * Get the URL route for companies
	 *
	 * @param   integer  $id     The id of the companies
	 *
	 * @return  string  The link to the companies
	 *
	 * @since   1.5
	 */
	public static function getCompaniesRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=companies&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=companies';
		}

		return $link;
	}

	/**
	 * Get the URL route for category
	 *
	 * @param   integer  $id     The id of the category
	 *
	 * @return  string  The link to the category
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=category&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=category';
		}

		return $link;
	}

	/**
	 * Get the URL route for listing
	 *
	 * @param   integer  $id     The id of the listing
	 *
	 * @return  string  The link to the listing
	 *
	 * @since   1.5
	 */
	public static function getListingRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=listing&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=listing';
		}

		return $link;
	}

	/**
	 * Get the URL route for tag
	 *
	 * @param   integer  $id     The id of the tag
	 *
	 * @return  string  The link to the tag
	 *
	 * @since   1.5
	 */
	public static function getTagRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=tag&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=tag';
		}

		return $link;
	}

	/**
	 * Get the URL route for areaofexpertise
	 *
	 * @param   integer  $id     The id of the areaofexpertise
	 *
	 * @return  string  The link to the areaofexpertise
	 *
	 * @since   1.5
	 */
	public static function getAreaofexpertiseRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=areaofexpertise&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=areaofexpertise';
		}

		return $link;
	}

	/**
	 * Get the URL route for lang
	 *
	 * @param   integer  $id     The id of the lang
	 *
	 * @return  string  The link to the lang
	 *
	 * @since   1.5
	 */
	public static function getLangRoute($id = 0): string
	{
		if ($id > 0)
		{
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=lang&id='. $id;
		}
		else
		{
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=lang';
		}

		return $link;
	}

	/**
	 * Retrieve a legacy-configured menu item override.
	 *
	 * This method is preserved for backward compatibility with older
	 * JCB-generated components where menu item overrides could be defined
	 * in the component's **global Options** panel. Administrators were able
	 * to add menu-item selector fields under the same tab name as the
	 * related entity/view type, using the naming convention:
	 *
	 *     {type}_menu
	 *
	 * Example:
	 *   - A field named `tag_menu` allowed administrators to force all tag
	 *     routing to use a specific menu item.
	 *
	 * These overrides served as a convenience mechanism for redirecting
	 * routing behaviour *without* modifying the router code.
	 *
	 * Joomla 5's recommended pattern now is to implement all routing
	 * decisions directly inside the router class. This method therefore
	 * remains solely as a **legacy fallback**, ensuring older sites continue
	 * functioning during migrations or long-term upgrade paths.
	 *
	 * If a matching `{type}_menu` parameter exists and contains a valid
	 * menu item ID (>0), that ID is returned. Otherwise, `null` is returned.
	 *
	 * @param  string  $type  The entity/view type whose `{type}_menu`
	 *                        override should be checked.
	 *
	 * @return int|null  The overridden menu item ID if available, otherwise null.
	 * @since   5.1.3
	 */
	protected static function _findItem(string $type): ?int
	{
		// Lazy-load the component parameters only once.
		self::$params ??= ComponentHelper::getParams('com_servicedirectory');

		// Read the legacy override (0 means "not set").
		$override = (int) self::$params->get($type . '_menu', 0);

		return $override > 0 ? $override : null;
	}
}
