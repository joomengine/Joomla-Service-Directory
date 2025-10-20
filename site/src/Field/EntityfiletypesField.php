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
use JoomService\Joomla\Utilities\JsonHelper;
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Entityfiletypes Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class EntityfiletypesField extends ListField
{
	/**
	 * The entityfiletypes field type.
	 *
	 * @var        string
	 */
	public $type = 'Entityfiletypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// Get the user object.
		$user = Factory::getApplication()->getIdentity();
		// Get the database object.
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.guid','a.name','a.target'),array('guid','file_type_name','target')));
		$query->from($db->quoteName('#__servicedirectory_file_type', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order('a.name ASC');
		// Implement View Level Access (if set in table)
		if (!$user->authorise('core.options', 'com_servicedirectory'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		$db->setQuery((string)$query);
		$items = $db->loadObjectList();
		$entity = $this->getAttribute('entity');
		$options = [];
		if ($items)
		{
			if ($this->multiple === false)
			{
				$options[] = Html::_('select.option', '', Text::_('COM_SERVICEDIRECTORY_SELECT_AN_OPTION'));
			}
			foreach($items as $item)
			{
				if (JsonHelper::check($item->target))
				{
					$item->target = json_decode($item->target, true);
				}
				if (ArrayHelper::check($item->target) && in_array($entity, $item->target))
				{
					$options[] = Html::_('select.option', $item->guid, $item->file_type_name);
				}
			}
		}
		if (count($options) <= 1)
		{
			return [Html::_('select.option', '', Text::sprintf('COM_SERVICEDIRECTORY_NO_FILE_TYPES_LINKED_TO_THE_S_AREA', $entity))];
		}
		return $options;
	}
}
