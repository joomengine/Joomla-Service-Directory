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


/***[JCBGUI.layout.php_view.147.$$$$]***/
$items = $displayData ?? [];
$count = is_array($items) ? count($items) : 0;/***[/JCBGUI$$$$]***/


?>

<!--[JCBGUI.layout.layout.147.$$$$]-->
<?php if (!empty($items)) : ?>
    <div class="container px-0">
        <!-- Horizontal scrollable ribbon -->
        <div class="d-flex flex-row flex-nowrap overflow-auto py-2 text-nowrap">
			<?php
			$lastIndex = $count - 1;
			foreach ($items as $i => $item) :
				echo LayoutHelper::render('socialhandle', $item);
				// add a dot separator except for the last item
				if ($i < $lastIndex) :
					echo '<span class="px-2 text-muted">·</span>';
				endif;
			endforeach;
			?>
        </div>
    </div>
<?php endif; ?><!--[/JCBGUI$$$$]-->

