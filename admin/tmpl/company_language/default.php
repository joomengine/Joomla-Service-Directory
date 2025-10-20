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
<div id="servicedirectory_loader">
<form action="<?php echo Route::_('index.php?option=com_servicedirectory&&layout=' . $layout . $tmpl . '&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'company_languageTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'company_languageTab', 'details', Text::_('COM_SERVICEDIRECTORY_COMPANY_LANGUAGE_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company_language.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company_language.details_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'company_languageTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('company_language.edit.created_by') || $this->canDo->get('company_language.edit.created') || $this->canDo->get('company_language.edit.state') || ($this->canDo->get('company_language.delete') && $this->canDo->get('company_language.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'company_languageTab', 'publishing', Text::_('COM_SERVICEDIRECTORY_COMPANY_LANGUAGE_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company_language.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company_language.publlshing', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'company_languageTab', 'permissions', Text::_('COM_SERVICEDIRECTORY_COMPANY_LANGUAGE_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_SERVICEDIRECTORY_COMPANY_LANGUAGE_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="company_language.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>
