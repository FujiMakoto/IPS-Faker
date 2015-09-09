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
abstract class _ActiveRecord extends \IPS\faker\Content
{
	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller = 'activerecords';

	/**
	 * @brief	Active Record class
	 */
	public static $activeRecordClass;

	/**
	 * Generate fake content
	 *
	 * @param   array   $values Generator form values
	 * @return  \IPS\Patterns\ActiveRecord
	 */
	abstract public function generateSingle( array $values );

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
			unset( \IPS\Request::i()->cookie[ $vCookie ] );
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
		}
		$values = $values ?: json_decode(\IPS\Request::i()->cookie[ $vCookie ], true);
		$perGo = isset( $values['per_go'] ) ? (int) $values['per_go'] : 25;
		$values['total'] = mt_rand( $values['record_range']['start'], $values['record_range']['end'] );

		/* Generate the MultipleRedirect page */
		$reflect    = new \ReflectionClass( $this );
		$extension  = $reflect->getShortName();
		$processUrl = \IPS\Http\Url::internal(
			"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}&do=process"
		);
		return new \IPS\Helpers\MultipleRedirect( $processUrl, function( $doneSoFar ) use( $self, $perGo, $values, $vCookie )
		{
			/* Have we processed everything? */
			if ( $doneSoFar >= $values['total'] ) {
				return NULL;
			}

			$count = 0;
			$generated = array();
			while ( ($count < $values['total']) and (count($generated) < $perGo) )
			{
				++$count;
				$generated[] = $self->generateSingle( $values );
			}
			$doneSoFar += $perGo;

			/* Update our session cookies and proceed to the next chunk */
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
			return array( $doneSoFar, end($generated), ( 100 * $doneSoFar ) / $values['total'] );

		}, function() use( $self, $extension )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal(
				"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}"
			), 'completed' );
		} );
	}
}