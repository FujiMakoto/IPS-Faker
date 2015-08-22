<?php

namespace IPS\faker\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Class _Member
 * @package IPS\faker\Content
 */
class _Member extends \IPS\Member
{
	/**
	 * Return all fake members
	 *
	 * @return	\IPS\faker\Content\Member[]
	 */
	public static function allFake()
	{
		$select = \IPs\Db::i()->select( '*', static::$databaseTable, 'faker_fake=1' );

		$fakeMembers = array();
		foreach ( $select as $row ) {
			$fakeMembers[] = static::constructFromData( $row );
		}

		return $fakeMembers;
	}

	/**
	 * Generate a fake member account
	 *
	 * @param	array	$values	Generator form values
	 *
	 * @return	\IPS\faker\Content\Member
	 */
	public static function create( array $values )
	{
		$generator = new \IPS\faker\Content\Generator();

		/* Create Member */
		$member = new \IPS\Member;
		$member->faker_fake			= 1;
		$member->name				= $generator->userName();
		$member->email				= $generator->email();
		$member->members_pass_salt  = $member->generateSalt();
		$member->members_pass_hash  = $member->encryptedPassword( $generator->password() );
		$member->allow_admin_mails  = 0;
		$member->member_group_id	= $values['member_group'];
		$member->members_bitoptions['view_sigs'] = TRUE;

		if ( $values['profile_photo'] )
		{
			$photoUrl	= new \IPS\Http\Url( $generator->photoUrl() );
			$response	= $photoUrl->request()->get();
			$filename	= preg_replace( "/(.+?)(\?|$)/", "$1", mb_substr( (string) $photoUrl, mb_strrpos( (string) $photoUrl, '/' ) + 1 ) );
			$photoFile	= \IPS\File::create( 'core_Profile', $filename, $response );

			$member->pp_photo_type = 'custom';
			$member->pp_main_photo = NULL;
			$member->pp_main_photo = (string) $photoFile;

			$thumbnail = $photoFile->thumbnail( 'core_Profile', \IPS\PHOTO_THUMBNAIL_SIZE, \IPS\PHOTO_THUMBNAIL_SIZE, TRUE );
			$member->pp_thumb_photo = (string) $thumbnail;
		}

		$member->save();
		return $member;
	}

	/**
	 * Build form to generate
	 *
	 * @return	\IPS\faker\Decorators\Form
	 */
	public static function buildGenerateForm()
	{
		$groups = \IPS\Member\Group::groups();
		$groupOpts = array();
		foreach ($groups as $group) {
			$groupOpts[$group->g_id] = $group->name;
		}

		$form = new \IPS\faker\Decorators\Form( 'form', 'faker_form_generate' );
		$form->langPrefix = 'faker_form';

		$form->add( new \IPS\Helpers\Form\Number( 'member_count', 5, true, array( 'min' => 1 ) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'profile_photo', true ) );
		$form->add( new \IPS\Helpers\Form\Select( 'member_group', \IPS\Settings::i()->member_group, true,
			array( 'options' => $groupOpts ) ) );
		// TODO: Generate status posts

		return $form;
	}
}