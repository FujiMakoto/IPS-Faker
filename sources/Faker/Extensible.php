<?php

namespace IPS\faker\Faker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

interface Extensible
{
	/**
	 * Bulk process generations
	 *
	 * @param   array       $extData    Extension data ( $ext, $extApp, $extension, $controller )
	 * @param   array|null  $values     Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( array $extData, $values=NULL );

	/**
	 * Build a generator form for this content item
	 *
	 * @return	\IPS\faker\Decorators\Form
	 */
	public function buildGenerateForm( &$form );
}