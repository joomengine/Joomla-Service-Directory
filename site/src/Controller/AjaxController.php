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
namespace JoomService\Component\Servicedirectory\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Ajax Base Controller
 *
 * @since  1.6
 */
class AjaxController extends BaseController
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// make sure all json stuff are set
		$this->app->getDocument()->setMimeEncoding( 'application/json' );
		$this->app->setHeader('Content-Disposition','attachment;filename="getajax.json"');
		$this->app->setHeader('Access-Control-Allow-Origin', '*');
		// load the tasks 
		$this->registerTask('getCountryStates', 'ajax');
		$this->registerTask('getStateCities', 'ajax');
		$this->registerTask('getFileTypeDetails', 'ajax');
		$this->registerTask('uploadFile', 'ajax');
		$this->registerTask('deleteFile', 'ajax');
		$this->registerTask('displayFiles', 'ajax');
	}

	public function ajax()
	{
		// get the user for later use
		$user         = $this->app->getIdentity();
		// get the input values
		$jinput       = $this->input ?? (method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input);
		// check if we should return raw (DEFAULT TRUE SINCE J4)
		$returnRaw    = $jinput->get('raw', true, 'BOOLEAN');
		// return to a callback function
		$callback     = $jinput->get('callback', null, 'CMD');
		// Check Token!
		$token        = Session::getFormToken();
		$call_token   = $jinput->get('token', 0, 'ALNUM');
		if($jinput->get($token, 0, 'ALNUM') || $token === $call_token)
		{
			// get the task
			$task = $this->getTask();
			switch($task)
			{
				case 'getCountryStates':
					try
					{
						$countryValue = $jinput->get('country', NULL, 'STRING');
						if($countryValue && $user->id != 0)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->getCountryStates($countryValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getStateCities':
					try
					{
						$stateValue = $jinput->get('state', NULL, 'STRING');
						if($stateValue && $user->id != 0)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->getStateCities($stateValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'getFileTypeDetails':
					try
					{
						$guidValue = $jinput->get('guid', NULL, 'STRING');
						$targetValue = $jinput->get('target', NULL, 'BASE64');
						if($guidValue && $user->id != 0 && $targetValue)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->getFileTypeDetails($guidValue, $targetValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'uploadFile':
					try
					{
						$guidValue = $jinput->get('guid', NULL, 'STRING');
						$entityValue = $jinput->get('entity', NULL, 'STRING');
						$targetValue = $jinput->get('target', NULL, 'BASE64');
						if($guidValue && $user->id != 0 && $entityValue && $targetValue)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->uploadFile($guidValue, $entityValue, $targetValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'deleteFile':
					try
					{
						$guidValue = $jinput->get('guid', NULL, 'STRING');
						if($guidValue && $user->id != 0)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->deleteFile($guidValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
				case 'displayFiles':
					try
					{
						$entityValue = $jinput->get('entity', NULL, 'STRING');
						$targetValue = $jinput->get('target', NULL, 'BASE64');
						if($entityValue && $user->id != 0 && $targetValue)
						{
							$ajaxModule = $this->getModel('ajax', 'Site');
							if ($ajaxModule)
							{
								$result = $ajaxModule->displayFiles($entityValue, $targetValue);
							}
							else
							{
								$result = ['error' => 'There was an error! [149]'];
							}
						}
						else
						{
							$result = ['error' => 'There was an error! [149]'];
						}
						if($callback)
						{
							echo $callback . "(".json_encode($result).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($result);
						}
						else
						{
							echo "(".json_encode($result).");";
						}
					}
					catch(\Exception $e)
					{
						if($callback)
						{
							echo $callback."(".json_encode($e).");";
						}
						elseif($returnRaw)
						{
							echo json_encode($e);
						}
						else
						{
							echo "(".json_encode($e).");";
						}
					}
				break;
			}
		}
		else
		{
			// return to a callback function
			if($callback)
			{
				echo $callback."(".json_encode(['error' => 'There was an error! [139]']).");";
			}
			elseif($returnRaw)
			{
				echo json_encode(['error' => 'There was an error! [139]']);
			}
			else
			{
				echo "(".json_encode(['error' => 'There was an error! [139]']).");";
			}
		}
	}
}
