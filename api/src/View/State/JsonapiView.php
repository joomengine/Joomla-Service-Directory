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
namespace JoomService\Component\Servicedirectory\Api\View\State;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Api\Serializer\StateSerializer;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * State Json View class
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'id',
		'name',
		'country',
		'type',
		'guid',
		'wikidataid',
		'fips_code',
		'iso2',
		'longitude',
		'latitude',
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
	 * The relationships the item has
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
			$this->serializer = new StateSerializer($config['contentType']);
		}

		parent::__construct($config);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   object  $item  Item
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem($item = null)
	{
		if ($item === null)
		{
			$item = $this->prepareItem($this->getModel()->getItem());
		}

		// Get user object.
		$user = Factory::getApplication()->getIdentity();
		$id = is_object($item) ? (int) $item->id : 0;

		// Remove the name value based on Access access controls.
		if (($id != 0 && !$user->authorise('state.access.name', 'com_servicedirectory.state.' . $id))
			|| ($id == 0 && !$user->authorise('state.access.name', 'com_servicedirectory')))
		{
			$this->fieldsToRenderItem = array_values(array_diff($this->fieldsToRenderItem, ['name']));
			$this->relationship = array_values(array_diff($this->relationship, ['name']));
		}

		// Remove the name value based on View access controls.
		if (($id != 0 && !$user->authorise('state.view.name', 'com_servicedirectory.state.' . $id))
			|| ($id == 0 && !$user->authorise('state.view.name', 'com_servicedirectory')))
		{
			$this->fieldsToRenderItem = array_values(array_diff($this->fieldsToRenderItem, ['name']));
			$this->relationship = array_values(array_diff($this->relationship, ['name']));
		}

		return parent::displayItem($item);
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
