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


/***[JCBGUI.layout.php_view.149.$$$$]***/
$areas = [
	'contactname' => 'COM_SERVICEDIRECTORY_CONTACT_NAME',
	'company_type' => 'COM_SERVICEDIRECTORY_COMPANY_TYPE',
	'companysize' => 'COM_SERVICEDIRECTORY_COMPANY_SIZE',
	'chamber_of_commerce' => 'COM_SERVICEDIRECTORY_CHAMBER_OF_COMMERCE',
];/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.layout.layout.149.$$$$]-->
<ul class="list-unstyled small mb-0">
<?php foreach ($areas as $area => $label): ?>
	<?php if (!empty($displayData->{$area})): ?>
		<li><strong><?php echo Text::_($label); ?>:</strong> <?php echo $displayData->{$area}; ?></li>
	<?php endif; ?>
<?php endforeach; ?>
</ul><!--[/JCBGUI$$$$]-->

