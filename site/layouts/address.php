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
<?php if (!empty($displayData)): ?>
	<?php if (!empty($displayData->type)): ?>
		<h5 class="card-title mb-1"><?php echo $displayData->type; ?></h5>
	<?php endif; ?>
	<address class="mb-0">
		<div class="lh-sm d-flex flex-column">
			<?php if (!empty($displayData->line_one)): ?>
				<div class="fw-medium"><?php echo $displayData->line_one; ?></div>
			<?php endif; ?>
			<?php if (!empty($displayData->line_two)): ?>
				<div><?php echo $displayData->line_two; ?></div>
			<?php endif; ?>
			<?php if (!empty($displayData->city) || !empty($displayData->state) || !empty($displayData->postal)): ?>
				<div>
					<?php if (!empty($displayData->city))	echo $displayData->city; ?>
					<?php if (!empty($displayData->state)) echo (!empty($displayData->city) ? ', ' : '') . $displayData->state; ?>
					<?php if (!empty($displayData->postal)) echo ' ' . $displayData->postal; ?>
				</div>
			<?php endif; ?>
			<?php if (!empty($displayData->country)): ?>
				<div class="text-muted small"><?php echo $displayData->country; ?></div>
			<?php endif; ?>
		</div>
	</address>
<?php endif; ?>
