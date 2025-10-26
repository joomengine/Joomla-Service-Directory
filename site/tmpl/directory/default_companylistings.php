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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;

// No direct access to this file
defined('_JEXEC') or die;

?>
<div class="container my-4">
	<div class="alert alert-secondary mb-4" role="alert">
		<?php echo Text::sprintf('COM_SERVICEDIRECTORY_WELCOME_BACK_S', $this->escape($this->user->name, false)); ?>
	</div>
	<h4 class="mb-3"><?php echo Text::_('COM_SERVICEDIRECTORY_YOUR_COMPANY_LISTINGS'); ?></h4>
	<?php if (!empty($this->mine) && !empty($this->tickets)) : ?>
		<?php echo Html::_('uitab.startTabSet', 'company_listings', ['active' => 'companies', 'recall' => true]); ?>
			<?php $label = (count((array) $this->mine) == 1) ? Text::_('COM_SERVICEDIRECTORY_COMPANY') : Text::_('COM_SERVICEDIRECTORY_COMPANIES'); ?>
			<?php echo Html::_('uitab.addTab', 'company_listings', 'companies', $label); ?>
				<?php echo LayoutHelper::render('companylistings', $this); ?>
			<?php echo Html::_('uitab.endTab'); ?>
			<?php $label = (count((array) $this->tickets) == 1) ? Text::_('COM_SERVICEDIRECTORY_TICKET') : Text::_('COM_SERVICEDIRECTORY_TICKETS'); ?>
			<?php echo Html::_('uitab.addTab', 'company_listings', 'tickets', $label); ?>
				<?php echo LayoutHelper::render('servicedirectorytickets', $this); ?>
			<?php echo Html::_('uitab.endTab'); ?>
		<?php echo Html::_('uitab.endTabSet'); ?>
	<?php elseif (!empty($this->mine)) : ?>
		<?php echo LayoutHelper::render('companylistings', $this); ?>
	<?php elseif (!empty($this->tickets)) : ?>
		<?php echo LayoutHelper::render('servicedirectorytickets', $this); ?>
	<?php else : ?>
		<div class="alert alert-info mt-4" role="alert">
			<?php echo Text::_('COM_SERVICEDIRECTORY_YOU_CURRENTLY_HAVE_NO_COMPANY_LISTINGS_ADD_YOUR_FIRST_COMPANY_TODAY'); ?>
		</div>
	<?php endif; ?>
</div>
