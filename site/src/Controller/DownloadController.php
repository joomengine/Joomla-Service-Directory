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

use Joomla\CMS\MVC\Controller\BaseController;
use JoomService\Joomla\Utilities\MimeHelper;
use JoomService\Joomla\Servicedirectory\File\Factory;
use Joomla\CMS\Factory as CMSFactory;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Servicedirectory Site Download Controller
 *
 * @since 5.0.2
 */
class DownloadController extends BaseController
{
	/**
	 * The application instance.
	 *
	 * @var \Joomla\CMS\Application\CMSApplication
	 * @since  5.0.2
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 * @since  5.0.2
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);

		// Initialize the application
		$this->app = CMSFactory::getApplication();

		// Register tasks
		$this->registerTask('file', 'downloadFile');
		$this->registerTask('image', 'downloadImage');
		$this->registerTask('media', 'downloadMedia');
	}

	/**
	 * Handles file downloads.
	 *
	 * @return void
	 * @since  5.0.2
	 */
	public function downloadFile()
	{
		$this->processDownload(true);
	}

	/**
	 * Handles image downloads.
	 *
	 * @return void
	 * @since  5.0.2
	 */
	public function downloadImage()
	{
		$this->processDownload(false);
	}

	/**
	 * Handles media downloads.
	 *
	 * @return void
	 * @since  5.0.2
	 */
	public function downloadMedia()
	{
		$this->processDownload(false);
	}

	/**
	 * Processes the download request.
	 *
	 * @param bool $download Whether to force download (true) or display inline (false).
	 *
	 * @return void
	 * @since  5.0.2
	 */
	protected function processDownload(bool $download)
	{
		$input    = method_exists($this->app, 'getInput') ? $this->app->getInput() : $this->app->input;
		$fileGuid = $input->getString('file');

		if ($fileGuid)
		{
			$fileDetails = $this->getFileDetails($fileGuid);

			if ($fileDetails)
			{
				$this->sendFile($fileDetails, $download);
			}
			else
			{
				$this->app->enqueueMessage('Download failed, you do not have access to this file!', 'error');
				$this->app->redirect('index.php');
			}
		}
		else
		{
			$this->app->enqueueMessage('No file specified.', 'error');
			$this->app->redirect('index.php');
		}
	}

	/**
	 * Sends the file to the client.
	 *
	 * @param array $fileDetails The details of the file to send.
	 * @param bool  $download    Whether to force download (true) or display inline (false).
	 *
	 * @return void
	 * @since  5.0.2
	 */
	protected function sendFile(array $fileDetails, bool $download)
	{
		$filePath = $fileDetails['file_path'] ?? null;
		$fileName = $fileDetails['name'] ?? null;

		if ($filePath !== null && $fileName !== null && is_file($filePath) && is_readable($filePath))
		{
			// Clean the output buffer
			if (ob_get_level())
			{
				ob_end_clean();
			}

			// Get and validate the file size in bytes
			$fileSize = isset($fileDetails['size']) && is_numeric($fileDetails['size']) && $fileDetails['size'] != 0
				? (int) $fileDetails['size'] : filesize($filePath);

			// Get and validate the MIME type
			$mimeType = !empty($fileDetails['mime']) && is_string($fileDetails['mime']) && trim($fileDetails['mime']) !== ''
				? trim($fileDetails['mime']) : MimeHelper::mimeType($filePath);

			$disposition = $download ? 'attachment' : 'inline';

			// Set headers
			$this->app->setHeader('Content-Description', 'File Transfer', true);
			$this->app->setHeader('Content-Type', $mimeType, true);
			$this->app->setHeader('Content-Length', (string) $fileSize, true);
			$this->app->setHeader('Content-Disposition', $disposition . '; filename="' . basename($fileName) . '"', true);
			$this->app->setHeader('Pragma', 'public', true);
			$this->app->setHeader('Expires', '0', true);
			$this->app->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

			// Send headers
			$this->app->sendHeaders();

			// Output the file
			readfile($filePath);

			// Close the application
			$this->app->close();
		} else {
			$this->app->enqueueMessage('File not found.', 'error');
			$this->app->redirect('index.php');
		}
	}

	/**
	 * Retrieves file details based on GUID.
	 *
	 * @param string $guid The GUID of the file.
	 *
	 * @return array|null The file details or null if not found or inaccessible.
	 * @since  5.0.2
	 */
	protected function getFileDetails(string $guid): ?array
	{
		try {
			return Factory::_('File.Manager')->download($guid);
		} catch (\Exception $e) {
			// Log exception (assumed that a logger is available)
			// $this->logger->error('Error retrieving file details: ' . $e->getMessage());

			// Display a generic error message
			$this->app->enqueueMessage('An error occurred while retrieving the file.', 'error');
		}

		return null;
	}
}
