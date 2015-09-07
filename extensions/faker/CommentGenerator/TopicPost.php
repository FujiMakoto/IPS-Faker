<?php
/**
 * @brief		Faker Comment Generator : TopicPosts
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 www.Makoto.io
 * @license		<a href='http://opensource.org/licenses/MIT'>MIT License</a>
 * @package		Faker
 * @subpackage	Fake Content Generator
 * @since		07 Sep 2015
 * @version		0.2.0
 */

namespace IPS\faker\extensions\faker\CommentGenerator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker Comment Generator Extension: TopicPosts
 */
class _TopicPost extends \IPS\faker\Faker\Comment
{
	/**
	 * @brief   Application name
	 */
	public static $app = 'forums';

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction = 'faker_generate';

	/**
	 * @brief   Item generator extension
	 */
	public static $commentExtension = 'ForumTopic';

	/**
	 * @brief	Node Class
	 */
	public static $containerNodeClass = 'IPS\forums\Forum';

	/**
	 * @brief	[Content\Comment]	Item Class
	 */
	public static $itemClass = 'IPS\forums\Topic';

	/**
	 * @brief	[Content\Item]	Comment Class
	 */
	public static $commentClass = 'IPS\forums\Topic\Post';

	/**
	 * @brief   Generator form title language string
	 */
	public static $title = 'forums_faker_comments_title';

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message = 'forums_faker_comments_generator_message';

	/**
	 * Generate a fake content comment
	 *
	 * @param	\IPS\Content\Item   $topic  The content item
	 * @param   array               $values Generator form values
	 *
	 * @return  \IPS\Content\Comment
	 */
	public function generateSingle( \IPS\Content\Item $topic, array $values )
	{
		$generator = new \IPS\faker\Content\Generator();
		$commentClass = static::$commentClass;

		/* Generate the author */
		if ( $values['author'] )
		{
			$member = $values['author'];
		}
		elseif ( $values['author_type'] == 'random_fake' )
		{
			$member = $generator->fakeMember();
		}
		else
		{
			$member = $generator->guest();
		}

		/* Create and save the post */
		$obj = $commentClass::create( $topic, $generator->comment(), TRUE, ( !$member->name ) ? NULL : $member->name, $topic->hidden() ? FALSE : NULL, $member );
		$obj->ip_address = $generator->ipAddress();
		$obj->save();

		$itemClass = static::$itemClass;
		call_user_func_array( array( 'IPS\File', 'claimAttachments' ), array_merge( array( 'newContentItem-' . $topic::$application . '/' . $itemClass::$module  . '-' . 0 ), $obj->attachmentIds() ) );

		return $obj;
	}

	/**
	 * Build a generator form for this comment
	 *
	 * @param   \IPS\faker\Decorators\Form  $form
	 * @return  void
	 */
	public function buildGenerateForm( &$form )
	{
		$form->add( new \IPS\Helpers\Form\Url( 'item_url', NULL, TRUE ) );
		$form->add( new \IPS\Helpers\Form\Select( 'author_type', 'random_fake', TRUE, array(
			'options' => array( 'random_fake' => 'random_fake', 'guest' => 'guest' ), 'unlimited' => '-1',
			'unlimitedLang' => "faker_custom_author", 'unlimitedToggles' => array( 'faker_custom_author' )
		) ) );
		$form->add( new \IPS\Helpers\Form\Member( 'author', NULL, FALSE, array(), NULL, NULL, NULL, 'faker_custom_author' ) );
		$form->add( new \IPS\Helpers\Form\NumberRange('comment_range', array( 'start' => 3, 'end' => 5 ), TRUE, array(
			'start' => array( 'min' => 1 ),
		) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'hide_comment', 0 ) );
	}
}