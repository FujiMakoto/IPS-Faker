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
abstract class _Item implements Extensible
{
	/**
	 * @brief   Comment extension container
	 */
	protected static $_commentExtension;

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
	public static $contentItemClass;

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
}