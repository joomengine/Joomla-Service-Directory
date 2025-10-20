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
use JoomService\Joomla\Utilities\MimeHelper;
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Allowedfileformats Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class AllowedfileformatsField extends ListField
{
	/**
	 * The allowedfileformats field type.
	 *
	 * @var        string
	 */
	public $type = 'Allowedfileformats';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		
		// Start the options array
		$options = [];
		// Get the extensions list.
		$extensionList = MimeHelper::getFileExtensions('file', true);
		if (ArrayHelper::check($extensionList))
		{
			foreach($extensionList as $type => $extensions)
			{
				foreach($extensions as $extension)
				{
					$options[] = Html::_('select.option', $extension, $extension . ' [ ' . $type . ' ]');
				}
			}
		}
		return $options;

	}
}
