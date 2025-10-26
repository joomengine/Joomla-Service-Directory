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

$comment   = $displayData['comment'] ?? null;
$isCurrent = !empty($displayData['isCurrent']);

if (!$comment)
{
	return;
}

$text  = $comment->comment;
$time  = Html::_('date', $comment->created, 'g:i a');
$shade = $isCurrent ? 'border-primary' : 'border-secondary';

?>
<div class="p-3 mb-2 bg-white rounded border-start border-4 <?php echo $shade; ?>">
	<div class="lh-base">
		<?php echo $text; ?>
	</div>
	<div class="mt-2 text-end">
		<small class="text-muted"><?php echo $time; ?></small>
	</div>
</div>
