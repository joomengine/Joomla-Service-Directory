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

$client_name = $displayData->client_name ?? '';
$project_title = $displayData->project_title ?? '';
$target_industry = $displayData->target_industry ?? '';
$services_provided = $displayData->services_provided ?? '';
$project_url = $displayData->project_url ?? '';
$portfolio_desc = $displayData->description ?? '';

?>
<div class="row g-4">
	<div class="col-12 col-lg-8">
		<?php if (!empty($project_title)): ?>
			<h4 class="mb-2"><?php echo $project_title; ?></h4>
		<?php endif; ?>

		<ul class="list-unstyled small mb-3">
			<?php if (!empty($client_name)): ?>
				<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_CLIENT'); ?>:</strong> <?php echo $client_name; ?></li>
			<?php endif; ?>
			<?php if (!empty($target_industry)): ?>
				<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_INDUSTRY'); ?>:</strong> <?php echo $target_industry; ?></li>
			<?php endif; ?>
			<?php if (!empty($services_provided)): ?>
				<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_SERVICES'); ?>:</strong> <?php echo $services_provided; ?></li>
			<?php endif; ?>
			<?php if (!empty($project_url)): ?>
				<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_URL'); ?>:</strong> <a href="<?php echo $project_url; ?>"><?php echo $project_url; ?></a></li>
			<?php endif; ?>
		</ul>

		<?php if (trim($portfolio_desc) !== ''): ?>
			<div class="text-muted">
				<?php echo $portfolio_desc; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
