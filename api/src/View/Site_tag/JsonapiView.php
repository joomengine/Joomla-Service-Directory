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
namespace JoomService\Component\Servicedirectory\Api\View\Site_tag;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Site_tag resource
 *
 * The attributes are whatever the model of the tag view returns.
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

		// The logos custom get of the tag view rides along as meta.
		$this->getDocument()->addMeta('logos', $this->getModel()->getLogos());

		// The banner custom get of the tag view rides along as meta.
		$this->getDocument()->addMeta('banner', $this->getModel()->getBanner());

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

		// The company_language rows joined to this tag on its guid.
		$item->company_language = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_languageAcfa_L($item->guid)
			: [];

		// The company_tag rows joined to this tag on its guid.
		$item->company_tag = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_tagAcfa_T($item->guid)
			: [];

		// The company_area_of_expertise rows joined to this tag on its guid.
		$item->company_area_of_expertise = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_area_of_expertiseAcfa_EE($item->guid)
			: [];

		return parent::prepareItem($item);
	}
}
