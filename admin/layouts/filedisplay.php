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
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

// No direct access to this file
defined('JPATH_BASE') or die;


/***[JCBGUI.layout.php_view.132.$$$$]***/
// Initialize
$data   = $displayData['data'] ?? [];
$removeDelete = $displayData['remove_delete'] ?? false;

// Group images by base key (icon__, thumb__, main__)
$bucket = [];
$files =  [];
foreach ($data as $file)
{
	$key  = str_replace(['icon__', 'thumb__', 'main__'], '', $file->name);
	$type = str_starts_with($file->name, 'icon__')
		? 'icon'
		: (str_starts_with($file->name, 'thumb__')
			? 'thumb'
			: (str_starts_with($file->name, 'main__') ? 'main' : null));

	if ($type !== null) { $bucket[$key][$type] = $file; } else { $files[] = $file; }
}

// Extract grouped images for display
$images = !empty($bucket) ? array_values($bucket) : [];/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.layout.layout.132.$$$$]-->
<?php if (!empty($data)) : ?>
<?php if (!empty($files)) : ?>
<ul class="uk-list uk-list-divider">
<?php foreach ($files as $file) : ?>
	<li>
	<?php if ($removeDelete) : ?>
	<a class="uk-button uk-button-default uk-width-1-1" href="<?php echo $file->link; ?>" download>
		(<?php echo $file->type_name; ?>)
		<?php echo $file->name; ?>
	</a>
	<?php else : ?>
	<div id="<?php echo $file->guid; ?>"
		 class="uk-button-group uk-width-1-1 uk-margin-small-bottom">
		<a class="uk-button uk-button-default uk-width-3-4"
		   href="<?php echo $file->link; ?>" download>
			(<?php echo $file->type_name; ?>)
			<?php echo $file->name; ?>
		</a>
		<button type="button"
				class="uk-button uk-button-danger uk-width-1-4"
				uk-icon="trash"
				onclick="VDMDeleteFile('file_vdm_uploader', '<?php echo $file->guid; ?>');">
		</button>
	</div>
	<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php if (!empty($images)) : ?>
<div class="uk-margin">
	<div class="uk-slider-container-offset" uk-slider>
		<div class="uk-position-relative uk-visible-toggle">
			<div class="uk-slider-items uk-child-width-1-4@s" uk-grid uk-lightbox="animation: slide">
			<?php foreach ($images as $fileSet) : ?>
			<?php
				$name       = $fileSet['main']->name ?? 'unknown';
				$mainGuid   = $fileSet['main']->guid ?? null;
				$thumbGuid  = $fileSet['thumb']->guid ?? null;
				$iconGuid   = $fileSet['icon']->guid ?? null;
				$mainLink   = $fileSet['main']->link ?? null;
				$thumbLink  = $fileSet['thumb']->link ?? null;
				$iconLink   = $fileSet['icon']->link ?? null;
				$mediaLink  = $mainLink ?? $thumbLink ?? $iconLink;
				$deleteList = array_filter([$mainGuid, $thumbGuid, $iconGuid]);
				$buttonSize = count($deleteList);
			?>
			<div id="<?php echo $mainGuid ?? uniqid('img_'); ?>">
				<div class="uk-card uk-card-body uk-box-shadow-small uk-box-shadow-hover-large">
					<div class="uk-card-media-top">
						<a class="uk-inline"
						   href="<?php echo $mainLink ?? $mediaLink; ?>"
						   data-caption="<?php echo $name; ?>">
							<img src="<?php echo $mediaLink; ?>"
								 width="600"
								 height="600"
								 alt="<?php echo $name; ?>">
						</a>
					</div>

					<div class="uk-card-body uk-padding-remove">
						<div class="uk-margin">
							<div class="uk-button-group uk-width-1-1">
							<?php if ($mainLink) : ?>
								<a class="uk-button uk-button-primary uk-button-small uk-width-1-<?php echo $buttonSize; ?>"
								   href="<?php echo $mainLink; ?>" download>
									<?php echo Text::_('COM_SERVICEDIRECTORY_LARGE'); ?>
								</a>
							<?php endif; ?>

							<?php if ($thumbLink) : ?>
								<a class="uk-button uk-button-primary uk-button-small uk-width-1-<?php echo $buttonSize; ?>"
								   href="<?php echo $thumbLink; ?>" download>
									<?php echo Text::_('COM_SERVICEDIRECTORY_THUMB'); ?>
								</a>
							<?php endif; ?>

							<?php if ($iconLink) : ?>
								<a class="uk-button uk-button-primary uk-button-small uk-width-1-<?php echo $buttonSize; ?>"
								   href="<?php echo $iconLink; ?>" download>
									<?php echo Text::_('COM_SERVICEDIRECTORY_ICON'); ?>
								</a>
							<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="uk-card-footer">
						<button class="uk-button uk-button-danger uk-width-1-1"
							type="button" uk-icon="trash"
							onclick="VDMDeleteFiles('file_vdm_uploader', <?php echo json_encode($deleteList); ?>);">
						</button>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
			</div>
		</div>
		<ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
	</div>
</div>
<?php endif; ?>
<?php endif; ?><!--[/JCBGUI$$$$]-->

