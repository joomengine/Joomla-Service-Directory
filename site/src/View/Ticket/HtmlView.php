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
namespace JoomService\Component\Servicedirectory\Site\View\Ticket;

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
use JoomService\Joomla\Servicedirectory\Utilities\Permitted\Actions;
use JoomService\Joomla\Utilities\StringHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\CMS\Layout\LayoutHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Ticket Html View class
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
	 * @var    string|null
	 * @since  3.10.11
	 */
	public ?string $ref;

	/**
	 * The origin referral view item id
	 *
	 * @var    int|null
	 * @since  3.10.11
	 */
	public ?int $refid;

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
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   6.0.0
	 */
	public function __construct(array $config)
	{
		if (empty($config['option']))
		{
			$config['option'] = 'com_servicedirectory';
		}

		parent::__construct($config);

		// get the application
		$this->app ??= Factory::getApplication();
		// get input
		$this->input ??= method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		// get component params
		$this->params ??= method_exists($this->app, 'getParams')
			? $this->app->getParams()
			: ComponentHelper::getParams('com_servicedirectory');

		$this->useCoreUI = true;
		$this->isModal = false; // no modal support yet
	}

	/**
	 * Ticket view display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		// Load module values
		$model = $this->getModel();
		$this->form ??= $model->getForm();
		$this->item = $model->getItem();
		$this->state = $model->getState();
		$this->styles = $model->getStyles() ?? [];
		$this->scripts = $model->getScripts() ?? [];

		// get the permitted actions the current user can do.
		$this->canDo = Actions::get('ticket', $this->item);

		// Set the return
		$this->setReturn();

		// Set the toolbar
		$this->addToolBar();

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
	 * Set the redirection details.
	 *
	 * @return  void
	 * @since   5.1.4
	 */
	protected function setReturn(): void
	{
		// This [ref,refid] will be removed in JCB.v7, use only [return]
		$this->ref = $this->input->getWord('ref', null);
		$this->refid = $this->input->getInt('refid', null);
		$this->referral = '';
		if (!empty($this->refid) && !empty($this->ref))
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string) $this->ref . '&refid=' . (int) $this->refid;
		}
		elseif (!empty($this->ref))
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string) $this->ref;
		}

		$return = $this->input->getBase64('return', null);
		if (!empty($return))
		{
			$this->referral .= '&return=' . (string) $return;
		}
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
		// Initialize the toolbar only if it hasn't been initialized yet.
		$this->toolbar ??= $this->getDocument()->getToolbar();

		$this->input->set('hidemainmenu', true);
		$user = $this->getCurrentUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;

		ToolbarHelper::title( Text::_($isNew ? 'COM_SERVICEDIRECTORY_TICKET_NEW' : 'COM_SERVICEDIRECTORY_TICKET_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (StringHelper::check($this->referral))
		{
			if ($this->canDo->get('ticket.create') && $isNew)
			{
				// We can create the record.
				ToolbarHelper::save('ticket.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('ticket.edit'))
			{
				// We can save the record.
				ToolbarHelper::save('ticket.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not create but cancel.
				ToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				ToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('ticket.create'))
				{
					ToolbarHelper::apply('ticket.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('ticket.save', 'JTOOLBAR_SAVE');
					ToolbarHelper::custom('ticket.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				ToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('ticket.edit'))
				{
					// We can save the new record
					ToolbarHelper::apply('ticket.apply', 'JTOOLBAR_APPLY');
					ToolbarHelper::save('ticket.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('ticket.create'))
					{
						ToolbarHelper::custom('ticket.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('ticket.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('ticket.edit') && $canVersion)
				{
					ToolbarHelper::versions('com_servicedirectory.ticket', $this->item->id);
				}
				if ($this->canDo->get('ticket.create'))
				{
					ToolbarHelper::custom('ticket.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				ToolbarHelper::cancel('ticket.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		// set help url for this view if found
		$this->help_url = ServicedirectoryHelper::getHelpUrl('ticket');
		if (StringHelper::check($this->help_url))
		{
			ToolbarHelper::help('COM_SERVICEDIRECTORY_HELP_MANAGER', false, $this->help_url);
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
		$this->setDocumentTitle(Text::_($isNew ? 'COM_SERVICEDIRECTORY_TICKET_NEW' : 'COM_SERVICEDIRECTORY_TICKET_EDIT'));
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
		$isSite = $this->app->isClient('site');

		if ($isSite)
		{
			// reset toolbar
			$this->toolbar->setItems([]);

			// create a save and back (apply) button
			$this->toolbar->apply('ticket.apply', 'COM_SERVICEDIRECTORY_SAVE_COMMENT_RETURN');
			$this->toolbar->customHtml('&nbsp;&nbsp;');
			// create a return (cancel) button
			$this->toolbar->cancel('ticket.cancel', 'COM_SERVICEDIRECTORY_RETURN');
			// add inline help back
			$this->toolbar->inlinehelp("hide-aware-inline-help");
		}

		// load the previous comments if there is any
		if (!empty($this->item->comments))
		{
			$this->form->setFieldAttribute('noteticketconversation', 'description',
				LayoutHelper::render('noteticketconversation',
					[
						'comments' => $this->item->comments,
						'user' => $this->getCurrentUser()
					]
				)
			);
		}
		else
		{
			$this->form->removeField('noteticketconversation');
		}

		// the ticket status must always be the same as published (little trick)
		$published = (int) (((int) ($this->item->published ?? -2) === -2) ? 2 : $this->item->published);
		$this->form->setValue('ticket_status', $published);

		if ($isSite)
		{
			// Make company hidden on the site area
 			// Since ModelSelect does not work in the site area!
			$this->form->setFieldAttribute('company', 'type', 'hidden');

			// ticket_status can not be changed on the site area
			$this->form->setFieldAttribute('ticket_status', 'disabled', 'true');
			$this->form->setFieldAttribute('ticket_status', 'readonly', 'true');
			$this->form->setFieldAttribute('ticket_status', 'required', 'false');
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
		$target ??= base64_encode('ticket');

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
			$wa = $document -> getWebAssetManager();
			$wa->addInlineScript($script);
		}
		else
		{
			$document -> addScriptDeclaration($script);
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
