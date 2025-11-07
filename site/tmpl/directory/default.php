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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use Joomla\CMS\Uri\Uri;
use JoomService\Component\Servicedirectory\Site\Helper\RouteHelper;

// No direct access to this file
defined('_JEXEC') or die;

$max_listing = $this->params->get('max_listings', 1);
$number_of_listings = count( (array) ($this->mine ?? []));
$allow_more_listings = ($max_listing > $number_of_listings);

$access_listing = ($this->user->authorise('company.access', 'com_servicedirectory') && $this->user->authorise('company.dashboard_list', 'com_servicedirectory'));
$create_listing = ($allow_more_listings && $access_listing && $this->user->authorise('core.create', 'com_servicedirectory'));
$return_here = urlencode(base64_encode((string) Uri::getInstance()));
$create_listing_url = Route::_("/index.php?option=com_servicedirectory&view=company&layout=edit&return={$return_here}");

$items = $this->items ?? [];
if (!empty($items))
{
	// Sort alphabetically by name (case-insensitive, natural order)
	usort($items, function($a, $b) {
		return strnatcasecmp($a->name ?? '', $b->name ?? '');
	});

	// Optional: keep at most 3×5 = 15 items. Remove this line to show all.
	$items = array_slice($items, 0, 15);
}


$id = $this->input->getInt('id', 0);
$search_link = Route::_(RouteHelper::getCompaniesRoute($id)) . '/';
$search_value = $this->input->get('search', null, 'STRING');

?>
<?php echo $this->toolbar->render(); ?>
<?php if (!empty($items)): ?>
<?php echo LayoutHelper::render('searchbox', ['url' => $search_link, 'value' => $search_value]); ?>
<div class="container-xxl my-4">
	<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
		<?php foreach ($items as $item): ?>
		<?php
			$url = $item->link ?? null;
			$name = $this->escape($item->name ?? 'error');
			$description = $this->escape($item->description ?? '');
			$image = $this->images[$item->guid] ?? null;
		?>
		<div class="col">
			<div class="card h-100 border-0 shadow-sm transition"
				 role="button"<?php if (!empty($url)): ?>
				 onclick="window.location.href='<?php echo $url; ?>'"
				 onmouseover="this.classList.add('shadow-lg')"
				 onmouseout="this.classList.remove('shadow-lg')"<?php endif; ?>>

				<!-- Image -->
				<?php if (!empty($image)) : ?>
					<img src="<?php echo $image; ?>" class="card-img-top" alt="<?php echo $name; ?>" width="400" height="200" loading="lazy">
				<?php else : ?>
					<div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
						<span class="text-muted small">400 × 200</span>
					</div>
				<?php endif; ?>

				<!-- Body -->
				<div class="card-body text-center">
					<h5 class="card-title mb-0 d-inline-flex align-items-center justify-content-center"><?php if (!empty($url)): ?>
						<a href="<?php echo $url; ?>" class="stretched-link text-decoration-none text-dark"
							<?php if (!empty($description)): ?>data-bs-toggle="tooltip" data-bs-html="true" title="<?php echo $description; ?>"<?php endif; ?>>
							<?php echo $name; ?>
						</a><?php else: ?>
							<?php echo $name; ?><?php endif; ?>
						<?php if (!empty($description)): ?>
							<i class="fas fa-circle-info ms-2 text-secondary"
								data-bs-toggle="tooltip"
								data-bs-html="true"
								title="<?php echo $description; ?>">
							</i>
						<?php endif; ?>
					</h5>
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
</script>
<?php else: ?>
	<div class="alert alert-warning mb-0" role="alert"><?php echo Text::_('COM_SERVICEDIRECTORY_NO_CATEGORIES'); ?></div>
<?php endif; ?>

<?php if ($access_listing): ?>
	<?php echo $this->loadTemplate('companylistings'); ?>
	<?php if ($create_listing): ?>
		<div class="container text-center">
			<a href="<?php echo $create_listing_url; ?>" class="btn btn-success btn-lg w-100 py-4 fs-3 fw-bold shadow-lg">
				<i class="bi bi-plus-circle me-2"></i> <?php echo Text::_('COM_SERVICEDIRECTORY_CREATE_LISTING'); ?>
			</a>
		</div>
	<?php endif; ?>
<?php elseif (!empty($this->user->name)): ?>
	<div class="alert alert-secondary mb-4" role="alert">
		<b><?php echo Text::sprintf('COM_SERVICEDIRECTORY_WELCOME_BACK_S', $this->escape($this->user->name)); ?></b><br>
		<?php echo Text::_('COM_SERVICEDIRECTORY_YOUR_ACCOUNT_IS_NOT_PERMITTED_TO_ADD_A_LISTING'); ?>
	</div>
<?php elseif ((bool) $this->params->get('show_login', 0)): ?>
	<?php echo Text::_('COM_SERVICEDIRECTORY_YOU_MUST_BE_SIGNED_IN_TO_VIEW_OR_MANAGE_YOUR_COMPANY_LISTINGS'); ?>
	<?php echo $this->loadTemplate('loginmodule'); ?>
<?php endif; ?>
<br>
