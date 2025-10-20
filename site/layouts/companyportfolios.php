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


/***[JCBGUI.layout.php_view.151.$$$$]***/
$tabId = 'portfolios-tabs-' . substr(md5(uniqid('', true)), 0, 8);
$activeId = $tabId . '-0';
$count = !empty($displayData) ? count((array) $displayData) : 0;/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.layout.layout.151.$$$$]-->
<?php if ($count > 0): ?>
<div class="container my-5">
	<?php if ($count == 1): ?>
		<h3 class="mb-3"><?php echo Text::_('COM_SERVICEDIRECTORY_PORTFOLIO'); ?></h3>
		<div class="card h-100">
			<div class="card-body">
				<?php echo LayoutHelper::render('companyportfolio', array_values($displayData)[0]); ?>
			</div>
		</div>
	<?php else: ?>
		<h3 class="mb-3"><?php echo Text::_('COM_SERVICEDIRECTORY_PORTFOLIOS'); ?></h3>
		<?php echo Html::_('uitab.startTabSet', $tabId, ['active' => $activeId, 'recall' => true]); ?>
		<?php foreach ($displayData as $idx => $portfolio): ?>
			<?php
			$targetId = $tabId . '-' . $idx;
			$label = !empty($portfolio->project_title)
				? $portfolio->project_title
				: (!empty($portfolio->client_name) ? $portfolio->client_name : 'Portfolio ' . ($idx + 1));
			?>
			<?php echo Html::_('uitab.addTab', $tabId, $targetId, $label); ?>
				<?php echo LayoutHelper::render('companyportfolio', $portfolio); ?>
			<?php echo Html::_('uitab.endTab'); ?>
		<?php endforeach; ?>
		<?php echo Html::_('uitab.endTabSet'); ?>
	<?php endif; ?>
</div>
<?php endif; ?><!--[/JCBGUI$$$$]-->

