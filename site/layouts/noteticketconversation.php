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
use Joomla\CMS\Date\Date;

// No direct access to this file
defined('JPATH_BASE') or die;

// Extract data
$comments = $displayData['comments'] ?? [];
$user     = $displayData['user'] ?? null;
$userId   = (int) ($user?->id ?? 0);

// Group comments by day
$grouped = [];
foreach ($comments as $comment)
{
	try {
		$date = new Date($comment->created);
		$day  = $date->format('Y-m-d');
	} catch (Exception $e) {
		$day = date('Y-m-d');
	}
	$grouped[$day][] = $comment;
}

?>
<div class="container-fluid px-0">
	<div class="d-flex flex-column">
		<?php foreach ($grouped as $day => $items): ?>
			<div class="d-flex justify-content-center my-3">
				<span class="badge bg-light text-secondary border">
					<?php echo Html::_('date', $day, Text::_('DATE_FORMAT_LC3')); ?>
				</span>
			</div>
			<?php
			// Render bundles of consecutive comments by same author
			$bundles = [];
			$prevAuthor = null;
			foreach ($items as $comment)
			{
				if ($prevAuthor === $comment->created_by)
				{
					// Add to current bundle
					$bundles[count($bundles) - 1][] = $comment;
				}
				else
				{
					// Start new bundle
					$bundles[] = [$comment];
				}
				$prevAuthor = $comment->created_by;
			}
			foreach ($bundles as $bundle)
			{
				echo LayoutHelper::render('noteticketcomments',
					[
						'bundle'  => $bundle,
						'userId'  => $userId,
					]
				);
			}
			?>
		<?php endforeach; ?>
	</div>
</div>
