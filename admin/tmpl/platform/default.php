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

<?php echo LayoutHelper::render('platform.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'platformTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'platformTab', 'details', Text::_('COM_SERVICEDIRECTORY_PLATFORM_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('platform.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('platform.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('platform.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'platformTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('platform.edit.created_by') || $this->canDo->get('platform.edit.created') || $this->canDo->get('platform.edit.state') || ($this->canDo->get('platform.delete') && $this->canDo->get('platform.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'platformTab', 'publishing', Text::_('COM_SERVICEDIRECTORY_PLATFORM_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('platform.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('platform.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'platformTab', 'permissions', Text::_('COM_SERVICEDIRECTORY_PLATFORM_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_SERVICEDIRECTORY_PLATFORM_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="platform.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>

<script type="text/javascript">



    jQuery(function($){

        function updateIconClass(select) {
            const $select = $(select);
            const value = $select.val();

            // Remove any previous Bootstrap icon class
            $select.removeClass(function(i, c) {
                return (c.match(/\bbi-\S+/g) || []).join(' ');
            });

            // Add new one if selected
            if (value) {
                $select.addClass('bi bi-' + value);
            }
        }

        // Initialize for all icon selects
        $('.bi-icon-field').each(function(){
            updateIconClass(this);
        });

        // Update on change
        $(document).on('change', '.bi-icon-field', function(){
            updateIconClass(this);
        });

        // For Joomla subform dynamically added rows
        $(document).on('subform-row-add', function(e, row){
            $(row).find('.bi-icon-field').each(function(){
                updateIconClass(this);
            });
        });
    });
</script>
