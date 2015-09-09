<?php

namespace IPS\faker\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Content item abstract class
 */
abstract class _Comment extends \IPS\faker\Content
{
	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller = 'comments';

	/**
	 * @brief	[Content\Comment]	Item Class
	 */
	public static $itemClass;

	/**
	 * @brief	[Content\Item]	Comment Class
	 */
	public static $commentClass;

	/**
	 * Generate a fake content comment
	 *
	 * @param	\IPS\Content\Item   $item   The content item
	 * @param   array               $values Generator form values
	 * @param   bool                $first  Indicates this is the first comment for an item
	 *
	 * @return  \IPS\Content\Comment
	 */
	abstract public function generateSingle( \IPS\Content\Item $item, array $values, $first=FALSE );

	/**
	 * Bulk process generations
	 *
	 * @param   array|null  $values Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( $values=NULL )
	{
		$self = $this;
		$vCookie = static::$app . '_faker_' . static::$_controller . '_generator_values';

		/* If this is a form submission, store our values now */
		if ( $values )
		{
			/* If we have a custom author defined, we need to save the ID manually for json encoding */
			if ( !empty($values['author']) and ($values['author'] instanceof \IPS\Member) ) {
				$values['author'] = $values['author']->member_id;
			}

			unset( \IPS\Request::i()->cookie[ $vCookie ] );
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
		}
		$values = $values ?: json_decode(\IPS\Request::i()->cookie[ $vCookie ], true);

		/* <sarcasm>Serialization is fun</sarcasm> @TODO: Clean this up */
		if ( !empty($values['author']) and is_int($values['author']) ) {
			$values['author'] = \IPS\Member::load( $values['author'] );
		}
		if ( !empty($values['item_url']) and is_array($values['item_url']) ) {
			$values['item_url'] = \IPS\Http\Url::createFromArray( $values['item_url']['data'] );
		}

		$perGo = isset( $values['per_go'] ) ? (int) $values['per_go'] : 25;
		$values['total'] = mt_rand( $values['comment_range']['start'], $values['comment_range']['end'] );

		/* Generate the MultipleRedirect page */
		$reflect = new \ReflectionClass( $this );
		$extension = $reflect->getShortName();
		$processUrl = \IPS\Http\Url::internal(
			"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}&do=process"
		);
		return new \IPS\Helpers\MultipleRedirect( $processUrl, function( $doneSoFar ) use( $self, $perGo, $values, $vCookie )
		{
			/* Have we processed everything? */
			if ( $doneSoFar >= $values['total'] ) {
				return NULL;
			}

			/* Load our content item container */
			$itemClass = $self::$itemClass;
			$_item = $itemClass::loadFromUrl( $values['item_url'] );

			$count = 0;
			$generated = array();
			while ( ($count < $values['total']) and (count($generated) < $perGo) )
			{
				++$count;
				$generated[] = $self->generateSingle( $_item, $values );
			}
			$doneSoFar += $perGo;

			/* Update our session cookies and proceed to the next chunk */
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
			return array( $doneSoFar, end($generated), ( 100 * $doneSoFar ) / $values['total'] );

		}, function() use( $self, $values, $extension )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal(
				"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}"
			), 'completed' );
		} );
	}
}