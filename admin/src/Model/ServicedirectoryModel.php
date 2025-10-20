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
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
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
use JoomService\Joomla\Utilities\ArrayHelper as UtilitiesArrayHelper;
use JoomService\Joomla\Utilities\StringHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory List Model
 *
 * @since  1.6
 */
class ServicedirectoryModel extends ListModel
{
	/**
	 * Represents the current user object.
	 *
	 * @var   User  The user object representing the current user.
	 * @since 3.2.0
	 */
	protected User $user;

	/**
	 * View groups of this component
	 *
	 * @var   array<string, string>
	 * @since 5.1.1
	 */
	protected array $viewGroups = [
		'main' => ['png.company.add', 'png.companies', 'png.portfolios', 'png.categories', 'png.tags', 'png.social_handles', 'png.areas_of_expertise', 'png.files', 'png.addresses', 'png.tickets', 'png.regions', 'png.subregions', 'png.countries', 'png.states', 'png.cities'],
	];

	/**
	 * View access array.
	 *
	 * @var   array<string, string>
	 * @since 5.1.1
	 */
	protected array $viewAccess = [
		'companies.access' => 'company.access',
		'company.access' => 'company.access',
		'companies.dashboard_list' => 'company.dashboard_list',
		'company.dashboard_add' => 'company.dashboard_add',
		'portfolio.create' => 'portfolio.create',
		'portfolios.access' => 'portfolio.access',
		'portfolio.access' => 'portfolio.access',
		'portfolios.dashboard_list' => 'portfolio.dashboard_list',
		'category.create' => 'category.create',
		'categories.access' => 'category.access',
		'category.access' => 'category.access',
		'categories.dashboard_list' => 'category.dashboard_list',
		'tag.create' => 'tag.create',
		'tags.access' => 'tag.access',
		'tag.access' => 'tag.access',
		'tags.dashboard_list' => 'tag.dashboard_list',
		'social_handle.create' => 'social_handle.create',
		'social_handles.access' => 'social_handle.access',
		'social_handle.access' => 'social_handle.access',
		'social_handles.dashboard_list' => 'social_handle.dashboard_list',
		'area_of_expertise.create' => 'area_of_expertise.create',
		'areas_of_expertise.access' => 'area_of_expertise.access',
		'area_of_expertise.access' => 'area_of_expertise.access',
		'areas_of_expertise.dashboard_list' => 'area_of_expertise.dashboard_list',
		'file.create' => 'file.create',
		'files.access' => 'file.access',
		'file.access' => 'file.access',
		'files.dashboard_list' => 'file.dashboard_list',
		'address.create' => 'address.create',
		'addresses.access' => 'address.access',
		'address.access' => 'address.access',
		'addresses.dashboard_list' => 'address.dashboard_list',
		'ticket.create' => 'ticket.create',
		'tickets.access' => 'ticket.access',
		'ticket.access' => 'ticket.access',
		'tickets.dashboard_list' => 'ticket.dashboard_list',
		'region.create' => 'region.create',
		'regions.access' => 'region.access',
		'region.access' => 'region.access',
		'regions.dashboard_list' => 'region.dashboard_list',
		'subregion.create' => 'subregion.create',
		'subregions.access' => 'subregion.access',
		'subregion.access' => 'subregion.access',
		'subregions.dashboard_list' => 'subregion.dashboard_list',
		'country.create' => 'country.create',
		'countries.access' => 'country.access',
		'country.access' => 'country.access',
		'countries.dashboard_list' => 'country.dashboard_list',
		'state.create' => 'state.create',
		'states.access' => 'state.access',
		'state.access' => 'state.access',
		'states.dashboard_list' => 'state.dashboard_list',
		'city.create' => 'city.create',
		'cities.access' => 'city.access',
		'city.access' => 'city.access',
		'cities.dashboard_list' => 'city.dashboard_list',
		'timezone.create' => 'timezone.create',
		'timezones.access' => 'timezone.access',
		'timezone.access' => 'timezone.access',
		'platform.create' => 'platform.create',
		'platforms.access' => 'platform.access',
		'platform.access' => 'platform.access',
		'language.create' => 'language.create',
		'languages.access' => 'language.access',
		'language.access' => 'language.access',
		'address_type.create' => 'address_type.create',
		'address_types.access' => 'address_type.access',
		'address_type.access' => 'address_type.access',
		'file_type.create' => 'file_type.create',
		'file_types.access' => 'file_type.access',
		'file_type.access' => 'file_type.access',
		'company_language.create' => 'company_language.create',
		'company_languages.access' => 'company_language.access',
		'company_language.access' => 'company_language.access',
		'company_tag.create' => 'company_tag.create',
		'company_tags.access' => 'company_tag.access',
		'company_tag.access' => 'company_tag.access',
		'company_area_of_expertise.create' => 'company_area_of_expertise.create',
		'company_areas_of_expertise.access' => 'company_area_of_expertise.access',
		'company_area_of_expertise.access' => 'company_area_of_expertise.access',
		'ticket_comment.create' => 'ticket_comment.create',
		'ticket_comments.access' => 'ticket_comment.access',
		'ticket_comment.access' => 'ticket_comment.access',
	];

	/**
	 * The styles array.
	 *
	 * @var    array
	 * @since  4.3
	 */
	protected array $styles = [
		'administrator/components/com_servicedirectory/assets/css/admin.css',
		'administrator/components/com_servicedirectory/assets/css/dashboard.css'
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
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

		$this->user ??= $this->getCurrentUser();
	}

