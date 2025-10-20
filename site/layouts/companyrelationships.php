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

$areas = [
	'tags' => 'COM_SERVICEDIRECTORY_TAGS',
	'areas_of_expertise' => 'COM_SERVICEDIRECTORY_AREA_OF_EXPERTISE',
	'languages' => 'COM_SERVICEDIRECTORY_SPOKEN_LANGUAGES'
];

$category = (object) [
	'name' => $displayData->category_name,
	'link' =>  $displayData->category_link ?? '#'
];

?>
<div class="position-relative z-2">
<ul class="list-unstyled small mb-0">
<?php echo LayoutHelper::render('companyrelationship', ['items' => [$category], 'label' => 'COM_SERVICEDIRECTORY_CATEGORY']); ?>
<?php foreach ($areas as $area => $label): ?>
	<?php if (!empty($displayData->{$area})): ?>
		<?php echo LayoutHelper::render('companyrelationship', ['items' => $displayData->{$area}, 'label' => $label]); ?>
	<?php endif; ?>
<?php endforeach; ?>
</ul>
</div>
<br>
