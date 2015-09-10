<?php

namespace IPS\faker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Content item abstract class
 */
abstract class _Content
{
	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller;

	/**
	 * @brief   Application name
	 */
	public static $app;

	/**
	 * @brief   Content class
	 */
	public static $class;

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction;

	/**
	 * @brief   Generator form title language string
	 */
	public static $title;

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message;

	/**
	 * @brief   Faker decorator container
	 * @var     \IPS\faker\Content\Generator
	 */
	public $generator = NULL;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->generator = new \IPS\faker\Content\Generator();
	}

	/**
	 * Create a map to the fake content item
	 *
	 * @param   string  $class      Content class
	 * @param   int     $contentId
	 * @return  void
	 */
	protected function map( $class, $contentId )
	{
		\IPS\faker\Faker::createMap( $class, $contentId );
	}

	/**
	 * Bulk process generations
	 *
	 * @param   array|null  $values Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	abstract public function generateBulk( $values=NULL );
}