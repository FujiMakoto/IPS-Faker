<?php

namespace IPS\faker\Faker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Content item abstract class
 */
abstract class _Comment implements Extensible
{
	/**
	 * @brief   Comment extension container
	 */
	protected static $_commentExtension;

	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller = 'comments';

	/**
	 * @brief   Application name
	 */
	public static $app;

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction;

	/**
	 * @brief   Comment generator extension
	 */
	public static $commentExtension;

	/**
	 * @brief	Node Class
	 */
	public static $containerNodeClass;

	/**
	 * @brief	Item Class
	 */
	public static $itemClass;

	/**
	 * @brief   Generator form title language string
	 */
	public static $title;

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message;

	/**
	 * Load the Comments extension for this Item
	 *
	 * @return  mixed   The extension if it exists, otherwise NULL
	 */
	protected function commentExt()
	{
		/* Return the extension if it has already been loaded */
		if ( static::$_commentExtension ) {
			return static::$_commentExtension;
		}

		$extensions = \IPS\faker\Faker::allExtensions( \IPS\faker\Faker::COMMENTS );

		/* Do we have an explicitly defined app for the Comment extension? */
		$app = static::$app;
		$commentExtension = static::$commentExtension;
		if ( is_array( $commentExtension ) )
		{
			$app = $commentExtension[0];
			$commentExtension = $commentExtension[1];
		}

		/* Return the extension if it exists */
		if ( in_array( $app . '_' . $commentExtension, $extensions ) ) {
			return $extensions[ $app . '_' . $commentExtension ];
		}

		return NULL;
	}

	/**
	 * Generate a fake content comment
	 *
	 * @param	\IPS\Content\Item   $item   The content item
	 * @param   array               $values Generator form values
	 *
	 * @return  \IPS\Content\Comment
	 */
	abstract public function generateSingle( \IPS\Content\Item $item, array $values );

	/**
	 * Bulk process generations
	 *
	 * @param   array       $extData            Extension data ( $ext, $extApp, $extension, $controller )
	 * @param   array|null  $values             Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( array $extData, $values=NULL )
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

			/* Load our content item container and set our dynamic message here */
			$itemClass = $self::$itemClass;
			$_item = $itemClass::loadFromUrl( $values['item_url'] );
			$message = \IPS\Member::loggedIn()->language()->addToStack( $self::$message, true, array(
				'sprintf' => $_item->mapped('title')
			) );

			$count = 0;
			$itemsGenerated = array();
			while ( ($count < $values['total']) and (count($itemsGenerated) < $perGo) )
			{
				++$count;
				$itemsGenerated[] = $self->generateSingle( $_item, $values );
			}
			$doneSoFar += $perGo;

			/* Update our session cookies and proceed to the next chunk */
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
			return array( $doneSoFar, $message, ( 100 * $doneSoFar ) / $values['total'] );

		}, function() use( $self, $values, $extension )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal(
				"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}"
			), 'completed' );
		} );
	}
}