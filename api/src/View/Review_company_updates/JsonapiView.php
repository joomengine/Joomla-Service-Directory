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
namespace JoomService\Component\Servicedirectory\Api\View\Review_company_updates;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Registry\Registry;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Api\Serializer\Review_company_updateSerializer;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Review_company_updates
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
		'contactname',
		'category',
		'created_by',
		'review_status',
		'addresses',
		'languages',
		'chamber_of_commerce',
		'guid',
		'tags',
		'email',
		'areas_of_expertise',
		'alias',
		'company_type',
		'description',
		'website',
		'portfolios',
		'social_handles',
		'phone',
		'companysize',
		'created',
		'modified',
		'modified_by',
		'published',
		'ordering',
		'access',
		'version',
		'hits',
		'metakey',
		'metadesc',
		'metadata',
	];

	/**
	 * The relationships the items have
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $relationship = [
		'category',
		'created_by',
		'languages',
		'tags',
		'areas_of_expertise',
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
			$this->serializer = new Review_company_updateSerializer($config['contentType']);
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
		if (isset($item->addresses) && is_string($item->addresses) && $item->addresses !== '')
		{
			// Convert the addresses field to an array.
			$registry = new Registry;
			$registry->loadString($item->addresses);
			$item->addresses = $registry->toArray();
		}

		if (isset($item->languages) && is_string($item->languages) && $item->languages !== '')
		{
			// Convert the languages field to an array.
			$registry = new Registry;
			$registry->loadString($item->languages);
			$item->languages = $registry->toArray();
		}

		if (isset($item->tags) && is_string($item->tags) && $item->tags !== '')
		{
			// Convert the tags field to an array.
			$registry = new Registry;
			$registry->loadString($item->tags);
			$item->tags = $registry->toArray();
		}

		if (isset($item->areas_of_expertise) && is_string($item->areas_of_expertise) && $item->areas_of_expertise !== '')
		{
			// Convert the areas_of_expertise field to an array.
			$registry = new Registry;
			$registry->loadString($item->areas_of_expertise);
			$item->areas_of_expertise = $registry->toArray();
		}

		if (isset($item->portfolios) && is_string($item->portfolios) && $item->portfolios !== '')
		{
			// Convert the portfolios field to an array.
			$registry = new Registry;
			$registry->loadString($item->portfolios);
			$item->portfolios = $registry->toArray();
		}

		if (isset($item->social_handles) && is_string($item->social_handles) && $item->social_handles !== '')
		{
			// Convert the social_handles field to an array.
			$registry = new Registry;
			$registry->loadString($item->social_handles);
			$item->social_handles = $registry->toArray();
		}

		return parent::prepareItem($item);
	}
}
