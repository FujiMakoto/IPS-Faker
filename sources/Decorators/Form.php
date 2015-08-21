<?php

namespace IPS\faker\Decorators;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Adds support for languages prefixes to the Form class
 * @package IPS\faker\Decorators
 */
class _Form extends \IPS\Helpers\Form
{
	/**
	 * @brief	Language prefix for form labels
	 */
	public $langPrefix = 'faker_form';

	/**
	 * Add Input
	 *
	 * @param	\IPS\Helpers\Form\Abstract	$input	Form element to add
	 * @param	string|NULL					$after	The key of element to insert after
	 * @param	string|NULL					$tab	The tab to insert onto
	 * @return	void
	 */
	public function add( $input, $after=NULL, $tab=NULL )
	{
		if ( $this->langPrefix && !$input->label )
		{
			$input->label = \IPS\Member::loggedIn()->language()->addToStack( "{$this->langPrefix}_{$input->name}" );
		}

		return call_user_func_array( 'parent::add', func_get_args() );
	}
}