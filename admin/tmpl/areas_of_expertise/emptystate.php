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

use Joomla\CMS\Layout\LayoutHelper;

// No direct access to this file
defined('_JEXEC') or die;

$displayData = [
	'textPrefix' => 'COM_SERVICEDIRECTORY_AREAS_OF_EXPERTISE',
	'formURL'    => 'index.php?option=com_servicedirectory&view=areas_of_expertise',
	'icon'       => 'icon-comment',
];

if ($this->user->authorise('area_of_expertise.create', 'com_servicedirectory'))
{
	$displayData['createURL'] = 'index.php?option=com_servicedirectory&task=area_of_expertise.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
