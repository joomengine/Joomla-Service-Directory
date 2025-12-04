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
namespace JoomService\Component\Servicedirectory\Site\Service;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Router class for the Service Directory Component
 *
 * @since  3.10
 */
class Router extends RouterView
{
	/**
	 * Flag to remove IDs
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $noIDs = false;

	/**
	 * The category factory
	 *
	 * @var    CategoryFactoryInterface
	 * @since  4.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $categoryCache = [];

	/**
	 * The db
	 *
	 * @var    DatabaseInterface
	 * @since  4.0.0
	 */
	private $db;

	/**
	 * The component params
	 *
	 * @var    Registry
	 * @since  4.0.0
	 */
	private $params;

	/**
	 * Servicedirectory Component router constructor
	 *
	 * @param   SiteApplication           $app               The application object
	 * @param   AbstractMenu              $menu              The menu object to work with
	 * @param   CategoryFactoryInterface  $categoryFactory   The category object
	 * @param   DatabaseInterface         $db                The database object
	 *
	 * @since   4.0.0
	 */
	public function __construct(
		SiteApplication $app,
		AbstractMenu $menu,
		CategoryFactoryInterface $categoryFactory,
		DatabaseInterface $db)
	{
		$this->categoryFactory = $categoryFactory;
		$this->db              = $db;
		$this->params          = ComponentHelper::getParams('com_servicedirectory');
		$this->noIDs           = (bool) $this->params->get('sef_ids', false);

		// Add the (directory:view) router configuration
		$directory = new RouterViewConfiguration('directory');
		$this->registerView($directory);

		// Add the (companies:view) router configuration
		$companies = new RouterViewConfiguration('companies');
		$companies->setKey('search')->setParent($directory);
		$this->registerView($companies);

		// Add the (category:view) router configuration
		$category = new RouterViewConfiguration('category');
		$category->setKey('id')->setKey('search')->setParent($directory);
		$this->registerView($category);

		// Add the (tag:view) router configuration
		$tag = new RouterViewConfiguration('tag');
		$tag->setKey('id')->setKey('search')->setParent($directory);
		$this->registerView($tag);

		// Add the (areaofexpertise:view) router configuration
		$areaofexpertise = new RouterViewConfiguration('areaofexpertise');
		$areaofexpertise->setKey('id')->setKey('search')->setParent($directory);
		$this->registerView($areaofexpertise);

		// Add the (lang:view) router configuration
		$lang = new RouterViewConfiguration('lang');
		$lang->setKey('id')->setParent($directory);
		$this->registerView($lang);

		// Add the (listing:view) router configuration
		$listing = new RouterViewConfiguration('listing');
		$listing->setKey('id')->setParent($directory);
		$this->registerView($listing);

		// Add the (company:view) router configuration
		$company = new RouterViewConfiguration('company');
		$company->setKey('id')->setParent($directory);
		$this->registerView($company);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Build the route for the com_servicedirectory component.
	 *
	 * This method converts query variables into URL segments for SEF routing.
	 * It checks for specific view types, handles search parameters, and calls
	 * custom segment builder methods when available.
	 *
	 * @param  array  &$query  The associative array of URL arguments.
	 *
	 * @return array  The array of URL path segments.
	 * @since   5.1.2
	 */
	public function build(&$query)
	{
		$segments = [];
		$view = (is_array($query) && !empty($query['view'])) ? $query['view'] : null;

		if (!empty($view) && $view !== 'directory')
		{
			$method = 'get' . ucfirst((string) $view) . 'Segment';

			if (\is_callable([$this, $method]))
			{
				$id = $query['id'] ?? null;
				$segmentData = \call_user_func([$this, $method], $id, $query);

				if (\is_array($segmentData) && !empty($segmentData))
				{
					$segment = array_values($segmentData)[0];

					if (!empty($segment) && \is_string($segment))
					{
						$segments[] = $view;
						$segments[] = $segment;

						if (!empty($query['search']))
						{
							$segments[] = (string) $query['search'];
						}
					}
				}
			}
			elseif (\in_array($view, ['companies', 'directory'], true))
			{
				$segments[] = $view;
				if (!empty($query['search']))
				{
					$segments[] = (string) $query['search'];
				}
			}
		}

		// Clean used query variables
		if (!empty($view) && \in_array($view, ['directory', 'companies', 'category', 'tag', 'areaofexpertise', 'lang', 'listing'], true))
		{
			unset($query['search'], $query['view'], $query['id']);
		}

		return $segments;
	}

	/**
	 * Parse the SEF URL segments for the com_servicedirectory component.
	 *
	 * This method translates URL segments back into query variables.
	 * It supports reverse lookup using component-specific ID retrieval methods.
	 *
	 * @param  array  &$segments  The array of URL segments to parse.
	 *
	 * @return array  The associative array of query variables.
	 * @since   5.1.2
	 */
	public function parse(&$segments)
	{
		$vars = [];

		// Default to 'directory' view if not set
		$vars['view'] = $segments[0] ?? 'directory';


		if ($vars['view'] === 'companies')
		{
			if (!empty($segments[1]))
			{
				$vars['search'] = $segments[1];
			}
			elseif (!empty($segments[2]))
			{
				$vars['search'] = $segments[2];
			}
		}
		elseif ($vars['view'] !== 'directory')
		{
			$method = 'get' . ucfirst((string) $vars['view']) . 'Id';

			if (\is_callable([$this, $method]))
			{
				$segmentValue = $segments[1] ?? '';
				$id = \call_user_func([$this, $method], (string) $segmentValue, $segments);

				if (!empty($id))
				{
					$vars['id'] = $id;

					if (!empty($segments[2]))
					{
						$vars['search'] = $segments[2];
					}
				}
				else
				{
					// Fallback if ID not found
					$vars['view'] = 'directory';
				}
			}
			else
			{
				// Invalid callable fallback
				$vars['view'] = 'directory';
			}
		}

		// Clear processed segments to prevent side effects
		$segments = [];

		return $vars;
	}


	/**
	 * Method to get the segment(s) for category
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 * @since   4.4.0
	 */
	public function getCategoryId($segment, $query)
	{
		if ($this->noIDs)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__servicedirectory_category'))
				->where(
					[
						$this->db->quoteName('alias') . ' = :alias'
					]
				)
				->bind(':alias', $segment);
			$this->db->setQuery($dbquery);

			return (int) $this->db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for category
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getCategorySegment($id, $query)
	{
		$id = (string) ($id ?? '');
		if (empty($id))
		{
			return 'error';
		}

		if (strpos($id, ':') === false)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('alias'))
				->from($this->db->quoteName('#__servicedirectory_category'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}

		if ($this->noIDs && strpos($id, ':') !== false)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [(int) $void => $segment];
		}

		return [(int) $id => $id];
	}

	/**
	 * Method to get the segment(s) for tag
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 * @since   4.4.0
	 */
	public function getTagId($segment, $query)
	{
		if ($this->noIDs)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__servicedirectory_tag'))
				->where(
					[
						$this->db->quoteName('alias') . ' = :alias'
					]
				)
				->bind(':alias', $segment);
			$this->db->setQuery($dbquery);

			return (int) $this->db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for tag
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getTagSegment($id, $query)
	{
		$id = (string) ($id ?? '');
		if (empty($id))
		{
			return 'error';
		}

		if (strpos($id, ':') === false)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('alias'))
				->from($this->db->quoteName('#__servicedirectory_tag'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}

		if ($this->noIDs && strpos($id, ':') !== false)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [(int) $void => $segment];
		}

		return [(int) $id => $id];
	}

	/**
	 * Method to get the segment(s) for areaofexpertise
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 * @since   4.4.0
	 */
	public function getAreaofexpertiseId($segment, $query)
	{
		if ($this->noIDs)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__servicedirectory_area_of_expertise'))
				->where(
					[
						$this->db->quoteName('alias') . ' = :alias'
					]
				)
				->bind(':alias', $segment);
			$this->db->setQuery($dbquery);

			return (int) $this->db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for areaofexpertise
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getAreaofexpertiseSegment($id, $query)
	{
		$id = (string) ($id ?? '');
		if (empty($id))
		{
			return 'error';
		}

		if (strpos($id, ':') === false)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('alias'))
				->from($this->db->quoteName('#__servicedirectory_area_of_expertise'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}

		if ($this->noIDs && strpos($id, ':') !== false)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [(int) $void => $segment];
		}

		return [(int) $id => $id];
	}

	/**
	 * Method to get the segment(s) for lang
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 * @since   4.4.0
	 */
	public function getLangId($segment, $query)
	{
		if ($this->noIDs)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__servicedirectory_language'))
				->where(
					[
						$this->db->quoteName('langtag') . ' = :langtag'
					]
				)
				->bind(':langtag', $segment);
			$this->db->setQuery($dbquery);

			return (int) $this->db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for lang
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getLangSegment($id, $query)
	{
		$id = (string) ($id ?? '');
		if (empty($id))
		{
			return 'error';
		}

		if (strpos($id, ':') === false)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('langtag'))
				->from($this->db->quoteName('#__servicedirectory_language'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}

		if ($this->noIDs && strpos($id, ':') !== false)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [(int) $void => $segment];
		}

		return [(int) $id => $id];
	}

	/**
	 * Method to get the segment(s) for Company
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getCompanySegment($id, $query)
	{
		return $this->getListingSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for listing
	 *
	 * @param   string  $segment  Segment of the article to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 * @since   4.4.0
	 */
	public function getListingId($segment, $query)
	{
		if ($this->noIDs)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__servicedirectory_company'))
				->where(
					[
						$this->db->quoteName('alias') . ' = :alias'
					]
				)
				->bind(':alias', $segment);
			$this->db->setQuery($dbquery);

			return (int) $this->db->loadResult();
		}

		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for listing
	 *
	 * @param   string  $id     ID of the contact to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 * @since   4.4.0
	 */
	public function getListingSegment($id, $query)
	{
		$id = (string) ($id ?? '');
		if (empty($id))
		{
			return 'error';
		}

		if (strpos($id, ':') === false)
		{
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('alias'))
				->from($this->db->quoteName('#__servicedirectory_company'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}

		if ($this->noIDs && strpos($id, ':') !== false)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [(int) $void => $segment];
		}

		return [(int) $id => $id];
	}
}
