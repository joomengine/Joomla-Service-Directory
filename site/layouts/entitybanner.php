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
use Joomla\CMS\Layout\LayoutHelper;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;

// No direct access to this file
defined('JPATH_BASE') or die;

$data = $displayData ?? null;

?>
<?php if ($data && !empty($data->name)) : ?>
<div class="container my-4">
	<div class="card border-0 shadow-sm overflow-hidden">

		<?php if (!empty($data->src)) : ?>
			<!-- Banner Section with background image -->
			<div
				class="position-relative text-center text-white"
				style="
					background-image: url('<?php echo $data->src; ?>');
					background-size: cover;
					background-position: center;
					height: 300px;">

				<!-- Overlay for contrast -->
				<div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50"></div>

				<!-- Centered heading -->
				<div class="position-absolute top-50 start-50 translate-middle w-100 px-3">
					<h1 class="display-4 fw-bold text-uppercase m-0 text-white"
						style="text-shadow: 2px 2px 6px rgba(0,0,0,0.8);">
						<?php echo $data->name; ?>
					</h1>
				</div>
			</div>
		<?php else : ?>
		<!-- Heading without banner -->
		<div
			class="d-flex align-items-center justify-content-center text-center bg-dark"
			style="height: 100px;">

			<h1 class="display-4 fw-bold text-uppercase m-0 text-white"
				style="text-shadow: 2px 2px 6px rgba(0,0,0,0.8);">
				<?php echo $data->name; ?>
			</h1>
		</div>
		<?php endif; ?>

		<!-- Description -->
		<?php if (!empty($data->description)) : ?>
			<div class="card-body">
				<div class="card-text text-center">
					<?php echo $data->description; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>