	/**
	 * Get dashboard icons, grouped by view sections.
	 *
	 * @return array<string, array<int, \stdClass|false>>
	 * @since  5.1.1
	 */
	public function getIcons(): array
	{
		$icons = [];

		foreach ($this->viewGroups as $group => $views)
		{
			if (!UtilitiesArrayHelper::check($views))
			{
				$icons[$group][] = false;
				continue;
			}

			foreach ($views as $view)
			{
				$icon = $this->buildIconObject($view);
				if ($icon !== null)
				{
					$icons[$group][] = $icon;
				}
			}
		}

		return $icons;
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
	 * Build a single dashboard icon if access is granted.
	 *
	 * @param string $view The view string to parse.
	 *
	 * @return \stdClass|null  The icon object or null if access denied.
	 * @since  5.1.1
	 */
	protected function buildIconObject(string $view): ?\stdClass
	{
		$parsed = $this->parseViewDefinition($view);
		if (!$parsed)
		{
			return null;
		}

		[
			'type' => $type,
			'name' => $name,
			'url' => $url,
			'image' => $image,
			'alt' => $alt,
			'viewName' => $viewName,
			'add' => $add,
		] = $parsed;

		if (!$this->hasAccessToView($viewName, $add))
		{
			return null;
		}

		return $this->createIconObject($url, $name, $image, $alt);
	}

	/**
	 * Parse a view string into structured components.
	 *
	 * @param string $view  The view definition string.
	 *
	 * @return array<string, mixed>|null  Parsed values or null on failure.
	 * @since  5.1.1
	 */
	protected function parseViewDefinition(string $view): ?array
	{
		$add = false;

		if (strpos($view, '||') !== false)
		{
			$parts = explode('||', $view);
			if (count($parts) === 3)
			{
				[$type, $name, $url] = $parts;
				return [
					'type' => $type,
					'name' => 'COM_SERVICEDIRECTORY_DASHBOARD_' . StringHelper::safe($name, 'U'),
					'url' => $url,
					'image' => "{$name}.{$type}",
					'alt' => $name,
					'viewName' => $name,
					'add' => false,
				];
			}
		}

		if (strpos($view, '.') !== false)
		{
			$parts = explode('.', $view);
			$type = $parts[0] ?? '';
			$name = $parts[1] ?? '';
			$action = $parts[2] ?? null;
			$viewName = $name;

			if ($action)
			{
				if ($action === 'add')
				{
					$url = "index.php?option=com_servicedirectory&view={$name}&layout=edit";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_SERVICEDIRECTORY_DASHBOARD_' .
							StringHelper::safe($name, 'U') . '_ADD';
					$add = true;
				}
				else
				{
					if (strpos($action, '_qpo0O0oqp_') !== false)
					{
						[$action, $ext] = explode('_qpo0O0oqp_', $action);
						$extension = str_replace('_po0O0oq_', '.', $ext);
					}
					else
					{
						$extension = "com_servicedirectory.{$name}";
					}
					$url = "index.php?option=com_categories&view=categories&extension={$extension}";
					$image = "{$name}_{$action}.{$type}";
					$alt = "{$name}&nbsp;{$action}";
					$name = 'COM_SERVICEDIRECTORY_DASHBOARD_' .
							StringHelper::safe($name, 'U') . '_' .
							StringHelper::safe($action, 'U');
				}
			}
			else
			{
				$url = "index.php?option=com_servicedirectory&view={$name}";
				$image = "{$name}.{$type}";
				$alt = $name;
				$name = 'COM_SERVICEDIRECTORY_DASHBOARD_' .
						StringHelper::safe($name, 'U');
			}

			return compact('type', 'name', 'url', 'image', 'alt', 'viewName', 'add');
		}

		return [
			'type' => 'png',
			'name' => ucwords($view) . '<br /><br />',
			'url' => "index.php?option=com_servicedirectory&view={$view}",
			'image' => "{$view}.png",
			'alt' => $view,
			'viewName' => $view,
			'add' => false,
		];
	}

	/**
	 * Determine if the user has access to view or create the item.
	 *
	 * @param string $viewName The base name of the view.
	 * @param bool $add If this is an add-action.
	 *
	 * @return bool
	 * @since  5.1.1
	 */
	protected function hasAccessToView(string $viewName, bool $add): bool
	{
		$viewAccess = $this->viewAccess;
		$accessAdd = $add && isset($viewAccess["{$viewName}.create"])
			? $viewAccess["{$viewName}.create"]
			: ($add ? 'core.create' : '');

		$accessTo = $viewAccess["{$viewName}.access"] ?? '';

		$dashboardAdd = isset($viewAccess["{$viewName}.dashboard_add"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_add"], 'com_servicedirectory');

		$dashboardList = isset($viewAccess["{$viewName}.dashboard_list"]) &&
					$this->user->authorise($viewAccess["{$viewName}.dashboard_list"], 'com_servicedirectory');

		if ($add && StringHelper::check($accessAdd))
		{
			return $this->user->authorise($accessAdd, 'com_servicedirectory') && $dashboardAdd;
		}

		if (StringHelper::check($accessTo))
		{
			return $this->user->authorise($accessTo, 'com_servicedirectory') && $dashboardList;
		}

		return !$accessTo && !$accessAdd;
	}

	/**
	 * Create a \stdClass icon object.
	 *
	 * @param string $url Icon URL.
	 * @param string $name Language string or label.
	 * @param string $image Image filename.
	 * @param string $alt Alt text.
	 *
	 * @return \stdClass
	 * @since  5.1.1
	 */
	protected function createIconObject(string $url, string $name, string $image, string $alt): \stdClass
	{
		$icon = new \stdClass;
		$icon->url = $url;
		$icon->name = $name;
		$icon->image = $image;
		$icon->alt = $alt;
		return $icon;
	}
}
