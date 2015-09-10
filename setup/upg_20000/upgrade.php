<?php


namespace IPS\faker\setup\upg_20000;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 0.2.0 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Purge old fake content
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		try
		{
			$topics = \IPS\Db::i()->select( '*', 'forums_topics', 'faker_fake=1' );
			foreach ( $topics as $topic )
			{
				$topic = \IPS\forums\Topic::constructFromData( $topic );
				$topic->delete();
			}
		} catch ( \Exception $e ) {}

		try
		{
			$members = \IPS\Db::i()->select( '*', 'core_members', 'faker_fake=1' );
			foreach ( $members as $member )
			{
				$member = \IPS\Member::constructFromData( $member );
				$member->delete();
			}
		} catch ( \Exception $e ) {}

		return TRUE;
	}
}