<?php
/**
 * @brief       Development Files Container and Extractor
 * @author      <a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright   (c) 2015 Makoto Fujimoto
 * @package     Development Packer
 * @subpackage  Fake Content Generator
 * @since       11/08/2015
 * @version     0.1.0
 */

namespace IPS\faker;

/**
 * Fake Content Generator Development Files Container
 */
class _DevFiles
{
	/**
	 * @brief    Should development resources be extracted IN_DEV only?
	 */
	public static $checkInDev = FALSE;

	/**
	 * The directory of the application we are managing
	 */
	public static $appDir = 'faker';

	/**
	 * Load and extract our application development files
	 *
	 * @return  void
	 */
	public static function extract()
	{
		/* Are we IN_DEV (and does it matter)? */
		if ( static::$checkInDev )
		{
			if ( !defined('\IPS\IN_DEV') or !\IPS\IN_DEV )
			{
				return;
			}
		}

		/* Attempt to load our application */
		$thisApp = NULL;

		$applications = \IPS\Application::applications();
		foreach ( $applications as $application )
		{
			if ( $application->directory === static::$appDir )
			{
				$thisApp = $application;
			}
		}

		if ( !$thisApp )
		{
			\IPS\Log::i( \LOG_ERR )->write( 'Error : Application ' . static::$appDir . ' not found', 'devpackager_error' );
			return;
		}

		/* Set our paths */
		$appPath = join( \DIRECTORY_SEPARATOR, [ \IPS\ROOT_PATH, 'applications', $thisApp->directory ] );
		$devPath = join( \DIRECTORY_SEPARATOR, [ $appPath, 'dev' ] );
		$devTar  = join( \DIRECTORY_SEPARATOR, [ $appPath, 'data', 'dev.tar' ] );

		/* Load and extract our tarball */
		try
		{
			if ( !is_dir($devPath) ) {
				mkdir( $devPath, 0777, true );
			}

			$devFiles = new \PharData( $devTar, 0, NULL, \Phar::TAR );
			$devFiles->extractTo( $devPath, NULL, TRUE );
		}
		catch ( \Exception $e )
		{
			\IPS\Log::i( \LOG_ERR )->write( 'Error : ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'devpackager_error' );
		}
	}
}