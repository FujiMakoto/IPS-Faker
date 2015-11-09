<?php
/**
 * @brief		Build process plugin
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Social Suite
 * @subpackage	Fake Content Generator
 * @since		08 Nov 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\faker\extensions\core\Build;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Build process plugin
 */
class _DevFiles
{
	/**
	 * Build
	 *
	 * @return	void
	 * @throws	\RuntimeException
	 */
	public function build()
	{
		/**
		 * Make sure we have the DevPackager application installed.
		 * If you want to require developers install this application, simply remove this check.
		 */
		if ( !class_exists( 'IPS\devpackager\Packager' ) )
		{
			return;
		}

		/**
		 * Package our development files and build our extraction class
		 */
		$devPackager = new \IPS\devpackager\Packager( 'faker', TRUE );
		$devPackager->packageDevFiles();
		$devPackager->createDevFilesClass();
	}
	
	/**
	 * Finish Build
	 * Moved the pbckcode acs.js file over from development as CKEditor's build routine seems to break it
	 *
	 * @return	void
	 */
	protected function finish()
	{
	}
}