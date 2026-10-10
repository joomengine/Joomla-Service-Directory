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
namespace JoomService\Component\Servicedirectory\Api\Serializer;

use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Tag\TagApiSerializerTrait;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;
use JoomService\Component\Servicedirectory\Administrator\Helper\ServicedirectoryHelper;

// No direct access to this file
\defined('_JEXEC') or die;

/**
 * Review_company_update Api Serializer
 *
 * Builds the relationships of the review_company_update resource, for the item and
 * the list representation alike.
 *
 * @since  4.0.0
 */
class Review_company_updateSerializer extends JoomlaSerializer
{
	use TagApiSerializerTrait;

	/**
	 * Build the category relationship.
	 *
	 * @param   \stdClass  $item  The item.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	public function category($item)
	{
		// Relate the category to the categories resource.
		return $this->related($item->category ?? null, 'categories');
	}

	/**
	 * Build the created_by relationship.
	 *
	 * @param   \stdClass  $item  The item.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	public function createdBy($item)
	{
		// Relate the created_by to the users resource.
		return $this->related($item->created_by ?? null, 'users');
	}

	/**
	 * Build the languages relationship.
	 *
	 * @param   \stdClass  $item  The item.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	public function languages($item)
	{
		// Relate the languages to the languages resource.
		return $this->related($item->languages ?? null, 'languages');
	}

	/**
	 * Build the areas_of_expertise relationship.
	 *
	 * @param   \stdClass  $item  The item.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	public function areasOfExpertise($item)
	{
		// Relate the areas_of_expertise to the areas_of_expertise resource.
		return $this->related($item->areas_of_expertise ?? null, 'areas_of_expertise');
	}

	/**
	 * Build the modified_by relationship.
	 *
	 * @param   \stdClass  $item  The item.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	public function modifiedBy($item)
	{
		// Relate the modified_by to the users resource.
		return $this->related($item->modified_by ?? null, 'users');
	}

	/**
	 * Build the relationship to one related resource, or to many when the value holds several ids.
	 *
	 * @param   mixed   $value  The id of the related resource, or the ids of the related resources.
	 * @param   string  $type   The type of the related resource.
	 *
	 * @return  Relationship
	 *
	 * @since   4.0.0
	 */
	protected function related($value, string $type): Relationship
	{
		$serializer = new JoomlaSerializer($type);

		if (is_array($value))
		{
			$resources = [];

			foreach ($value as $id)
			{
				if ($id !== null && $id !== '' && !is_array($id) && !is_object($id))
				{
					$resources[] = new Resource($id, $serializer);
				}
			}

			return new Relationship(new Collection($resources, $serializer));
		}

		return new Relationship(new Resource($value, $serializer));
	}
}
