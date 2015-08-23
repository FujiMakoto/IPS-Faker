<?php

namespace IPS\faker\modules\admin\forumgen;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Topics generator
 */
class _topics extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'faker_generate_forum_topics' );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'faker_title_topicsGen' );
		parent::execute();
	}

	/**
	 * Display the content generation form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = \IPS\faker\Content\Forum\Topic::buildGenerateForm();

		if ( $values = $form->values() ) {
			$this->_generateTopics( $values );
			return \IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=faker&module=forumgen&controller=topics' ),
				'faker_generate_success' );
		}

		return \IPS\Output::i()->output = $form;
	}

	/**
	 * Generate forum topics
	 * TODO: Implement MultiRedirect support
	 *
	 * @param	array	$values	Generator form values
	 */
	protected function _generateTopics($values)
	{
		foreach ( $values['forums'] as $forum )
		{
			$topicCount = mt_rand( $values['topic_range']['start'], $values['topic_range']['end'] );
			for ( $tc = 0 ; $tc < $topicCount ; $tc++ )
			{
				$topic = \IPS\faker\Content\Forum\Topic::create( $forum, $values );

				if ( $values['add_posts'] )
				{
					$postCount = mt_rand( $values['post_range']['start'], $values['post_range']['end'] );
					for ( $pc = 0 ; $pc < $postCount ; $pc++ ) {
						\IPS\faker\Content\Forum\Post::create( $topic, $values );
					}
				}
			}
		}
	}
}