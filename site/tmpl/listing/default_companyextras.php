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

$addresses = $item->addresses ?? [];
$social_handles = $item->social_handles ?? [];
$portfolios = $item->portfolios ?? [];

?>
<div class="container my-5">
	<?php echo LayoutHelper::render('addresses', $addresses); ?>
	<?php echo LayoutHelper::render('socialhandles', $social_handles); ?>
</div>

<?php if (!empty($portfolios)): ?>
	<?php echo LayoutHelper::render('companyportfolios', $portfolios); ?>
<?php endif; ?>
