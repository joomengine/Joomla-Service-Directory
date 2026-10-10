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

// No direct access to this file
defined('_JEXEC') or die;

?>
<div id="j-main-container">
	<div class="main-card p-3 p-lg-4">
		<div class="row g-4">
			<div class="col-12 col-xl-9">
				<?php echo $this->loadTemplate('main');?>
			</div>
			<div class="col-12 col-xl-3">
				<?php echo $this->loadTemplate('vdm');?>
			</div>
		</div>
	</div>
</div>