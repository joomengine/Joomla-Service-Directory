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

use Joomla\CMS\Layout\LayoutHelper;

// No direct access to this file
defined('_JEXEC') or die;

$displayData = [
	'textPrefix' => 'COM_SERVICEDIRECTORY_PLATFORMS',
	'formURL'    => 'index.php?option=com_servicedirectory&view=platforms',
	'icon'       => 'icon-wifi',
];

if ($this->user->authorise('platform.create', 'com_servicedirectory'))
{
	$displayData['createURL'] = 'index.php?option=com_servicedirectory&task=platform.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
