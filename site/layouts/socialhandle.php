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



?>

<!--[JCBGUI.layout.layout.148.$$$$]-->
<?php if (!empty($displayData->name) && !empty($displayData->handle) && !empty($displayData->link)) : ?>
    <span class="d-inline-flex align-items-center">
		<span class="fw-semibold me-1"><?php echo $displayData->name; ?></span>
		<a href="<?php echo $displayData->link; ?>" class="link-primary text-decoration-none">
			<?php echo $displayData->handle; ?>
		</a>
	</span>
<?php endif; ?><!--[/JCBGUI$$$$]-->

