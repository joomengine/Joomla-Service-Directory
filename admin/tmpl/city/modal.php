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

// No direct access to this file
defined('_JEXEC') or die;

/** @var \JoomService\Component\Servicedirectory\Administrator\View\City\HtmlView $this */
?>
<div class="subhead noshadow mb-3">
    <?php echo $this->getDocument()->getToolbar('toolbar')->render(); ?>
</div>
<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
