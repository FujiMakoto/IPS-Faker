<?php

namespace IPS\faker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker utilities
 * @package IPS\faker
 */
class _Faker
{
	/**
	 * Generators
	 */
	const NODES     = 'NodeGenerator';
	const ITEMS     = 'ItemGenerator';
	const COMMENTS  = 'CommentGenerator';

	/**
	 * Retrieve generator extensions
	 *
	 * @param   string|null $generator The generator to retrieve extensions for, or NULL to retrieve all generators
	 * @return  array
	 */
	public static function allExtensions( $generator=NULL )
	{
		/* Fetching extensions for a specific generator or all generators? */
		if ( $generator === NULL )
		{
			$rawExtensions = array_merge(
				\IPS\Application::allExtensions( 'faker', static::NODES ),
				\IPS\Application::allExtensions( 'faker', static::ITEMS ),
				\IPS\Application::allExtensions( 'faker', static::COMMENTS )
			);
		}
		else
		{
			$rawExtensions = \IPS\Application::allExtensions( 'faker', $generator );
		}

		/* Update keys for extensions with explicitly defined apps */
		$extensions = array();
		foreach ( $rawExtensions as $key => $extension )
		{
			if ( property_exists($extension, 'app') )
			{
				$splitKey = explode( '_', $key );
				$splitKey[0] = $extension::$app;

				$key = implode( '_', $splitKey );
			}

			$extensions[ $key ] = $extension;
		}

		return $extensions;
	}
}