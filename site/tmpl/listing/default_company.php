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

// No direct access to this file
defined('_JEXEC') or die;

// Single entity view
$item = $this->item ?? (object) [];

$name = $item->name ?? '';
$description = $item->description ?? null;
$logo = $item->logo ?? '';
$banner = $item->banner ?? '';

?>
<div class="container my-5">
	<?php if (!empty($banner)): ?>
		<div class="mb-4">
			<img
				src="<?php echo $banner; ?>"
				alt=""
				class="img-fluid w-100 rounded border shadow-sm"
				width="1080"
				height="200"
				onerror="this.remove()"
			>
		</div>
	<?php endif; ?>

	<div class="row g-4 align-items-start">
		<div class="col-12 col-md-8">
			<h1 class="fw-bold mb-3">
				<?php if (!empty($this->item->edit_link)): ?>
					<span class="me-3">
						<a href="<?php echo $this->item->edit_link; ?>" title="<?php echo Text::_('COM_SERVICEDIRECTORY_EDIT_LISTING'); ?>"><span class="icon-pencil-2 article-edit"></span></a>
					</span>
				<?php endif; ?>
				<?php echo $name; ?>
			</h1>

			<?php echo $this->item->event->onContentAfterTitle ?? ''; ?>

			<?php if (!empty($description)): ?>
				<div class="fs-5 text-muted">
					<?php echo $description; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="col-12 col-md-4">
			<div class="d-flex justify-content-md-end">
				<?php if (!empty($logo)): ?>
					<img
						src="<?php echo $logo; ?>"
						alt="<?php echo $name; ?>"
						class="rounded border shadow-sm"
						width="150"
						height="150"
						onerror="this.remove()"
					>
				<?php else: ?>
					<div class="bg-light d-flex align-items-center justify-content-center rounded border" style="width:150px; height:150px;">
						<span class="text-muted small">150x150</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
