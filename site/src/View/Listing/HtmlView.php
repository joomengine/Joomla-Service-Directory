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
namespace JoomService\Component\Servicedirectory\Site\View\Listing;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use JoomService\Component\Servicedirectory\Site\Helper\HeaderCheck;
use JoomService\Component\Servicedirectory\Site\Helper\ServicedirectoryHelper;
use JoomService\Component\Servicedirectory\Site\Helper\RouteHelper;
use JoomService\Joomla\Utilities\ObjectHelper;
use JoomService\Joomla\Utilities\JsonHelper;
use JoomService\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\User\User;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Layout\LayoutHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Html View class for the Listing
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The app class
	 *
	 * @var    CMSApplicationInterface
	 * @since  5.2.1
	 */
	public CMSApplicationInterface $app;

	/**
	 * The input class
	 *
	 * @var    Input
	 * @since  5.2.1
	 */
	public Input $input;

	/**
	 * The params registry
	 *
	 * @var    Registry
	 * @since  5.2.1
	 */
	public Registry $params;

	/**
	 * The user object.
	 *
	 * @var    User
	 * @since  3.10.11
	 */
	public User $user;

	/**
	 * The toolbar object
	 *
	 * @var    Toolbar
	 * @since  3.10.11
	 */
	public Toolbar $toolbar;

	/**
	 * The styles url array
	 *
	 * @var    array
	 * @since  3.10.11
	 */
	protected array $styles;

	/**
	 * The scripts url array
	 *
	 * @var    array
	 * @since  3.10.11
	 */
	protected array $scripts;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 * @throws \Exception
	 * @since  1.6
	 */
	public function display($tpl = null): void
	{
		// get application
		$this->app ??= Factory::getApplication();
		// get input
		$this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		// set params
		$this->params ??= method_exists($this->app, 'getParams')
			? $this->app->getParams()
			: ComponentHelper::getParams('com_servicedirectory');
		$this->menu = $this->app->getMenu()->getActive();
		// get the user object
		$this->user ??= $this->getCurrentUser();
		// Load module values
		$model = $this->getModel();
		$this->styles = $model->getStyles() ?? [];
		$this->scripts = $model->getScripts() ?? [];
		// Initialise variables.
		$this->item = $model->getItem();

		// Set the toolbar
		$this->addToolBar();

		// Set the html view document stuff
		$this->_prepareDocument();

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			throw new \Exception(implode(PHP_EOL, $errors), 500);
		}
		// Process the content plugins.
		if (ObjectHelper::check($this->item))
		{
			PluginHelper::importPlugin('content');
			// Setup Event Object.
			$this->item->event = new \stdClass;
			// Check if item has params, or pass global params
			$params = (isset($this->item->params) && JsonHelper::check($this->item->params)) ? json_decode($this->item->params) : $this->params;
			// onContentAfterTitle Event Trigger
			$results = $this->getDispatcher()->dispatch('onContentAfterTitle',
				new AfterTitleEvent(
					'onContentAfterTitle',
					[
						'context' => 'servicedirectory.listing',
						'subject' => $this->item,
						'params' => $params,
						'page' => 0
					]
				)
			)->getArgument('result', []);
			$this->item->event->onContentAfterTitle = trim(implode("\n", $results));
			// onContentBeforeDisplay Event Trigger
			$results = $this->getDispatcher()->dispatch('onContentBeforeDisplay',
				new BeforeDisplayEvent(
					'onContentBeforeDisplay',
					[
						'context' => 'servicedirectory.listing',
						'subject' => $this->item,
						'params' => $params,
						'page' => 0
					]
				)
			)->getArgument('result', []);
			$this->item->event->onContentBeforeDisplay = trim(implode("\n", $results));
			// onContentAfterDisplay Event Trigger
			$results = $this->getDispatcher()->dispatch('onContentAfterDisplay',
				new AfterDisplayEvent(
					'onContentAfterDisplay',
					[
						'context' => 'servicedirectory.listing',
						'subject' => $this->item,
						'params' => $params,
						'page' => 0
					]
				)
			)->getArgument('result', []);
			$this->item->event->onContentAfterDisplay = trim(implode("\n", $results));
		}

		parent::display($tpl);
	}

// LayoutHelper::render('filedisplay', $displayData);

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{

		// set help url for this view if found
		$this->help_url = ServicedirectoryHelper::getHelpUrl('listing');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_SERVICEDIRECTORY_HELP_MANAGER', false, $this->help_url);
		}

		// add the toolbar if it's not already loaded
		$this->toolbar ??= $this->getDocument()->getToolbar();
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{

		// Only load jQuery if needed. (default is true)
		if ($this->params->get('add_jquery_framework', 1) == 1)
		{
			Html::_('jquery.framework');
		}
		// Load the header checker class.
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck();

		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		// Set script size.
		$size = $this->params->get('uikit_min');
		// The uikit css.
		if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			Html::_('stylesheet', 'media/com_servicedirectory/uikit-v3/css/uikit'.$size.'.css', ['version' => 'auto']);
		}
		// The uikit js.
		if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			Html::_('script', 'media/com_servicedirectory/uikit-v3/js/uikit'.$size.'.js', ['version' => 'auto']);
			Html::_('script', 'media/com_servicedirectory/uikit-v3/js/uikit-icons'.$size.'.js', ['version' => 'auto']);
		}
		// load the meta description
		if (isset($this->item->metadesc) && $this->item->metadesc)
		{
			$this->setDocumentTitle($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->setDocumentTitle($this->params->get('menu-meta_description'));
		}
		// load the key words if set
		if (isset($this->item->metakey) && $this->item->metakey)
		{
			$this->getDocument()->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->getDocument()->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		// check the robot params
		if (isset($this->item->robots) && $this->item->robots)
		{
			$this->getDocument()->setMetadata('robots', $this->item->robots);
		}
		elseif ($this->params->get('robots'))
		{
			$this->getDocument()->setMetadata('robots', $this->params->get('robots'));
		}
		// check if autor is to be set
		if (isset($this->item->created_by) && $this->params->get('MetaAuthor') == '1')
		{
			$this->getDocument()->setMetaData('author', $this->item->created_by);
		}
		// check if metadata is available
		if (isset($this->item->metadata) && $this->item->metadata)
		{
			$mdata = json_decode($this->item->metadata,true);
			foreach ($mdata as $k => $v)
			{
				if ($v)
				{
					$this->getDocument()->setMetadata($k, $v);
				}
			}
		}
		// add styles
		foreach ($this->styles as $style)
		{
			Html::_('stylesheet', $style, ['version' => 'auto']);
		}
		// add scripts
		foreach ($this->scripts as $script)
		{
			Html::_('script', $script, ['version' => 'auto']);
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var     The output to escape.
	 * @param   bool   $shorten The switch to shorten.
	 * @param   int    $length  The shorting length.
	 *
	 * @return  mixed  The escaped value.
	 * @since   1.6
	 */
	public function escape($var, bool $shorten = false, int $length = 40)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
