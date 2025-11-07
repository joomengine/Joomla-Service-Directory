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
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Joomla\Servicedirectory\Utilities\Permitted\Actions;
use Joomla\CMS\User\UserFactoryInterface;

// No direct access to this file
defined('_JEXEC') or die;

$edit = "index.php?option=com_servicedirectory&view=addresses&task=address.edit";

?>
<?php foreach ($this->items as $i => $item): ?>
	<?php
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
		$userChkOut = Factory::getContainer()->
			get(UserFactoryInterface::class)->
				loadUserById((int) ($item->checked_out ?? 0));
		$canDo = Actions::get('address', $item, 'addresses');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="order nowrap center hidden-phone">
		<?php if (!$this->isModal && $canDo->get('address.edit.state')): ?>
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
		<?php if (!$this->isModal && $canDo->get('address.edit')): ?>
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
			<div class="name">
				<?php if (!$this->isModal && $canDo->get('address.edit')): ?>
					<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->line_one); ?></a>
					<?php if ($item->checked_out): ?>
						<?php echo Html::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'addresses.', $canCheckin); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php if (!$this->isModal): ?>
						<?php echo $this->escape($item->line_one); ?>
					<?php else: ?>
						<?php
							$link = "{$edit}&id={$item->id}";
							$dataId = $item->{$this->getModalTitleKey()} ?? 0;
							$itemHtml = '<a href="' . $this->escape($link, false) . '">' . $this->escape($item->line_one, false) . '</a>';
							$attribs = 'data-content-select data-content-type="com_servicedirectory.address"'
								. ' data-id="' . $dataId . '"'
								. ' data-title="' . $this->escape($item->line_one, false) . '"'
								. ' data-uri="' . $this->escape($link, false) . '"'
								. ' data-html="' . $this->escape($itemHtml, false) . '"';
						?>
						<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
							<?php echo $this->escape($item->line_one); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="hidden-phone">
			<?php echo $this->escape($item->type_name); ?>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('country.edit', 'com_servicedirectory.country.' . (int) ($item->country_id ?? 0))): ?>
					<a href="index.php?option=com_servicedirectory&view=countries&task=country.edit&id=<?php echo $item->country_id; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->country_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->country_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('state.edit', 'com_servicedirectory.state.' . (int) ($item->state_id ?? 0))): ?>
					<a href="index.php?option=com_servicedirectory&view=states&task=state.edit&id=<?php echo $item->state_id; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->state_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->state_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('city.edit', 'com_servicedirectory.city.' . (int) ($item->city_id ?? 0))): ?>
					<a href="index.php?option=com_servicedirectory&view=cities&task=city.edit&id=<?php echo $item->city_id; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->city_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->city_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="nowrap">
			<div class="name">
				<?php if (!$this->isModal && $this->user->authorise('core.edit', 'com_servicedirectory.company.' . (int) ($item->company_id ?? 0))): ?>
					<a href="index.php?option=com_servicedirectory&view=companies&task=company.edit&id=<?php echo $item->company_id; ?>&return=<?php echo $this->return_here; ?>"><?php echo $this->escape($item->company_name); ?></a>
				<?php else: ?>
					<?php echo $this->escape($item->company_name); ?>
				<?php endif; ?>
			</div>
		</td>
		<td class="center">
		<?php if (!$this->isModal && $canDo->get('address.edit.state')) : ?>
				<?php if ($item->checked_out) : ?>
					<?php if ($canCheckin) : ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'addresses.', true, 'cb'); ?>
					<?php else: ?>
						<?php echo Html::_('jgrid.published', $item->published, $i, 'addresses.', false, 'cb'); ?>
					<?php endif; ?>
				<?php else: ?>
					<?php echo Html::_('jgrid.published', $item->published, $i, 'addresses.', true, 'cb'); ?>
				<?php endif; ?>
		<?php else: ?>
			<?php echo Html::_('jgrid.published', $item->published, $i, 'addresses.', false, 'cb'); ?>
		<?php endif; ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>