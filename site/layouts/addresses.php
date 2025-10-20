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


/***[JCBGUI.layout.php_view.145.$$$$]***/
$items = $displayData ?? [];
$count = is_array($items) ? count($items) : 0;

$uid = 'bs-carousel-' . substr(md5(uniqid('', true)), 0, 8);

// How many cards you want per *slide* on large screens
$perSlide = 4;

// Chunk items into slides (only used if we actually show the carousel)
$slides = $count > 3 ? array_chunk($items, $perSlide) : [];/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.layout.layout.145.$$$$]-->
<?php if ($count === 0) : ?>
	<!-- nothing to render -->
<?php elseif ($count <= $perSlide) : ?>
	<div class="container px-0">
		<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
			<?php foreach ($items as $item): ?>
				<div class="col">
					<div class="card h-100">
						<div class="card-body">
							<?php echo LayoutHelper::render('address', $item); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php else : ?>
	<div class="container px-0">
		<div class="d-flex flex-row flex-nowrap overflow-auto gap-3 p-2">
			<?php foreach ($items as $item): ?>
				<div class="card flex-shrink-0" style="width: 18rem;">
					<div class="card-body">
						<?php echo LayoutHelper::render('address', $item); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?><!--[/JCBGUI$$$$]-->

