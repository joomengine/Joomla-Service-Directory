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

?>
<div class="servicedirectory-company">
<?php echo $this->toolbar->render(); ?>
<form action="<?php echo Route::_('index.php?option=com_servicedirectory&layout=edit&id='. (int) $this->item->id . $this->referral); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

<?php echo LayoutHelper::render('company.details_above', $this); ?>
<div class="main-card">

	<?php echo Html::_('uitab.startTabSet', 'companyTab', ['active' => 'details', 'recall' => true]); ?>

	<?php echo Html::_('uitab.addTab', 'companyTab', 'details', Text::_('COM_SERVICEDIRECTORY_COMPANY_DETAILS', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.details_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.details_right', $this); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('company.details_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'companyTab', 'contact_info', Text::_('COM_SERVICEDIRECTORY_COMPANY_CONTACT_INFO', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.contact_info_left', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.contact_info_right', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'companyTab', 'media', Text::_('COM_SERVICEDIRECTORY_COMPANY_MEDIA', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('company.media_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php echo Html::_('uitab.addTab', 'companyTab', 'portfolio', Text::_('COM_SERVICEDIRECTORY_COMPANY_PORTFOLIO', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('company.portfolio_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>

	<?php if ($this->canDo->get('ticket.access')) : ?>
	<?php echo Html::_('uitab.addTab', 'companyTab', 'support', Text::_('COM_SERVICEDIRECTORY_COMPANY_SUPPORT', true)); ?>
		<div class="row">
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo LayoutHelper::render('company.support_fullwidth', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php $this->ignore_fieldsets = array('details','metadata','vdmmetadata','accesscontrol'); ?>
	<?php $this->tab_name = 'companyTab'; ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

	<?php if ($this->canDo->get('core.edit.created_by') || $this->canDo->get('core.edit.created') || $this->canDo->get('core.edit.state') || ($this->canDo->get('core.delete') && $this->canDo->get('core.edit.state'))) : ?>
	<?php echo Html::_('uitab.addTab', 'companyTab', 'publishing', Text::_('COM_SERVICEDIRECTORY_COMPANY_PUBLISHING', true)); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.publishing', $this); ?>
			</div>
			<div class="col-md-6">
				<?php echo LayoutHelper::render('company.metadata', $this); ?>
			</div>
		</div>
	<?php echo Html::_('uitab.endTab'); ?>
	<?php endif; ?>

	<?php if ($this->canDo->get('core.admin')) : ?>
	<?php echo Html::_('uitab.addTab', 'companyTab', 'permissions', Text::_('COM_SERVICEDIRECTORY_COMPANY_PERMISSION', true)); ?>
		<div class="row">
			<div class="col-md-12">
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_SERVICEDIRECTORY_COMPANY_PERMISSION'); ?></legend>
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
		<input type="hidden" name="task" value="company.edit" />
		<?php echo Html::_('form.token'); ?>
	</div>
</div>
</form>
</div>
