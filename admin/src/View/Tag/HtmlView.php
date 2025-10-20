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
namespace JoomService\Component\Servicedirectory\Administrator\View\Tag;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Document\Document;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;
use JoomService\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Tag Html View class
 *
 * @since  1.6
 */
#[\AllowDynamicProperties]
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
	 * The item from the model
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $item;

	/**
	 * The state object
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $state;

	/**
	 * The form from the model
	 *
	 * @var    mixed
	 * @since  3.10.11
	 */
	public mixed $form;

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
	 * @since  5.0.0
	 */
	protected array $styles;

	/**
	 * The scripts url array
	 *
	 * @var    array
	 * @since  5.0.0
	 */
	protected array $scripts;

	/**
	 * The actions object
	 *
	 * @var    object
	 * @since  3.10.11
	 */
	public object $canDo;

	/**
	 * The origin referral view name
	 *
	 * @var    string
	 * @since  3.10.11
	 */
	public string $ref;

	/**
	 * The origin referral item id
	 *
	 * @var    int
	 * @since  3.10.11
	 */
	public int $refid;

	/**
	 * The referral url suffix values
	 *
	 * @var    string
	 * @since  3.10.11
	 */
	public string $referral;

	/**
	 * The modal state
	 *
	 * @var    bool
	 * @since  5.2.1
	 */
	public bool $isModal;

	/**
	 * Tag view display method
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
		$this->useCoreUI = true;
		// Load module values
		$model = $this->getModel();
		$this->form ??= $model->getForm();
		$this->item = $model->getItem();
		$this->styles = $model->getStyles();
		$this->scripts = $model->getScripts();
		$this->state = $model->getState();
		// get action permissions
		$this->canDo = ServicedirectoryHelper::getActions('tag', $this->item);
		// get return referral details
		$this->ref = $this->input->get('ref', 0, 'word');
		$this->refid = $this->input->get('refid', 0, 'int');
		$return = $this->input->get('return', null, 'base64');
		// set the referral string
		$this->referral = '';
		if ($this->refid && $this->ref)
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
		}
		elseif($this->ref)
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string) $this->ref;
		}
		// check return value
		if (!is_null($return))
		{
			// add the return value
			$this->referral .= '&return=' . (string) $return;
		}

		// Set the toolbar
		if ($this->getLayout() !== 'modal')
		{
			$this->isModal = false;
			$this->addToolbar();
		}
		else
		{
			$this->isModal = true;
			$this->addModalToolbar();
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		// Set the html view document stuff
		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		$this->input->set('hidemainmenu', true);
		$user = $this->getCurrentUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_SERVICEDIRECTORY_TAG_NEW' : 'COM_SERVICEDIRECTORY_TAG_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('tag.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('tag.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('tag.create'))
				{
					ToolbarHelper::apply('tag.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('tag.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('tag.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('tag.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('tag.create'))
					{
						ToolbarHelper::custom('tag.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('tag.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('tag.edit') && $canVersion)
				{
					ToolbarHelper::versions('com_servicedirectory.tag', $this->item->id);
				}
				if ($this->canDo->get('tag.create'))
				{
					ToolbarHelper::custom('tag.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		// set help url for this view if found
		$this->help_url = ServicedirectoryHelper::getHelpUrl('tag');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_SERVICEDIRECTORY_HELP_MANAGER', false, $this->help_url);
		}
	}

	/**
	 * Add the modal toolbar.
	 *
	 * @return  void
	 * @throws  \Exception
	 * @since   5.0.0
	 */
	protected function addModalToolbar()
	{
		$this->input->set('hidemainmenu', true);
		$user = $this->getCurrentUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_SERVICEDIRECTORY_TAG_NEW' : 'COM_SERVICEDIRECTORY_TAG_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('tag.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('tag.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('tag.create'))
				{
					ToolbarHelper::apply('tag.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('tag.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('tag.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('tag.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('tag.save', 'JTOOLBAR_SAVE');
				}
				ToolbarHelper::cancel('tag.cancel', 'JTOOLBAR_CLOSE');
			}
		}
	}

	/**
	 * Prepare some document related stuff.
	 *
	 * @return  void
	 * @since   1.6
	 */
	protected function _prepareDocument(): void
	{
		// Load jQuery
		Html::_('jquery.framework');
		$isNew = ($this->item->id < 1);
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

		$app = $this->app ?? Factory::getApplication();

		// set the url as needed
		$url = '';
		if (method_exists($app, 'isClient') && $app->isClient('site'))
		{
			$url = Uri::root();
		}

		// get the form token
		$token = Session::getFormToken();
		$entity ??= $this->item->guid ?? 0;
		$target ??= base64_encode('tag');

		// Define the configuration for the uploader
		$uploaderConfig = [
			"endpoint_type" => "{$url}index.php?option=com_servicedirectory&task=ajax.getFileTypeDetails&format=json&raw=true&{$token}=1&target={$target}",
			"target_class" => "vdm-uikit-uploader",
			"file_vdm_uploader" => [
				"endpoint_upload" => "{$url}index.php?option=com_servicedirectory&task=ajax.uploadFile&format=json&raw=true&{$token}=1&entity={$entity}&target={$target}",
				"endpoint_display" => "{$url}index.php?option=com_servicedirectory&task=ajax.displayFiles&format=json&raw=true&{$token}=1&entity={$entity}&target={$target}",
				"endpoint_delete" => "{$url}index.php?option=com_servicedirectory&task=ajax.deleteFile&format=json&raw=true&{$token}=1",
			],
		];

		// Convert the PHP array to a JavaScript object
		$uploaderConfigJson = json_encode($uploaderConfig);
		$script = "(window.VDM ??= {}).uikit ??= {}; window.VDM.uikit.config = {$uploaderConfigJson};";

		/** @var \Joomla\CMS\Document\Document $document */
		$document ??= ($this->getDocument() ?? $app->getDocument());

		// Use WebAssetManager if available (Joomla 4+), otherwise fallback
		if (method_exists($document, 'getWebAssetManager'))
		{
			/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = $this->getDocument()->getWebAssetManager();
			$wa->addInlineScript($script);
		}
		else
		{
			$this->getDocument()->addScriptDeclaration($script);
		}

		Html::_('script', 'media/com_servicedirectory/uikit-v3/js/uikit.min.js', ['version' => 'auto']);
		Html::_('script', 'media/com_servicedirectory/uikit-v3/js/uikit-icons.min.js', ['version' => 'auto']);
		Html::_('script', 'https://cdn.jsdelivr.net/gh/vdm-io/uikit@3.0.2/dist/js/vdm.min.js', ['version' => 'auto']);
		Html::_('stylesheet', 'media/com_servicedirectory/uikit-v3/css/uikit.min.css', ['version' => 'auto']);
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
	public function escape($var, bool $shorten = true, int $length = 30)
	{
		if (!is_string($var))
		{
			return $var;
		}

		return StringHelper::html($var, $this->_charset ?? 'UTF-8', $shorten, $length);
	}
}
