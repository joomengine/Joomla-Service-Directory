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
namespace JoomService\Component\Servicedirectory\Site\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use Joomla\Database\DatabaseInterface;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Filetypes Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class FiletypesField extends ListField
{
	/**
	 * The filetypes field type.
	 *
	 * @var        string
	 */
	public $type = 'Filetypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get the database object.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.guid','a.name','a.target'),array('guid','file_type_name','target')));
		$query->from($db->quoteName('#__servicedirectory_file_type', 'a'));
		$query->where($db->quoteName('a.published') . ' >= 1');
		$query->order('a.name ASC');
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$options = [];
		if ($items)
		{
			if ($this->multiple === false)
			{
				$options[] = Html::_('select.option', '', Text::_('COM_SERVICEDIRECTORY_SELECT_AN_OPTION'));
			}
			foreach($items as $item)
			{
				$options[] = Html::_('select.option', $item->guid, $item->file_type_name);
			}
		}
		return $options;
	}
}
