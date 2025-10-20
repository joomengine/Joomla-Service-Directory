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



use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\LayoutHelper;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
defined('JPATH_BASE') or die;

$areas = [
	'email' => (object) ['label' => 'COM_SERVICEDIRECTORY_EMAIL', 'href' => 'mailto:'],
	'phone' => (object) ['label' => 'COM_SERVICEDIRECTORY_PHONE', 'href' => 'tel:'],
	'website' => (object) ['label' => 'COM_SERVICEDIRECTORY_WEBSITE', 'href' => ''],
];

?>
<ul class="list-unstyled small mb-0">
<?php foreach ($areas as $area => $target): ?>
	<?php if (!empty($displayData->{$area})): ?>
		<li>
			<strong><?php echo Text::_($target->label); ?>:</strong>
			<a
				href="<?php echo $target->href . $displayData->{$area}; ?>"
				target="_blank"
				title="<?php echo Text::_($target->label); ?>"
			>
				<?php echo $displayData->{$area}; ?>
			</a>
		</li>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
