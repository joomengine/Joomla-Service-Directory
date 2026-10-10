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
namespace JoomService\Component\Servicedirectory\Api\View\Directory;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Directory resource
 *
 * The attributes are whatever the model of the directory view returns.
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render items in the documents, taken from the items
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [];

	/**
	 * The position of the last row prepared, the id of a row without one
	 *
	 * @var    int
	 * @since  4.0.0
	 */
	protected int $position = 0;

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
		if ($items === null)
		{
			$items = [];

			foreach ($this->getModel()->getItems() ?: [] as $item)
			{
				$items[] = $this->prepareItem($item);
			}
		}

		// The attributes are the keys the model returned (a field selection may follow later).
		$fields = [];

		foreach ($items as $item)
		{
			if (is_object($item))
			{
				$fields += array_flip(array_keys(get_object_vars($item)));
			}
		}

		$this->fieldsToRenderList = array_keys($fields);

		// The images custom get of the directory view rides along as meta.
		$this->getDocument()->addMeta('images', $this->getModel()->getImages());

		// The mine custom get of the directory view rides along as meta.
		$this->getDocument()->addMeta('mine', $this->getModel()->getMine());

		// The tickets custom get of the directory view rides along as meta.
		$this->getDocument()->addMeta('tickets', $this->getModel()->getTickets());

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
		if (!is_object($item))
		{
			return $item;
		}
		// A JSON:API resource needs an id.
		if (!isset($item->id))
		{
			$item->id = ++$this->position;
		}

		return parent::prepareItem($item);
	}
}
