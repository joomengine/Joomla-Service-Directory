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
use JoomService\Joomla\Utilities\ArrayHelper;

// No direct access to this file
defined('_JEXEC') or die;

// get the login module
$this->modules = $this->getModules('servicedirectory-login', 'array');

?>
<?php if (ArrayHelper::check($this->modules)): ?>
	<?php foreach($this->modules as $module): ?>
		<?php echo LayoutHelper::render('panelbox', $module); ?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="uk-alert uk-alert-large">
		<h2><?php echo Text::_('COM_SERVICEDIRECTORY_LOGIN_MODULE_POSITION'); ?></h2>
		<p><?php echo Text::sprintf('COM_SERVICEDIRECTORY_PLEASE_PUBLISH_A_LOGIN_MODULE_TO_THIS_CODESLOGINCODE_POSITION_AND_INSURE_THAT_YOU_TARGET_THESE_PAGES_THIS_IS_POSSIBLE_IF_YOU_ADD_THE_MODULE_TO_ALL_PAGES_SINCE_THIS_MODULE_POSITION_SHOULD_ONLY_BE_AVAILABLE_IN_THIS_COMPONENT', 'servicedirectory'); ?></p>
	</div>
<?php endif; ?>
