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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Categories\Categories;
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Component Route Helper
 *
 * @since       1.5
 */
abstract class RouteHelper
{
	protected static $lookup;

	/**
	 * @param int The route of the Directory
	 */
	public static function getDirectoryRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'directory'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=directory&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'directory'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=directory';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.directory');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Companies
	 */
	public static function getCompaniesRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'companies'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=companies&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'companies'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=companies';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.companies');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Category
	 */
	public static function getCategoryRoute($id = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'category'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=category&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'category'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=category';
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Listing
	 */
	public static function getListingRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'listing'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=listing&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'listing'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=listing';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.listing');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Tag
	 */
	public static function getTagRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'tag'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=tag&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'tag'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=tag';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.tag');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Areaofexpertise
	 */
	public static function getAreaofexpertiseRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'areaofexpertise'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=areaofexpertise&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'areaofexpertise'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=areaofexpertise';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.areaofexpertise');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Lang
	 */
	public static function getLangRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'lang'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_servicedirectory&view=lang&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'lang'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_servicedirectory&view=lang';
		}
		if ($catid > 1)
		{
			$categories = Categories::getInstance('servicedirectory.lang');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	protected static function _findItem($needles = null,$type = null)
	{
		$app      = Factory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = [];

			$component  = ComponentHelper::getComponent('com_servicedirectory');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = [];
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					else
					{
						self::$lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					if (ArrayHelper::check($ids))
					{
						foreach ($ids as $id)
						{
							if (isset(self::$lookup[$language][$view][(int) $id]))
							{
								return self::$lookup[$language][$view][(int) $id];
							}
						}
					}
					elseif (isset(self::$lookup[$language][$view][0]))
					{
						return self::$lookup[$language][$view][0];
					}
				}
			}
		}

		if ($type)
		{
			// Check if the global menu item has been set.
			$params = ComponentHelper::getParams('com_servicedirectory');
			if ($item = $params->get($type.'_menu', 0))
			{
				return $item;
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active
			&& $active->component == 'com_servicedirectory'
			&& ($language == '*' || in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
