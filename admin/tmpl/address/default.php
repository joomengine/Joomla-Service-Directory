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
use Joomla\CMS\Router\Route;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
Html::_('bootstrap.tooltip');

// No direct access to this file
defined('_JEXEC') or die;

$layout  = $this->isModal ? 'modal' : 'edit';
$tmpl    = $this->input->get('tmpl');
$tmpl    = $tmpl ? '&tmpl=' . $tmpl : '';
?>
<script type="text/javascript">
	// waiting spinner
	var outerDiv = document.querySelector('body');
	var loadingDiv = document.createElement('div');
	loadingDiv.id = 'loading';
	loadingDiv.style.cssText = "background: rgba(255, 255, 255, .8) url('components/com_servicedirectory/assets/images/ajax.gif') 50% 35% no-repeat; top: " + (outerDiv.getBoundingClientRect().top + window.pageYOffset) + "px; left: " + (outerDiv.getBoundingClientRect().left + window.pageXOffset) + "px; width: " + outerDiv.offsetWidth + "px; height: " + outerDiv.offsetHeight + "px; position: fixed; opacity: 0.80; -ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=80); filter: alpha(opacity=80); display: none;";
	outerDiv.appendChild(loadingDiv);
	loadingDiv.style.display = 'block';
	// when page is ready remove and show
	window.addEventListener('load', function() {
		var componentLoader = document.getElementById('servicedirectory_loader');
		if (componentLoader) componentLoader.style.display = 'block';
		loadingDiv.style.display = 'none';
	});
</script>
<div id="servicedirectory_loader" style="display: none;">
<form action="<?php echo Route::_('index.php?option=com_servicedirectory&&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('address.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'addressTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'addressTab', 'details', Text::_('COM_SERVICEDIRECTORY_ADDRESS_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('address.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('address.details_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'addressTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('address.edit.created_by') || $this->canDo->get('address.edit.created') || $this->canDo->get('address.edit.state') || ($this->canDo->get('address.delete') && $this->canDo->get('address.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'addressTab', 'publishing', Text::_('COM_SERVICEDIRECTORY_ADDRESS_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('address.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('address.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'addressTab', 'permissions', Text::_('COM_SERVICEDIRECTORY_ADDRESS_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_SERVICEDIRECTORY_ADDRESS_PERMISSION'); ?></legend>
					<div>
						<?php echo $this->form->getInput('rules'); ?>
					</div>
				</fieldset>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php echo Html::_('uitab.endTabSet'); ?>

	<div>
		<input type="hidden" name="task" value="address.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>
