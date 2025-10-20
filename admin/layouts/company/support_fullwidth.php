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
use JoomService\Joomla\Utilities\StringHelper;
use JoomService\Joomla\Utilities\ArrayHelper;
use Joomla\CMS\User\UserFactoryInterface;

// No direct access to this file
defined('_JEXEC') or die;

// set the defaults
$items = $displayData->vvvsupport;
$user = Factory::getApplication()->getIdentity();
$id = $displayData->item->id;
// set the edit URL
$edit = "index.php?option=com_servicedirectory&view=tickets&task=ticket.edit";
// set a return value
$return = ($id) ? "index.php?option=com_servicedirectory&view=company&layout=edit&id=" . $id : "";
// check for a return value
$jinput = Factory::getApplication()->input;
if ($_return = $jinput->get('return', null, 'base64'))
{
	$return .= "&return=" . $_return;
}
// get the GUID value
$guid = $displayData->item->guid ?? null;
// check if return value was set
if (StringHelper::check($return))
{
	// set the referral values
	$ref = $guid ? "&init_defaults=" . urlencode('{"company":"' . $guid . '"}') . "&return=" . urlencode(base64_encode($return)) : "&return=" . urlencode(base64_encode($return));
}
else
{
	$ref = $guid ? "&init_defaults=" . urlencode('{"company":"' . $guid . '"}') : "";
}
// set the create new URL
$new = "index.php?option=com_servicedirectory&view=tickets&task=ticket.add" . $ref;
// load the action object
$can = ServicedirectoryHelper::getActions('ticket');

?>
<div class="form-vertical">
<?php if ($can->get('ticket.create')): ?>
	<a class="btn btn-small btn-success" href="<?php echo $new; ?>"><span class="icon-new icon-white"></span> <?php echo Text::_('COM_SERVICEDIRECTORY_NEW'); ?></a><br /><br />
<?php endif; ?>
<?php if (ArrayHelper::check($items)): ?>
<table class="footable table data tickets metro-blue" data-page-size="20" data-filter="#filter_tickets">
<thead>
	<tr>
		<th data-toggle="true">
			<?php echo Text::_('COM_SERVICEDIRECTORY_TICKET_SUBJECT_LABEL'); ?>
		</th>
		<th data-hide="phone">
			<?php echo Text::_('COM_SERVICEDIRECTORY_TICKET_COMPANY_LABEL'); ?>
		</th>
		<th data-hide="phone">
			<?php echo Text::_('COM_SERVICEDIRECTORY_TICKET_PUBLISHED_LABEL'); ?>
		</th>
		<th width="5" data-type="numeric" data-hide="phone,tablet">
			<?php echo Text::_('COM_SERVICEDIRECTORY_TICKET_ID'); ?>
		</th>
	</tr>
</thead>
<tbody>
<?php foreach ($items as $i => $item): ?>
	<?php
		$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
		$userChkOut = Factory::getContainer()->
			get(UserFactoryInterface::class)->
				loadUserById((int) ($item->checked_out ?? 0));
		$canDo = ServicedirectoryHelper::getActions('ticket',$item,'tickets');
	?>
	<tr>
		<td>
			<?php if (!$displayData->isModal && $canDo->get('ticket.edit')): ?>
				<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?><?php echo $ref; ?>"><?php echo $displayData->escape($item->subject); ?></a>
				<?php if ($item->checked_out): ?>
					<?php echo Html::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'tickets.', $canCheckin); ?>
				<?php endif; ?>
			<?php else: ?>
				<?php if (!$displayData->isModal): ?>
					<?php echo $displayData->escape($item->subject); ?>
				<?php else: ?>
					<?php
						$link = "{$edit}&id={$item->id}";
						$dataId = $item->{$displayData->getModalTitleKey()} ?? 0;
						$itemHtml = '<a href="' . $displayData->escape($link, false) . '">' . $displayData->escape($item->subject, false) . '</a>';
						$attribs = 'data-content-select data-content-type="com_servicedirectory.ticket"'
							. ' data-id="' . $dataId . '"'
							. ' data-title="' . $displayData->escape($item->subject, false) . '"'
							. ' data-uri="' . $displayData->escape($link, false) . '"'
							. ' data-html="' . $displayData->escape($itemHtml, false) . '"';
					?>
					<a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
						<?php echo $displayData->escape($item->subject); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</td>
		<td>
			<?php echo $displayData->escape($item->company_name); ?>
		</td>
		<td>
			<?php echo Text::_($item->published); ?>
		</td>
		<td class="nowrap center hidden-phone">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
<tfoot class="hide-if-no-paging">
	<tr>
		<td colspan="4">
			<div class="pagination pagination-centered"></div>
		</td>
	</tr>
</tfoot>
</table>
<?php else: ?>
	<div class="alert alert-no-items">
		<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php endif; ?>
</div>
