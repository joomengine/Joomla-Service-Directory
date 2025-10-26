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

$items = $displayData->tickets ?? [];

?>
<div class="row g-4">
	<?php echo Html::_('bootstrap.startAccordion', 'tickets', ['active' => array_values($items)[0]->guid]); ?>
	<?php foreach ($items as $item) : ?>
		<?php
			$name = $displayData->escape($item->company_name, false) .
				'<br>-&nbsp;' . $displayData->escape($item->subject, false) .
				'<br>-&nbsp;' . Html::_('date', $item->created, Text::_('DATE_FORMAT_LC3')) .
				'<br>-&nbsp;' . $item->guid;
		?>
		<?php echo Html::_('bootstrap.addSlide', 'tickets', $name, $item->guid); ?>
			<?php echo LayoutHelper::render('noteticketconversation', ['comments' => $item->comments, 'user' => $displayData->user]); ?>
		<?php echo Html::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo Html::_('bootstrap.endAccordion'); ?>
</div>
