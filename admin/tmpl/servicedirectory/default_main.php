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

// No direct access to this file
defined('_JEXEC') or die;

?>
<?php if (isset($this->icons['main']) && is_array($this->icons['main']) && !empty($this->icons['main'])) : ?>
    <div class="dashboard-icons" role="list">
		<?php foreach ($this->icons['main'] as $icon) : ?>
            <div class="dashboard-icon-item" role="listitem">
                <a class="dashboard-icon-link" href="<?php echo $icon->url; ?>">
					<span class="dashboard-icon-image">
						<img
                            alt="<?php echo $icon->alt; ?>"
                            src="components/com_servicedirectory/assets/images/icons/<?php echo $icon->image; ?>"
                            loading="lazy"
                            decoding="async"
                        >
					</span>
                    <span class="dashboard-icon-title">
						<?php echo Text::_($icon->name); ?>
					</span>
                </a>
            </div>
		<?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="alert alert-danger">
        <h4 class="alert-heading">
			<?php echo Text::_("Permission denied, or not correctly set"); ?>
        </h4>
        <div>
			<?php echo Text::_("Please notify your System Administrator if result is unexpected."); ?>
        </div>
    </div>
<?php endif; ?>