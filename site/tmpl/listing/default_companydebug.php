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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;

// No direct access to this file
defined('_JEXEC') or die;


/***[JCBGUI.template.php_view.170.$$$$]***/
Html::_('bootstrap.collapse');/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.template.template.170.$$$$]-->
<?php if ($this->params->get('show_listing_object', 0) === 1): ?>
<div class="container my-5">
	<div class="accordion accordion-flush" id="Service-Directory-Listing-Debug">
		<div class="accordion-item">
			<h2 class="accordion-header">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#Listing-Object" aria-expanded="false" aria-controls="Listing-Object">
					<?php echo Text::_('COM_SERVICEDIRECTORY_LISTING_OBJECT'); ?>
				</button>
			</h2>
			<div id="Listing-Object" class="accordion-collapse collapse" data-bs-parent="#Service-Directory-Listing-Debug">
				<div class="accordion-body">
					<pre><?php var_dump($this->item); ?></pre>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?><!--[/JCBGUI$$$$]-->

