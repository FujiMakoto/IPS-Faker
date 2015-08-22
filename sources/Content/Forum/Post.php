<?php

namespace IPS\faker\Content\Forum;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Class _Post
 * @package IPS\faker\Content\Forum
 */
class _Post extends \IPS\forums\Topic\Post
{
	/**
	 * @param	\IPS\forums\Topic	$topic	The Topic we are posting in
	 * @param	array				$values	Generator form values
	 *
	 * @return	\IPS\faker\Content\Forum\Post
	 */
	public static function create( \IPS\forums\Topic $topic, array $values )
	{
		$generator = new \IPS\faker\Content\Generator();

		/* Generate the author */
		if ( $values['author'] )
		{
			$member = $values['member'];
		}
		elseif ( $values['author_type'] == 'random_fake' )
		{
			$member = $generator->fakeMember();
		}
		else
		{
			$member = $generator->guest();
		}

		$comment = parent::create( $topic, $generator->comment(), TRUE, ( !$member->name ) ? NULL : $member->name, $topic->hidden() ? FALSE : NULL, $member );
		$comment->ip_address = $generator->ipAddress();
		$comment->save();

		return $comment;
	}
}