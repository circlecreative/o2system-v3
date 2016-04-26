<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 12:43 PM
 */

namespace O2System\ORM\Interfaces;


trait Mapper
{
	/**
	 * Belongs To
	 *
	 * Define an inverse one-to-one or many relationship.
	 *
	 * @access  public
	 * @final   this method can't be overwrite
	 *
	 * @uses    O2System\ORM\Relations\Belongs_to()
	 *
	 * @param string $relation      table name, model name or instance of ORM model
	 * @param null   $reference_key working table foreign key
	 *
	 * @return mixed
	 */
	final protected function belongs_to( $relation, $reference_key = NULL )
	{
		$belongs_to = new Relations\Belongs_To( $this );

		$belongs_to->set_relation( $relation );
		$belongs_to->set_reference_field( $reference_key );

		return $belongs_to->result();
	}
	// ------------------------------------------------------------------------

	/**
	 * Define a many-to-many relationship.
	 *
	 * @param  string $related
	 * @param  string $table
	 * @param  string $foreign_key
	 * @param  string $other_key
	 * @param  string $relation
	 *
	 * @return array
	 */
	final protected function belongs_to_many( $relation, $reference_key = NULL )
	{
		$belongs_to_many = new Relations\Belongs_To_Many( $this );

		$belongs_to_many->set_relation( $relation );
		$belongs_to_many->set_reference_field( $reference_key );

		return $belongs_to_many->result();
	}
	// ------------------------------------------------------------------------

	/**
	 * Has One
	 *
	 * Define a one-to-one relationship.
	 *
	 * @access  public
	 * @final   this method can't be overwrite
	 *
	 * @uses    O2System\ORM\Relations\Has_one()
	 *
	 * @param string $reference   table name, model name or instance of ORM model
	 * @param null   $foreign_key working table foreign key
	 *
	 * @return mixed
	 */
	final protected function has_one( $relation, $reference_key = NULL )
	{
		$has_one = new Relations\Has_One( $this );

		$has_one->set_relation( $relation );
		$has_one->set_reference_field( $reference_key );

		return $has_one->result();
	}
	// ------------------------------------------------------------------------

	/**
	 * Has Many
	 *
	 * Define a one-to-many relationship.
	 *
	 * @access  public
	 * @final   this method can't be overwrite
	 *
	 * @uses    O2System\ORM\Relations\Has_one()
	 *
	 * @param string $reference   table name, model name or instance of ORM model
	 * @param null   $foreign_key working table foreign key
	 *
	 * @return mixed
	 */
	final protected function has_many( $relation, $reference_key = NULL )
	{
		$has_many = new Relations\Has_Many( $this );

		$has_many->set_relation( $relation );
		$has_many->set_reference_field( $reference_key );

		return $has_many->result();
	}
	// ------------------------------------------------------------------------

	/**
	 * Set the relationships that should be eager loaded.
	 *
	 * @access  public
	 *
	 * @uses    O2System\ORM\Relations\With()
	 *
	 * @return $this
	 */
	final protected function has()
	{
		$has = new Relations\Has( $this );
		$has->set_relationships( func_get_args() );
		$has->result();

		return $this;
	}

	// ------------------------------------------------------------------------

	/**
	 * Has
	 *
	 * Set relationship based on table name, model name, or instance of ORM model
	 *
	 * @access  public
	 *
	 * @uses    O2System\ORM\Relations\With()
	 *
	 * @return $this
	 */
	final protected function with()
	{
		$with = new Relations\With( $this );
		$with->set_relationships( func_get_args() );

		return $this;
	}
}