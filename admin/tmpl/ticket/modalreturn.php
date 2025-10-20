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

use Joomla\CMS\Router\Route;

// No direct access to this file
defined('_JEXEC') or die;

/** @var \JoomService\Component\Servicedirectory\Administrator\View\Ticket\HtmlView $this */

$icon = 'icon-check';
$title_key = $this->item->guid ?? '';
$title_column = $this->item->subject ?? '';
$data = [
	'contentType' => 'com_servicedirectory.ticket',
	'id' => $title_key,
	'title' => $title_column,
	'uri' => Route::_('index.php?option=com_servicedirectory&layout=modal&tmpl=component&id='. (int) ($this->item->id ?? 0))
];

// Add Content select script
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('modal-content-select');

// The data for Content select script
$this->getDocument()->addScriptOptions('content-select-on-load', $data, false);

?>

<div class="px-4 py-5 my-5 text-center">
    <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo $title_column; ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            <?php echo $title_column; ?>
        </p>
    </div>
</div>