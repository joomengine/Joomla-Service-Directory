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
	 * Method to test the url for a valid parts.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   ?Form              $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   1.7.0
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
