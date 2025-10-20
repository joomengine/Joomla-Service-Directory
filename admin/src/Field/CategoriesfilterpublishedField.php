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

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Categoriesfilterpublished Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class CategoriesfilterpublishedField extends ListField
{
	/**
	 * The categoriesfilterpublished field type.
	 *
	 * @var        string
	 */
	public $type = 'Categoriesfilterpublished';

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
		$query->select($db->quoteName('published'));
		$query->from($db->quoteName('#__servicedirectory_category'));
		$query->order($db->quoteName('published') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$_results = $db->loadColumn();
		$_filter = [];
		$_filter[] = Html::_('select.option', '', '- ' . Text::_('COM_SERVICEDIRECTORY_FILTER_SELECT_STATUS') . ' -');

		if ($_results)
		{
			// get categoriesmodel
			$_model = ServicedirectoryHelper::getModel('categories');
			$_results = array_unique($_results);
			foreach ($_results as $published)
			{
				// Translate the published selection
				$_text = $_model->selectionTranslation($published,'published');
				// Now add the published and its text to the options array
				$_filter[] = Html::_('select.option', $published, Text::_($_text));
			}
		}
		return $_filter;
	}
}
