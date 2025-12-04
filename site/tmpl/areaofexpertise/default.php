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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Site\Helper\RouteHelper;

// No direct access to this file
defined('_JEXEC') or die;


$id = $this->input->getInt('id', 0);
$search_link = Route::_(RouteHelper::getAreaofexpertiseRoute($id)) . '/';
$search_value = $this->input->get('search', null, 'STRING');

?>
<form action="<?php echo Route::_('index.php?option=com_servicedirectory'); ?>" method="post" name="adminForm" id="adminForm">
<?php echo LayoutHelper::render('entitybanner', $this->banner ?? null); ?>
<?php echo LayoutHelper::render('searchbox', ['url' => $search_link, 'value' => $search_value]); ?>
<?php if (!empty($this->items)): ?>
	<?php echo $this->loadTemplate('companies'); ?>
<?php else: ?>
	<div class="container-xxl my-4">
		<div class="row">
			<div class="alert alert-warning mb-0" role="alert"><?php echo Text::_('COM_SERVICEDIRECTORY_NO_ITEMS_FOUND'); ?></div>
		</div>
	</div>
<?php endif; ?>

<?php
// Show only if there are items
if (!empty($this->items) && isset($this->pagination)) :
	$showLinks   = isset($this->pagination->pagesTotal) && $this->pagination->pagesTotal > 1;
	$showResults = $this->params->def('show_pagination_results', 1);
	?>
	<div class="pagination d-flex justify-content-between align-items-center flex-wrap mt-3">
		<?php if ($showLinks) : ?>
			<div class="page-links">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php else : ?>
			<!-- Keep layout stable when links are hidden -->
			<div class="page-links"></div>
		<?php endif; ?>

		<?php if ($showResults) : ?>
			<div class="page-info d-flex align-items-center justify-content-end text-end">
				<span class="me-2"><?php echo $this->pagination->getPagesCounter(); ?></span>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
<input type="hidden" name="task" value="" />
<?php echo Html::_('form.token'); ?>
</form>
