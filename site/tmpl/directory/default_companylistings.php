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

<!--[JCBGUI.template.template.171.$$$$]-->
<div class="container my-4">
	<div class="alert alert-secondary mb-4" role="alert">
		<?php echo Text::sprintf('COM_SERVICEDIRECTORY_WELCOME_BACK_S', $this->escape($this->user->name, false)); ?>
	</div>
	<?php if (!empty($this->mine)) : ?>
		<h4 class="mb-3"><?php echo Text::_('COM_SERVICEDIRECTORY_YOUR_COMPANY_LISTINGS'); ?></h4>
		<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
			<?php foreach ($this->mine as $item) : ?>
				<div class="col">
					<div class="card border-0 shadow-sm h-100">
						<div class="card-body d-flex flex-column justify-content-between">
							<div>
								<h5 class="card-title mb-1">
									<?php if (!empty($item->edit_link)): ?>
										<span class="me-2">
											<a href="<?php echo $item->edit_link; ?>" title="<?php echo Text::_('COM_SERVICEDIRECTORY_EDIT_LISTING'); ?>"><span class="icon-pencil-2 article-edit"></span></a>
										</span>
									<?php endif; ?>
									<?php if (!empty($item->link)): ?>
										<a href="<?php echo $item->link; ?>" class="text-decoration-none">
											<?php echo $this->escape($item->name); ?>
										</a>
									<?php else: ?>
										<?php echo $this->escape($item->name); ?>
									<?php endif; ?>
								</h5>
								<p class="card-text small text-muted mb-2">
									<?php echo $this->escape($item->description, true, 200); ?>
								</p>
								<ul class="list-unstyled small mb-0">
									<?php if (!empty($item->contactname)) : ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_CONTACT_NAME'); ?></strong> <?php echo $this->escape($item->contactname); ?></li>
									<?php endif; ?>
									<?php if (!empty($item->email)) : ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_EMAIL'); ?></strong>
											<a href="mailto:<?php echo $this->escape($item->email, false); ?>">
												<?php echo $this->escape($item->email, true, 30); ?>
											</a>
										</li>
									<?php endif; ?>
									<?php if (!empty($item->phone)) : ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_PHONE'); ?></strong>
											<?php echo $this->escape($item->phone, false); ?>
										</li>
									<?php endif; ?>
									<?php if (!empty($item->website)) : ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_WEBSITE'); ?></strong>
											<a href="<?php echo $this->escape($item->website, false); ?>" target="_blank" rel="noopener">
												<?php echo $this->escape($item->website, true, 30); ?>
											</a>
										</li>
									<?php endif; ?>
									<?php if (!empty($item->created)): ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_STATUS'); ?></strong>
											<?php echo $item->published ? Text::_('COM_SERVICEDIRECTORY_PUBLISHED') : Text::_('COM_SERVICEDIRECTORY_UNPUBLISHED'); ?>
										</li>
									<?php endif; ?>
									<?php if (!empty($item->created)): ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_CREATED'); ?></strong>
											<?php echo Html::_('date', $item->created, Text::_('DATE_FORMAT_LC3')); ?>
										</li>
									<?php endif; ?>
									<?php if (!empty($item->modified)): ?>
										<li><strong><?php echo Text::_('COM_SERVICEDIRECTORY_LAST_MODIFIED'); ?></strong>
											<?php echo Html::_('date', $item->modified, Text::_('DATE_FORMAT_LC3')); ?>
										</li>
									<?php endif; ?>
								</ul>
							</div>

							<?php if (!empty($item->edit_link)): ?>
								<div class="mt-3 text-end">
									<a href="<?php echo $item->edit_link; ?>" class="btn btn-sm btn-outline-primary">
										<span class="icon-pencil-2 article-edit"></span> <?php echo Text::_('COM_SERVICEDIRECTORY_EDIT_LISTING'); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="alert alert-info mt-4" role="alert">
			<?php echo Text::_('COM_SERVICEDIRECTORY_YOU_CURRENTLY_HAVE_NO_COMPANY_LISTINGS_ADD_YOUR_FIRST_COMPANY_TODAY'); ?>
		</div>
	<?php endif; ?>
</div><!--[/JCBGUI$$$$]-->

