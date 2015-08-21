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
 */
class _Application extends \IPS\Application
{
	public function get__icon()
	{
		return 'comments';
	}
}