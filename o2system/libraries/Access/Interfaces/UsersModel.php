<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 5/14/2016
 * Time: 12:42 AM
 */

namespace O2System\Libraries\Access\Interfaces;


interface UsersModel
{
	/**
	 * Get User Account
	 *
	 * @param int $id_account User Account ID
	 *
	 * @return mixed
	 */
	public function getAccount( $username );

	public function insertAccount( array $account );

	public function updateAccount( array $account );

	public function getRegistrationBuffer( $token );

	public function insertRegistrationBuffer( array $registration_buffer, $type = 'EMAIL' );

	public function updateRegistrationBuffer( array $registration_buffer );

	public function deleteRegistrationBuffer( array $registration_buffer );

	public function getTrail( $id_user_trail );

	public function insertTrail( array $trail );

	public function updateTrail( array $trail );

	public function getTrails( $id_account );

	public function isUsernameExists( $username );

	public function isEmailExists( $email );

	public function isMsisdnExists( $msisdn );
}