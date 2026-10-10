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
namespace JoomService\Component\Servicedirectory\Api\View\Timezones;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Registry\Registry;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Api\Serializer\TimezoneSerializer;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Timezones
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render items in the documents
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'timezone_name',
		'timezone_identifier',
		'gmt_offset_name',
		'country',
		'guid',
		'gmt_offset_sec',
		'abbreviation',
		'created',
		'created_by',
		'modified',
		'modified_by',
		'published',
		'ordering',
		'access',
		'version',
		'hits',
	];

	/**
	 * The relationships the items have
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $relationship = [
		'country',
		'created_by',
		'modified_by',
	];

	/**
	 * Constructor.
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          contentType: the name (optional) of the content type to use for the serialization
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = [])
	{
		if (\array_key_exists('contentType', $config))
		{
			$this->serializer = new TimezoneSerializer($config['contentType']);
		}

		parent::__construct($config);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   ?array  $items  Array of items
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList(?array $items = null)
	{
		// Get user object.
		$user = Factory::getApplication()->getIdentity();

		// Remove the timezone_name value based on Access access controls.
		if (!$user->authorise('timezone.access.timezone_name', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['timezone_name']));
			$this->relationship = array_values(array_diff($this->relationship, ['timezone_name']));
		}

		// Remove the timezone_name value based on View access controls.
		if (!$user->authorise('timezone.view.timezone_name', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['timezone_name']));
			$this->relationship = array_values(array_diff($this->relationship, ['timezone_name']));
		}

		return parent::displayList($items);
	}

	/**
	 * Prepare item before render.
	 *
	 * @param   object  $item  The model item
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function prepareItem($item)
	{
		return parent::prepareItem($item);
	}
}
