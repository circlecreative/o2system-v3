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
	public function getChildren( $id_parent, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		$result = $this->db->getWhere( $table, [ $this->parent_key => $id_parent ] );

		if ( $result->numRows() > 0 )
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
	public function hasChildren( $id_parent, $table = NULL )
	{
		$table = isset( $table ) ? $table : $this->table;

		$result = $this->db->select( 'id' )->getWhere( $table, [ $this->parent_key => $id_parent ] );

		if ( $result->numRows() > 0 )
		{
			return TRUE;
		}

		return FALSE;
	}
}