<?php
/**
 * @brief		Fake Content Generator : ForumTopic
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 www.Makoto.io
 * @license		<a href='http://opensource.org/licenses/MIT'>MIT License</a>
 * @package		Faker
 * @subpackage	Fake Content Generator
 * @since		05 Sep 2015
 * @version		0.2.0
 */

namespace IPS\faker\extensions\faker\ItemGenerator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker Content Generator Extension: ForumTopic
 */
class _ForumTopic extends \IPS\faker\Content\Item
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
	 * @brief   Comment generator extension
	 */
	public static $commentExtension = 'TopicPost';

	/**
	 * @brief	Node Class
	 */
	public static $containerNodeClass = 'IPS\forums\Forum';

	/**
	 * @brief	[Content\Comment]	Item Class
	 */
	public static $itemClass = 'IPS\forums\Topic';

	/**
	 * @brief   Generator form title language string
	 */
	public static $title = 'forums_faker_items_title';

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message = 'forums_faker_items_generator_message';

	/**
	 * Generate a fake forum topic
	 *
	 * @param	\IPS\Node\Model	$forum	The forum container
	 * @param   array           $values Generator form values
	 * @return  \IPS\faker\Content\Forum\Topic
	 */
	public function generateSingle( \IPS\Node\Model $forum = null, array $values )
	{
		$itemClass = static::$itemClass;
		$tagsContainer = $values['add_tags'] ? $this->generator->tags() : array( 'tags' => null, 'prefix' => null );

		/* Generate the author */
		if ( $values['author'] )
		{
			$member = $values['author'];
		}
		elseif ( $values['author_type'] == 'random_fake' )
		{
			$member = $this->generator->fakeMember();
		}
		else
		{
			$member = $this->generator->guest();
		}

		/* Assign topic values */
		$topicValues = array(
			'topic_title'			=> $this->generator->title(),
			'topic_content'			=> $this->generator->comment(),
			'topic_tags'			=> $tagsContainer['tags'],
			'topic_tags_prefix'		=> $tagsContainer['prefix'],
			'topic_create_state'	=> $values['after_posting']
		);

		/* Create and save the topic */
		$obj = \IPS\forums\Topic::createItem( $member, $ipAddress = $this->generator->ipAddress(), new \IPS\DateTime, $forum );
		$obj->processForm( $topicValues );
		$obj->faker_fake = 1;
		$obj->save();

		/* Create and save the first post in the topic */
		$comment = $this->commentExt()->generateSingle( $obj, $values, TRUE );

		/* Create any additional posts if necessary */
		if ( $values['add_comments'] )
		{
			$postCount = mt_rand( $values['comment_range']['start'], $values['comment_range']['end'] );
			for ( $pc = 0 ; $pc < $postCount ; $pc++ ) {
				$this->commentExt()->generateSingle( $obj, $values );
			}
		}

		/* Claim attachments and map the first comment ID */
		$commentClass = $itemClass::$commentClass;
		$commentIdColumn = $commentClass::$databaseColumnId;
		call_user_func_array( array( 'IPS\File', 'claimAttachments' ), array_merge( array( 'newContentItem-' . \IPS\forums\Topic::$application . '/' . \IPS\forums\Topic::$module  . '-' . ( $forum ? $forum->_id : 0 ) ), $comment->attachmentIds() ) );

		if ( isset( \IPS\forums\Topic::$databaseColumnMap['first_comment_id'] ) )
		{
			$firstCommentIdColumn = \IPS\forums\Topic::$databaseColumnMap['first_comment_id'];
			$obj->$firstCommentIdColumn = $comment->$commentIdColumn;
			$obj->save();
		}

		return $obj;
	}

	/**
	 * Build a generator form for this content item
	 *
	 * @param   \IPS\faker\Decorators\Form  $form
	 * @return  void
	 */
	public function buildGenerateForm( &$form )
	{
		$form->add( new \IPS\Helpers\Form\Node( 'nodes', null, true, array(
			'url'					=> \IPS\Http\Url::internal( 'app=forums&module=forums&controller=forums&do=createMenu' ),
			'class'					=> static::$containerNodeClass,
			'multiple'				=> true,
		) ) );
		$form->add( new \IPS\Helpers\Form\Select( 'author_type', 'random_fake', true, array(
			'options' => array( 'random_fake' => 'random_fake', 'guest' => 'guest' ), 'unlimited' => '-1',
			'unlimitedLang' => "faker_custom_author", 'unlimitedToggles' => array( 'faker_custom_author' )
		) ) );
		$form->add( new \IPS\Helpers\Form\Member( 'author', null, false, array(), null, null, null, 'faker_custom_author' ) );
		$form->add( new \IPS\Helpers\Form\NumberRange('item_range', array( 'start' => 3, 'end' => 5 ), true, array(
			'start' => array( 'min' => 1 ),
		) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'add_comments', 0, false, array( 'togglesOn' => array( 'faker_comment_range' ) ) ) );
		$form->add( new \IPS\Helpers\Form\NumberRange('comment_range', array( 'start' => 3, 'end' => 5 ), false, array(
			'start' => array( 'min' => 1 ),
		), null, null, null, 'faker_comment_range' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'add_tags', 0 ) );
		$form->add( new \IPS\Helpers\Form\CheckboxSet( 'after_posting', array(), false, array(
			'options' => array( 'lock' => 'lock', 'pin' => 'pin', 'hide' => 'hide', 'feature' => 'feature' )
		) ) );
	}
}