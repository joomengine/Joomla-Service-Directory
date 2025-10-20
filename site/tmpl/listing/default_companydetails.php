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

// Single entity view
$item = $this->item ?? (object) [];

?>
<div class="container my-5">
	<div class="row g-2">
		<div class="col-12 col-md-6">
			<?php echo LayoutHelper::render('companydetails', $item); ?>
		</div>
		<div class="col-12 col-md-6">
			<?php echo LayoutHelper::render('companycontacts', $item); ?>
		</div>
	</div>
</div>
