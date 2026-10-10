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

// No direct access to this file
defined('_JEXEC') or die;

?>
<div id="j-main-container" class="container-fluid">
	<div class="main-card jcb-dashboard p-3 p-lg-4">
		<div class="jcb-dashboard__content">
			<div class="row g-4 align-items-start">
			<div class="col-12 col-xxl-9">
				<div class="jcb-dashboard__main d-flex flex-column gap-4">
					<?php echo $this->loadTemplate('main');?>
				</div>
			</div>
			<div class="col-12 col-xxl-3">
				<div class="jcb-dashboard__sidebar d-flex flex-column gap-4">
					<?php echo $this->loadTemplate('vdm');?>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>