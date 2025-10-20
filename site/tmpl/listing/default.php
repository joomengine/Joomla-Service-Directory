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

// No direct access to this file
defined('_JEXEC') or die;

?>
<?php echo $this->toolbar->render(); ?>

<!--[JCBGUI.site_view.default.83.$$$$]-->
<?php echo $this->item->event->onContentBeforeDisplay ?? ''; ?>

<?php echo $this->loadTemplate('company'); ?>

<?php echo $this->loadTemplate('companydetails'); ?>

<?php echo $this->loadTemplate('companyextras'); ?>

<?php echo LayoutHelper::render('companyrelationships', ($this->item ?? (object) [])); ?>

<?php echo $this->item->event->onContentAfterDisplay ?? ''; ?>

<?php echo $this->loadTemplate('companydebug'); ?><!--[/JCBGUI$$$$]-->

