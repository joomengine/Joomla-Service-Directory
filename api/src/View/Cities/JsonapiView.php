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
namespace JoomService\Component\Servicedirectory\Api\View\Cities;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Registry\Registry;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Api\Serializer\CitySerializer;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Cities
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
		'name',
		'state',
		'latitude',
		'longitude',
		'guid',
		'wikidataid',
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
		'state',
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
			$this->serializer = new CitySerializer($config['contentType']);
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

		// Remove the name value based on Access access controls.
		if (!$user->authorise('city.access.name', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['name']));
			$this->relationship = array_values(array_diff($this->relationship, ['name']));
		}

		// Remove the name value based on View access controls.
		if (!$user->authorise('city.view.name', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['name']));
			$this->relationship = array_values(array_diff($this->relationship, ['name']));
		}

		// Remove the state value based on Access access controls.
		if (!$user->authorise('city.access.state', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['state']));
			$this->relationship = array_values(array_diff($this->relationship, ['state']));
		}

		// Remove the state value based on View access controls.
		if (!$user->authorise('city.view.state', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['state']));
			$this->relationship = array_values(array_diff($this->relationship, ['state']));
		}

		// Remove the latitude value based on Access access controls.
		if (!$user->authorise('city.access.latitude', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['latitude']));
			$this->relationship = array_values(array_diff($this->relationship, ['latitude']));
		}

		// Remove the latitude value based on View access controls.
		if (!$user->authorise('city.view.latitude', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['latitude']));
			$this->relationship = array_values(array_diff($this->relationship, ['latitude']));
		}

		// Remove the longitude value based on Access access controls.
		if (!$user->authorise('city.access.longitude', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['longitude']));
			$this->relationship = array_values(array_diff($this->relationship, ['longitude']));
		}

		// Remove the longitude value based on View access controls.
		if (!$user->authorise('city.view.longitude', 'com_servicedirectory'))
		{
			$this->fieldsToRenderList = array_values(array_diff($this->fieldsToRenderList, ['longitude']));
			$this->relationship = array_values(array_diff($this->relationship, ['longitude']));
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
