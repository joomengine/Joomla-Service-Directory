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
 * Region Api Serializer
 *
 * Builds the relationships of the region resource, for the item and
 * the list representation alike.
 *
 * @since  4.0.0
 */
class RegionSerializer extends JoomlaSerializer
{

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
