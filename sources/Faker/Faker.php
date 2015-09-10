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
 */
class _Faker extends \IPS\Patterns\ActiveRecord
{
	/**
	 * Generators
	 */
	const NODES             = 'NodeGenerator';
	const ITEMS             = 'ItemGenerator';
	const COMMENTS          = 'CommentGenerator';
	const ACTIVERECORDS     = 'ActiveRecordGenerator';

	/**
	 * @brief	Database Table
	 */
	public static $databaseTable = 'faker_content_map';

	/**
	 * @brief	Multiton Store
	 */
	protected static $multitons;

	/**
	 * @brief	Default Values
	 */
	protected static $defaultValues = array();

	/**
	 * @brief	Database Column Map
	 */
	public static $databaseColumnMap = array();

	/**
	 * Get a map of fake content items
	 *
	 * @param   null    $class  Content item class, or NULL to return all
	 * @param   int     $limit  Query limit
	 * @param   int     $offset Query offset
	 * @return  Faker[]
	 */
	public static function allFake( $class=NULL, $limit=0, $offset=0 )
	{
		$class = trim( $class, '\\' );
		$where = $class ? array( 'class=?', $class ) : NULL;
		if ( !$limit and !$offset )
		{
			$limit = NULL;
		}
		elseif ( $limit and $offset )
		{
			$limit = array( (int) $offset, (int) $limit );
		}
		$select = \IPS\Db::i()->select( '*', static::$databaseTable, $where, NULL, $limit );

		$fakes = array();
		foreach ( $select as $row ) {
			$fakes[] = static::constructFromData( $row );
		}

		return $fakes;
	}

	/**
	 * Create a map to a fake content item
	 *
	 * @param   string              $class      Content class
	 * @param   int                 $contentId  Content ID
	 * @param   \IPS\Member|null    $author     Member that generated the item, or NULL for the logged in member
	 * @return  int Insert ID
	 */
	public static function createMap( $class, $contentId, $author=NULL )
	{
		$author = $author ?: \IPS\Member::loggedIn();
		return \IPS\Db::i()->insert( static::$databaseTable, array(
			'class'         => trim( $class, '\\' ),
			'content_id'    => $contentId,
			'author'        => $author->member_id,
			'created_at'    => time()
		) );
	}

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
				\IPS\Application::allExtensions( 'faker', static::COMMENTS ),
				\IPS\Application::allExtensions( 'faker', static::ACTIVERECORDS )
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
				$splitKey = array( $extension::$app, end($splitKey) );

				$key = implode( '_', $splitKey );
			}

			$extensions[ $key ] = $extension;
		}

		return $extensions;
	}
}