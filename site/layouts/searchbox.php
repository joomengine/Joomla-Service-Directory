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
use JoomService\Joomla\Utilities\StringHelper;

// No direct access to this file
defined('JPATH_BASE') or die;

$baseUrl = $displayData['url'] ?? null;
$value   = $displayData['value'] ?? null;
$random  = StringHelper::random(5);

?>
<?php if (!empty($baseUrl)): ?>
<div class="container-fluid">
	<div class="d-flex justify-content-end">
		<div class="input-group w-30">
			<input
				type="text"
				class="form-control"
				id="searchInput-<?php echo $random; ?>"
				placeholder="<?php echo Text::_('COM_SERVICEDIRECTORY_SEARCH'); ?>"
				aria-label="<?php echo Text::_('COM_SERVICEDIRECTORY_SEARCH'); ?>"
				<?php if (!empty($value)): ?>value="<?php echo $value; ?>"<?php endif; ?>
			>
			<button class="btn btn-outline-secondary" type="button" id="searchButton-<?php echo $random; ?>">
				<i class="icon-search"></i>
			</button>

			<?php if (!empty($value)): ?>
			<button class="btn btn-outline-secondary" type="button" id="clearButton-<?php echo $random; ?>" title="<?php echo Text::_('COM_SERVICEDIRECTORY_CLEAR_SEARCH'); ?>">
				<i class="icon-cancel"></i>
			</button>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const input<?php echo $random; ?>  = document.getElementById('searchInput-<?php echo $random; ?>');
	const button<?php echo $random; ?> = document.getElementById('searchButton-<?php echo $random; ?>');
	const baseUrl<?php echo $random; ?> = '<?php echo $baseUrl; ?>';
	<?php if (!empty($value)): ?>
	const clearButton<?php echo $random; ?> = document.getElementById('clearButton-<?php echo $random; ?>');
	<?php endif; ?>

	function sanitizeInput<?php echo $random; ?>(text) {
		// Keep only letters, numbers, basic punctuation, and spaces
		return text.replace(/[^a-zA-Z0-9\s\-_.]/g, '').trim();
	}

	function triggerSearch<?php echo $random; ?>() {
		const term = sanitizeInput<?php echo $random; ?>(input<?php echo $random; ?>.value);
		if (term.length === 0) {
			return; // do nothing on empty input
		}
		window.location.href = baseUrl<?php echo $random; ?> + encodeURIComponent(term);
	}

	// Trigger search on Enter key
	input<?php echo $random; ?>.addEventListener('keypress', function(event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			triggerSearch<?php echo $random; ?>();
		}
	});

	// Trigger search on icon click
	button<?php echo $random; ?>.addEventListener('click', function() {
		triggerSearch<?php echo $random; ?>();
	});

	<?php if (!empty($value)): ?>
	// Clear search and reload to base URL
	clearButton<?php echo $random; ?>.addEventListener('click', function() {
		window.location.href = baseUrl<?php echo $random; ?>;
	});
	<?php endif; ?>
});
</script>
<?php endif; ?>
