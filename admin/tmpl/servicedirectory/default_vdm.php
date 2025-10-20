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
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
defined('_JEXEC') or die;

?>
<img alt="<?php echo Text::_('COM_SERVICEDIRECTORY'); ?>" width="100%"  src="components/com_servicedirectory/assets/images/vdm-component.jpg">
<ul class="list-striped">
	<li><b><?php echo Text::_('COM_SERVICEDIRECTORY_VERSION'); ?>:</b> <?php echo $this->manifest->version; ?>&nbsp;&nbsp;<span class="update-notice" id="component-update-notice"></span></li>
	<li><b><?php echo Text::_('COM_SERVICEDIRECTORY_DATE'); ?>:</b> <?php echo $this->manifest->creationDate; ?></li>
	<li><b><?php echo Text::_('COM_SERVICEDIRECTORY_AUTHOR'); ?>:</b> <a href="mailto:<?php echo $this->manifest->authorEmail; ?>"><?php echo $this->manifest->author; ?></a></li>
	<li><b><?php echo Text::_('COM_SERVICEDIRECTORY_WEBSITE'); ?>:</b> <a href="<?php echo $this->manifest->authorUrl; ?>" target="_blank"><?php echo $this->manifest->authorUrl; ?></a></li>
	<li><b><?php echo Text::_('COM_SERVICEDIRECTORY_LICENSE'); ?>:</b> <?php echo $this->manifest->license; ?></li>
	<li><b><?php echo $this->manifest->copyright; ?></b></li>
</ul>
<div class="clearfix"></div>
<?php if(ArrayHelper::check($this->contributors)): ?>
	<?php if(count($this->contributors) > 1): ?>
		<h3><?php echo Text::_('COM_SERVICEDIRECTORY_CONTRIBUTORS'); ?></h3>
	<?php else: ?>
		<h3><?php echo Text::_('COM_SERVICEDIRECTORY_CONTRIBUTOR'); ?></h3>
	<?php endif; ?>
	<ul class="list-striped">
		<?php foreach($this->contributors as $contributor): ?>
		<li><b><?php echo $contributor['title']; ?>:</b> <?php echo $contributor['name']; ?></li>
		<?php endforeach; ?>
	</ul>
	<div class="clearfix"></div>
<?php endif; ?>