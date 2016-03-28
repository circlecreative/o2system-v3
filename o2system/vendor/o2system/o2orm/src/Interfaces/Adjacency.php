<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 12/3/2015
 * Time: 9:58 AM
 */

namespace O2System\ORM\Interfaces;


trait Adjacency
{
	/**
	 * Adjacency Enabled Flag
	 *
	 * @access  protected
	 * @type    bool
	 */
	protected $_adjacency_enabled = TRUE;

	/**
	 * Parent Key Field
	 *
	 * @access  public
	 * @type    string
	 */
	public $parent_key = 'id_parent';

	/**
	 * Get Children
	 *
	 * @param int         $id_parent
	 * @param string|null $table
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function get_children( $id_parent, $table = NULL)
	{
		$table = isset( $table ) ? $table : $this->table;

		$result = $this->db->get_where( $table, [ $this->parent_key => $id_parent ] );

		if ( $result->num_rows() > 0 )
		{
			return $result;
		}

		return FALSE;
	}

	/**
	 * Has Children
	 *
	 * @param int         $id_parent
	 * @param string|null $table
	 *
	 * @access  public
	 * @return  bool
	 */
	public function has_children( $id_parent, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		$result = $this->db->select( 'id' )->get_where( $table, [ $this->parent_key => $id_parent ] );

		if ( $result->num_rows() > 0 )
		{
			return TRUE;
		}

		return FALSE;
	}
}