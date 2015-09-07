<?php


namespace IPS\faker\modules\admin\generator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Item generator
 */
class _items extends \IPS\faker\Faker\Controller
{
	/**
	 * @brief   Controller name
	 */
	public static $controller = 'items';

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}
}