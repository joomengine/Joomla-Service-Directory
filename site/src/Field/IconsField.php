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

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Icons Form Field class for the Servicedirectory component
 *
 * @since  1.6
 */
class IconsField extends ListField
{
	/**
	 * The icons field type.
	 *
	 * @var        string
	 */
	public $type = 'Icons';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array    An array of Html options.
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$icons = [
			'facebook'  => 'Facebook',
			'instagram' => 'Instagram',
			'twitter'   => 'Twitter / X',
			'linkedin'  => 'LinkedIn',
			'youtube'   => 'YouTube',
			'whatsapp'  => 'WhatsApp',
			'telegram'  => 'Telegram',
			'tiktok'    => 'TikTok',
			'github'    => 'GitHub',
			'discord'   => 'Discord',
			'slack'     => 'Slack',
			'email'     => 'Email',
			'rss'       => 'RSS',
		];

		$options = [];

		foreach ($icons as $value => $label)
		{
			$options[] = Html::_('select.option', $value, Text::_($label));
		}

		return $options;
	}
}
