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
namespace JoomService\Component\Servicedirectory\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Component\ComponentHelper;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Form Rule (Maxlength) class for the Joomla Platform.
 *
 * @since  3.5
 */
class MaxlengthRule extends FormRule
{
	/**
	 * Validates a string value against the field's required state and maximum length.
	 *
	 * This method determines whether a field value is valid by checking:
	 * - Whether the field is marked as required.
	 * - Whether an empty value is allowed.
	 * - Whether the normalized length (whitespace removed) exceeds the configured maximum.
	 *
	 * The maximum length is read from the field's `maxlength` attribute and defaults to 2000
	 * characters when not specified.
	 *
	 * All whitespace is removed before length validation using UTF-8 safe processing.
	 *
	 * @param  \SimpleXMLElement  $element  The `<field>` definition containing attributes such as `required` and `maxlength`.
	 * @param  mixed              $value    The value being validated; expected to be castable to string.
	 * @param  string|null        $group    The name group (fieldset or repeatable context), if any.
	 * @param  Registry|null      $input    Optional registry containing the full form data set.
	 * @param  Form|null          $form     The form object owning this field.
	 *
	 * @return bool  True if the value passes validation, false otherwise.
	 *
	 * @since  5.1.4
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

		if (!$required && empty($value)) {
			return true;
		}

		$length = $element['maxlength'] ?? 2000;

		// Normalize whitespace and trim
		$string = trim(preg_replace('/\s+/u', '', $value));

		if (mb_strlen($string) > $length)
		{
			return false;
		}

		return true;
	}
}
