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
namespace JoomService\Component\Servicedirectory\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\User\UserFactoryInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Companiesfiltercreatedby Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class CompaniesfiltercreatedbyField extends ListField
{
	/**
	 * The companiesfiltercreatedby field type.
	 *
	 * @var        string
	 */
	public $type = 'Companiesfiltercreatedby';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get a db connection.
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('created_by'));
		$query->from($db->quoteName('#__servicedirectory_company'));
		$query->order($db->quoteName('created_by') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$_results = $db->loadColumn();
		$_filter = [];
		$_filter[] = Html::_('select.option', '', '- ' . Text::_('COM_SERVICEDIRECTORY_FILTER_SELECT_CREATED_BY') . ' -');

		if ($_results)
		{
			$_results = array_unique($_results);
			foreach ($_results as $created_by)
			{
				// Now add the created_by and its text to the options array
				$_filter[] = Html::_('select.option', $created_by,
					Factory::getContainer()->
					get(UserFactoryInterface::class)->
					loadUserById((int) ($created_by ?? 0))->name
					);
			}
		}
		return $_filter;
	}
}
