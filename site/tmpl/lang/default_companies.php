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

?>

<!--[JCBGUI.template.template.166.$$$$]-->
<div class="container-xxl my-4">
	<div class="row row-cols-1 g-4">
		<?php foreach ($this->items as $item): ?>
		<?php
			$url = $item->link ?? '#';
			$name = $this->escape($item->name ?? '');
			$description = $this->escape($item->description ?? '', true, 100);
			$logo = is_array($this->logos) ? ($this->logos[$item->guid] ?? null) : null;
		?>
		<div class="col">
			<div class="card h-100 border-0 shadow-sm transition p-3"
				 role="button"
				 onclick="window.location.href='<?php echo $url; ?>'"
				 onmouseover="this.classList.add('shadow-lg')"
				 onmouseout="this.classList.remove('shadow-lg')">

				<div class="row g-3 align-items-center">
					<div class="col-md-9">
						<div class="card-body">
							<h5 class="card-title fs-4 fw-bold mb-2">
								<?php if (!empty($item->edit_link)): ?>
									<span class="position-relative z-2"><span class="me-3">
										<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('COM_SERVICEDIRECTORY_EDIT_LISTING'); ?>"><span class="icon-pencil-2 article-edit"></span></a>
									</span></span>
								<?php endif; ?>
								<a href="<?php echo $url; ?>" 
								   class="stretched-link text-decoration-none text-dark">
									<?php echo $name; ?>
								</a>
							</h5>

							<?php if (!empty($description)): ?>
							<p class="card-text text-muted mb-3">
								<?php echo $description; ?>
							</p>
							<?php endif; ?>

							<?php echo LayoutHelper::render('companyrelationships', $item); ?>
						</div>
					</div>

					<div class="col-md-3 text-center">
						<?php if (!empty($logo)): ?>
							<img src="<?php echo $this->escape($logo); ?>"
								 alt="<?php echo $name; ?>"
								 class="img-fluid rounded border"
								 style="width:150px; height:150px; object-fit:contain;">
						<?php else: ?>
							<div class="bg-light d-flex align-items-center justify-content-center rounded border"
								 style="width:150px; height:150px;">
								<span class="text-muted small">150 × 150</span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
	const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
	tooltipTriggerList.map(function (el) {
		return new bootstrap.Tooltip(el);
	});
});
</script><!--[/JCBGUI$$$$]-->

