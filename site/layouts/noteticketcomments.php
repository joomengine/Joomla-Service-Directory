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

// Extract data
$bundle = $displayData['bundle'] ?? [];
$userId = (int) ($displayData['userId'] ?? 0);

if (empty($bundle))
{
	return;
}

$first = reset($bundle);
$authorName = $first->name;
$isCurrent  = ((int) $first->created_by === $userId);

// Choose Bootstrap contextual tone
$bgClass   = $isCurrent ? 'bg-primary-subtle' : 'bg-body-secondary';
$textClass = $isCurrent ? 'text-dark' : 'text-body';

?>
<div class="w-100 py-3 mb-3 border-bottom <?php echo $bgClass; ?>">
	<div class="container-fluid px-3">
		<div class="row">
			<div class="col-12">
				<div class="fw-semibold mb-2 text-secondary">
					<?php echo $authorName; ?>
				</div>
				<div class="<?php echo $textClass; ?> lh-base">
					<?php foreach ($bundle as $comment): ?>
						<?php
							echo LayoutHelper::render('noteticketcomment',
								[
									'comment' => $comment,
									'isCurrent' => $isCurrent,
								]
							);
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>
