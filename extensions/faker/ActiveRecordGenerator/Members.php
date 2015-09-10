<?php
/**
 * @brief		Faker ActiveRecord Generator : Members
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 www.Makoto.io
 * @license		<a href='http://opensource.org/licenses/MIT'>MIT License</a>
 * @package		Fake Content Generator
 * @subpackage	Fake Content Generator
 * @since		09 Sep 2015
 * @version		0.2.0
 */

namespace IPS\faker\extensions\faker\ActiveRecordGenerator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker ActiveRecord Generator Extension: Members
 */
class _Members extends \IPS\faker\Content\ActiveRecord
{
	/**
	 * @brief   Application name
	 */
	public static $app = 'core';

	/**
	 * @brief   Content class
	 */
	public static $class = 'IPS\Member';

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction = 'core_faker_generate_members';

	/**
	 * @brief	Active Record class
	 */
	public static $activeRecordClass = '\IPS\Member';

	/**
	 * @brief   Generator form title language string
	 */
	public static $title = 'core_members_faker_activerecords_title';

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message = 'core_members_faker_activerecords_generator_message';

	/**
	 * @brief   Default generator cycle value
	 */
	public static $cycleDefault = 1;

	/**
	 * Generate a fake member account
	 *
	 * @param   array   $values Generator form values
	 * @return  string  Progress message
	 */
	public function generateSingle( array $values )
	{
		/* Create Member */
		$member = new \IPS\Member;
		$member->name               = $values['__generator_message'] = $this->generator->userName();
		$password                   = isset ( $values['password'] ) ? $member->name : $this->generator->password();
		$member->email              = $this->generator->email();
		$member->members_pass_salt  = $member->generateSalt();
		$member->members_pass_hash  = $member->encryptedPassword( $password );
		$member->allow_admin_mails  = 0;
		$member->member_group_id    = $values['member_group'];
		$member->members_bitoptions['view_sigs'] = TRUE;

		if ( $values['profile_photo'] )
		{
			$photoUrl   = new \IPS\Http\Url( $this->generator->photoUrl() );
			$response   = $photoUrl->request()->get();
			$filename   = preg_replace( "/(.+?)(\?|$)/", "$1", mb_substr( (string) $photoUrl, mb_strrpos( (string) $photoUrl, '/' ) + 1 ) );
			$photoFile  = \IPS\File::create( 'core_Profile', $filename, $response );

			$member->pp_photo_type = 'custom';
			$member->pp_main_photo = NULL;
			$member->pp_main_photo = (string) $photoFile;

			$thumbnail = $photoFile->thumbnail( 'core_Profile', \IPS\PHOTO_THUMBNAIL_SIZE, \IPS\PHOTO_THUMBNAIL_SIZE, TRUE );
			$member->pp_thumb_photo = (string) $thumbnail;
		}

		$member->save();
		$this->map( static::$activeRecordClass, $member->member_id );
		return \IPS\Member::loggedIn()->language()->addToStack( static::$message, true, array( 'sprintf' => array($member->name) ) );
	}

	/**
	 * Build a generator form for this comment
	 *
	 * @param   \IPS\faker\Decorators\Form  $form
	 * @return  void
	 */
	public function buildGenerateForm( &$form )
	{
		$groups = \IPS\Member\Group::groups();
		$groupOpts = array();
		foreach ($groups as $group) {
			$groupOpts[$group->g_id] = $group->name;
		}

		$form->langPrefix = 'core_members_faker_activerecords';
		$form->add( new \IPS\Helpers\Form\NumberRange('record_range', array( 'start' => 3, 'end' => 5 ), TRUE, array(
			'start' => array( 'min' => 1 ),
		) ) );
		$form->add( new \IPS\Helpers\Form\Select( 'member_group', \IPS\Settings::i()->member_group, true,
			array( 'options' => $groupOpts ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'profile_photo', true ) );

		$form->add( new \IPS\Helpers\Form\YesNo( 'password' ) );
	}
}