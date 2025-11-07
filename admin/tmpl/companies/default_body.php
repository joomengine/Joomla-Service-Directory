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
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Joomla\Servicedirectory\Utilities\Permitted\Actions;
use Joomla\CMS\User\UserFactoryInterface;

// No direct access to this file
defined('_JEXEC') or die;

$edit = "index.php?option=com_servicedirectory&view=companies&task=company.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
	<?php
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
		$userChkOut = Factory::getContainer()->
			get(UserFactoryInterface::class)->
				loadUserById((int) ($item->checked_out ?? 0));
		$canDo = Actions::get('company', $item, 'companies');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
		<?php if (!$this->isModal && $canDo->get('core.edit.state')): ?>
			<?php
				$iconClass = '';
				if (!$this->saveOrder)
				{
					$iconClass = ' inactive tip-top" hasTooltip" title="' . Html::tooltipText('JORDERINGDISABLED');
				}
			?>
			<span class="sortable-handler<?php echo $iconClass; ?>">
				<i class="icon-menu"></i>
			</span>
			<?php if ($this->saveOrder) : ?>
				<input type="text" style="display:none" name="order[]" size="5"
				value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
			<?php endif; ?>
		<?php else: ?>
			&#8942;
		<?php endif; ?>
		</td>
		<td class="nowrap center">
		<?php if (!$this->isModal && $canDo->get('core.edit')): ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo Html::_('grid.id', $i, $item->id); ?>
					<?php else: ?>
						&#9633;
					<?php endif; ?>
				<?php else: ?>
					<?php echo Html::_('grid.id', $i, $item->id); ?>
				<?php endif; ?>
		<?php else: ?>
			&#9633;
		<?php endif; ?>
		</td>
		<td class="nowrap">
			<div>
			<?php if (!$this->isModal && $canDo->get('core.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->name); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo Html::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'companies.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php if (!$this->isModal): ?>
					<?php echo $this->escape($item->name); ?>
				<?php else: ?>
					<?php
						$link = "{$edit}&id={$item->id}";
						$dataId = $item->{$this->getModalTitleKey()} ?? 0;
						$itemHtml = '<a href="' . $this->escape($link, false) . '">' . $this->escape($item->name, false) . '</a>';
						$attribs = 'data-content-select data-content-type="com_servicedirectory.company"'
							. ' data-id="' . $dataId . '"'
							. ' data-title="' . $this->escape($item->name, false) . '"'
							. ' data-uri="' . $this->escape($link, false) . '"'
							. ' data-html="' . $this->escape($itemHtml, false) . '"';
					?>
					<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
						<?php echo $this->escape($item->name); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?><?php if (!empty($item->company_type)): ?> <small>
			<?php echo $this->escape($item->company_type); ?></small><?php endif; ?>
			<small><ul class="list-unstyled">
			<?php if (!empty($item->chamber_of_commerce)): ?><li><?php echo Text::_('COM_SERVICEDIRECTORY_CHAMBER_OF_COMMERCE'); ?>: <b> 
			<?php echo $this->escape($item->chamber_of_commerce); ?></b></li><?php endif; ?>
			<?php if (!empty($item->companysize)): ?><li><?php echo Text::_('COM_SERVICEDIRECTORY_COMPANY_SIZE'); ?>:  <b>
			<?php echo Text::_($item->companysize); ?><b/></li><?php endif; ?>
			</ul></small>
			</div>
		</td>
		<td class="hidden-phone">
			<div><small><ul class="list-unstyled">
			<?php if (!empty($item->contactname)): ?><li>
			<?php echo $this->escape($item->contactname); ?></li><?php endif; ?>
			<?php if (!empty($item->email)): ?><li>
			<?php echo $this->escape($item->email); ?></li><?php endif; ?>
			<?php if (!empty($item->phone)): ?><li>
			<?php echo $this->escape($item->phone); ?></li><?php endif; ?>
			<?php if (!empty($item->website)): ?><li>
			<?php echo $this->escape($item->website); ?></li><?php endif; ?>
			</ul></small>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('category.edit', 'com_servicedirectory.category.' . (int) ($item->category_id ?? 0))): ?>
					<a href="index.php?option=com_servicedirectory&view=categories&task=category.edit&id=<?php echo $item->category_id; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->category_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->category_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('core.edit', 'com_users')): ?>
					<a href="index.php?option=com_users&task=user.edit&id=<?php echo (int) $item->created_by ?>"><?php echo Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) ($item->created_by ?? 0))->name; ?></a>
				<?php else: ?>
					<?php echo Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById((int) ($item->created_by ?? 0))->name; ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="center">
		<?php if (!$this->isModal && $canDo->get('core.edit.state')) : ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'companies.', true, 'cb'); ?>
					<?php else: ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'companies.', false, 'cb'); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php echo Html::_('jgrid.published', $item->published, $i, 'companies.', true, 'cb'); ?>
				<?php endif; ?>
		<?php else: ?>
			<?php echo Html::_('jgrid.published', $item->published, $i, 'companies.', false, 'cb'); ?>
		<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>