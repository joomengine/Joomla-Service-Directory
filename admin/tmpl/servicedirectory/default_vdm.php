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

use Joomla\CMS\Language\Text;
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
defined('_JEXEC') or die;

?>
<div class="com-servicedirectory-dashboard-details">
	<div class="com-servicedirectory-dashboard-details__image mb-4">
		<img
			class="img-fluid w-100"
			alt="<?php echo Text::_('COM_SERVICEDIRECTORY'); ?>"
			src="components/com_servicedirectory/assets/images/vdm-component.jpg"
			loading="lazy"
			decoding="async"
		>
	</div>
	<ul class="list-group list-group-flush mb-4">
		<li class="list-group-item d-flex flex-wrap justify-content-between gap-2">
			<span>
				<strong><?php echo Text::_('COM_SERVICEDIRECTORY_VERSION'); ?>:</strong>
				<?php echo $this->manifest->version; ?>
			</span>
			<span class="update-notice" id="component-update-notice"></span>
		</li>
		<li class="list-group-item">
			<strong><?php echo Text::_('COM_SERVICEDIRECTORY_DATE'); ?>:</strong>
			<?php echo $this->manifest->creationDate; ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo Text::_('COM_SERVICEDIRECTORY_AUTHOR'); ?>:</strong>
			<a href="mailto:<?php echo $this->manifest->authorEmail; ?>">
				<?php echo $this->manifest->author; ?>
			</a>
		</li>
		<li class="list-group-item">
			<strong><?php echo Text::_('COM_SERVICEDIRECTORY_WEBSITE'); ?>:</strong>
			<a
				href="<?php echo $this->manifest->authorUrl; ?>"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php echo $this->manifest->authorUrl; ?>
			</a>
		</li>
		<li class="list-group-item">
			<strong><?php echo Text::_('COM_SERVICEDIRECTORY_LICENSE'); ?>:</strong>
			<?php echo $this->manifest->license; ?>
		</li>
		<li class="list-group-item">
			<strong><?php echo $this->manifest->copyright; ?></strong>
		</li>
	</ul>
	<?php if (ArrayHelper::check($this->contributors)) : ?>
		<div class="com-servicedirectory-dashboard-details__contributors mt-4">
			<h3 class="h5 mb-3">
				<?php if (count($this->contributors) > 1) : ?>
					<?php echo Text::_('COM_SERVICEDIRECTORY_CONTRIBUTORS'); ?>
				<?php else : ?>
					<?php echo Text::_('COM_SERVICEDIRECTORY_CONTRIBUTOR'); ?>
				<?php endif; ?>
			</h3>
			<ul class="list-group list-group-flush">
				<?php foreach ($this->contributors as $contributor) : ?>
					<li class="list-group-item">
						<strong><?php echo $contributor['title']; ?>:</strong>
						<?php echo $contributor['name']; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>