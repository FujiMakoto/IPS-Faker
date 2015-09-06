<?php
/**
 * @brief		Faker Application Class
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 Makoto Fujimoto
 * @package		IPS Social Suite
 * @subpackage	Faker
 * @since		20 Aug 2015
 * @version		
 */
 
namespace IPS\faker;
\IPS\IPS::$PSR0Namespaces['Faker'] = \IPS\ROOT_PATH . '/applications/faker/sources/3rd_party/vendor/fzaninotto/faker/src/Faker';

/**
 * Faker Application Class
 * @package IPS\faker
 */
class _Application extends \IPS\Application
{
	/**
	 * Application icon
	 *
	 * @return  string
	 */
	public function get__icon()
	{
		return 'plus-square';
	}

	/**
	 * Dynamic AdminCP menu generator for extensions
	 *
	 * @return array
	 */
	public function acpMenu()
	{
		$extensions = \IPS\faker\Faker::allExtensions();

		$menu = array();
		foreach ( $extensions as $key => $extension )
		{
			$splitKey = explode('_', $key);

			/* What app is this extension for? */
			$app = property_exists( $extension, 'app' ) ? $extension::$app : $splitKey[0];

			/* Make sure the application is enabled */
			if ( !\IPS\Application::appIsEnabled( $app ) ) {
				continue;
			}

			/* Do we need to create a new category for this app? */
			if ( !in_array($app, $menu) ) {
				$menu[ $app ] = array();
			}

			$extName = implode( '_', array_slice($splitKey, 1) );
			$menu[ $app ][ $extName ] = array(
				'tab'           => 'faker',
				'controller'    => 'items',  // @TODO: We need to discern the controller
				'do'            => "manage&module=generator&extApp={$app}&extension={$extName}",  // @TODO: This is a hack, we're just overriding the module here by re-declaring it
				'restriction'   => NULL,
			);
		}

		/* Append any misc. modules we want to display here */
		$origMenu = parent::acpMenu();
		$menu['tools'] = $origMenu['tools'];

		return $menu;
	}
}
