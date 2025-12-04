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
 * Areasofexpertise Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class AreasofexpertiseField extends ListField
{
	/**
	 * The areasofexpertise field type.
	 *
	 * @var        string
	 */
	public $type = 'Areasofexpertise';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
				// Get the databse object.
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.guid','a.name'),array('guid','areas_of_expertise_name')));
		$query->from($db->quoteName('#__servicedirectory_area_of_expertise', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
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
				$options[] = Html::_('select.option', $item->guid, $item->areas_of_expertise_name);
			}
		}
		return $options;
	}
}
