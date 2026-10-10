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
namespace JoomService\Component\Servicedirectory\Api\View\Listing;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Json View class for the Listing resource
 *
 * The attributes are whatever the model of the listing view returns.
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents, taken from the item
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [];

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

		// The attributes are the keys the model returned (a field selection may follow later).
		$this->fieldsToRenderItem = is_object($item) ? array_keys(get_object_vars($item)) : [];

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
		if (!is_object($item))
		{
			return $item;
		}
		// A JSON:API resource needs an id.
		if (!isset($item->id))
		{
			$item->id = (int) $this->getModel()->getState('listing.id');
		}

		// The company_language rows joined to this listing on its guid.
		$item->company_language = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_languageDaec_L($item->guid)
			: [];

		// The company_tag rows joined to this listing on its guid.
		$item->company_tag = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_tagDaec_T($item->guid)
			: [];

		// The company_area_of_expertise rows joined to this listing on its guid.
		$item->company_area_of_expertise = isset($item->guid)
			? $this->getModel()->getGuidCompanyCompany_area_of_expertiseDaec_EE($item->guid)
			: [];

		// The social_handle rows joined to this listing on its guid.
		$item->social_handle = isset($item->guid)
			? $this->getModel()->getGuidCompanySocial_handleDaec_S($item->guid)
			: [];

		// The address rows joined to this listing on its guid.
		$item->address = isset($item->guid)
			? $this->getModel()->getGuidCompanyAddressDaec_HH($item->guid)
			: [];

		// The portfolio rows joined to this listing on its guid.
		$item->portfolio = isset($item->guid)
			? $this->getModel()->getGuidCompanyPortfolioDaec_QQ($item->guid)
			: [];

		// The file rows joined to this listing on its guid.
		$item->file = isset($item->guid)
			? $this->getModel()->getGuidEntityFileDaec_YY($item->guid)
			: [];

		return parent::prepareItem($item);
	}
}
