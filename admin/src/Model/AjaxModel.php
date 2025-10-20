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
namespace JoomService\Component\Servicedirectory\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Utilities\ArrayHelper;
use Joomla\Input\Input;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Joomla\Utilities\GuidHelper;
use JoomService\Joomla\Data\Factory as DataFactory;
use JoomService\Joomla\Servicedirectory\File\Factory as FileFactory;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Ajax List Model
 *
 * @since  1.6
 */
class AjaxModel extends ListModel
{
	/**
	 * The component params.
	 *
	 * @var   Registry
	 * @since 3.2.0
	 */
	protected Registry $app_params;

	/**
	 * The application object.
	 *
	 * @var   CMSApplicationInterface  The application instance.
	 * @since 3.2.0
	 */
	protected CMSApplicationInterface $app;

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

		$this->app_params = ComponentHelper::getParams('com_servicedirectory');
		$this->app ??= Factory::getApplication();
	}

	// Used in company

/***[JCBGUI.admin_view.php_ajaxmethod.324.$$$$]***/
	/**
	 * Get the country states if they exist.
	 *
	 * @param string $country  The country guid
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function getCountryStates(string $country): array
	{
		if (GuidHelper::valid($country))
		{
			try
			{
				$result = DataFactory::_('Data.Items')->table('state')->get([$country], 'country');
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if ($result !== null)
			{
				$states = array_map(function ($state) {
					return (object) ['value' => $state->guid, 'text' => $state->name];
				}, $result);

				return ['data' => $states];
			}
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_THE_COUNTRY_STATES_COULD_NOT_BE_FOUND')];
	}

	/**
	 * Get the state cities if they exist.
	 *
	 * @param string $country  The country guid
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function getStateCities(string $state): array
	{
		if (GuidHelper::valid($state))
		{
			try
			{
				$result = DataFactory::_('Data.Items')->table('city')->get([$state], 'state');
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if ($result !== null)
			{
				$cities = array_map(function ($city) {
					return (object) ['value' => $city->guid, 'text' => $city->name];
				}, $result);

				return ['data' => $cities];
			}
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_THE_STATE_CITIES_COULD_NOT_BE_FOUND')];
	}/***[/JCBGUI$$$$]***/


	// Used in file_type
/***[INSERTED$$$$]***//**440**/
	/**
	 * Get the file type details, if it exists.
	 *
	 * @param string $guid    The file type guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function getFileTypeDetails(string $guid, string $target): array
	{
		if (GuidHelper::valid($guid))
		{
			try
			{
				$target = base64_decode($target);
				$type = FileFactory::_('File.Type')->get($guid, $target);
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if ($type !== null)
			{
				return ['data' => $type];
			}
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_FILE_TYPE_DETAILS_COULD_NOT_BE_FOUND')];
	}

	/**
	 * Upload a file, of a given file type and link it to an entity.
	 *
	 * @param string $guid    The file type guid
	 * @param string $entity  The entity guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function uploadFile(string $guid, string $entity, string $target): array
	{
		if (GuidHelper::valid($guid)
			&& GuidHelper::valid($entity))
		{
			try
			{
				$target = base64_decode($target);
				FileFactory::_('File.Manager')->upload($guid, $entity, $target);
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			return ['success' => Text::_('COM_SERVICEDIRECTORY_THE_FILE_WAS_SUCCESSFULLY_UPLOADED')];
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_THE_FILE_FAILED_TO_UPLOAD')];
	}

	/**
	 * Delete a file of a given entity.
	 *
	 * @param string $guid    The file guid
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function deleteFile(string $guid): array
	{
		if (GuidHelper::valid($guid))
		{
			try
			{
				FileFactory::_('File.Manager')->delete($guid);
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			return ['success' => Text::_('COM_SERVICEDIRECTORY_THE_FILE_WAS_SUCCESSFULLY_DELETED')];
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_THE_FILE_COULD_NOT_BE_DELETED')];
	}

	/**
	 * Load the display of the files linked this entity.
	 *
	 * @param string $entity  The entity guid
	 * @param string $target  The target entity name
	 *
	 * @return array
	 * @since 5.0.2
	 */
	public function displayFiles(string $entity, string $target): array
	{
		if (GuidHelper::valid($entity))
		{
			$display = null;

			try
			{
				$target = base64_decode($target);
				$data = FileFactory::_('File.Display')->get($entity, $target);

				if ($data !== null)
				{
					$displayData =  ['data' => $data, 'entity' => $entity, 'target' => $target];
					$display = LayoutHelper::render('filedisplay', $displayData);
				}
				else
				{
					return ['data' => '<b>' . Text::sprintf('COM_SERVICEDIRECTORY_NO_FILES_LINKED_TO_S', $target) . '</b>'];
				}
			}
			catch (\Exception $error)
			{
				return ['error' => $error->getMessage()];
			}

			if (!empty($display))
			{
				return ['data' => $display];
			}
		}

		return ['error' => Text::_('COM_SERVICEDIRECTORY_THE_FILE_DISPLAY_COULD_NOT_BE_LOADED')];
	}/***[/INSERTED$$$$]***/
}
