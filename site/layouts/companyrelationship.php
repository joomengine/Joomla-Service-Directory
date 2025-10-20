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



use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
defined('JPATH_BASE') or die;

// Extract all keys from $displayData as individual variables.
extract($displayData);

// Set Blank Label if empty
$label ??= 'COM_SERVICEDIRECTORY_NO_LABEL';

$bucket = [];
foreach ($items as $item)
{
	$name = $item->name ?? null;
	$link = $item->link ?? null;
	if (!empty($link) && !empty($name))
	{
		$bucket[] = LayoutHelper::render('basicitemlink', (object) ['name' => $name, 'link' => $link]);
	}
}

?>
<?php if (!empty($bucket)): ?>
<li><strong><?php echo Text::_($label); ?>:</strong> <?php echo implode(' ', $bucket); ?></li>
<?php endif; ?>
