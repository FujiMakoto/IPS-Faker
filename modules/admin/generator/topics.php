<?php


namespace IPS\faker\modules\admin\generator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * topics
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
		\IPS\Dispatcher::i()->checkAcpPermission( 'posts_manage' );
		parent::execute();
	}

	/**
	 * Display the content generation form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = new \IPS\faker\Decorators\Form( 'form', 'faker_form_generate' );
		$form->langPrefix = 'faker_form';

		$form->add( new \IPS\Helpers\Form\Node( 'forums', null, true, array(
			'url'					=> \IPS\Http\Url::internal( 'app=forums&module=forums&controller=forums&do=createMenu' ),
			'class'					=> 'IPS\forums\Forum',
			'multiple'				=> true,

		) ) );
		$form->add( new \IPS\Helpers\Form\Select( 'author_type', 'random_fake', true, array(
			'options' => array( 'random_fake', 'guest' ), 'unlimited' => '-1',
			'unlimitedLang' => "faker_form_custom_author", 'unlimitedToggles' => array( 'faker_custom_author' )
		) ) );
		$form->add( new \IPS\Helpers\Form\Member( 'author', null, false, array(), null, null, null, 'faker_custom_author' ) );
		$form->add( new \IPS\Helpers\Form\NumberRange('topic_range', array( 'start' => 3, 'end' => 5 ), true, array(
			'start' => array( 'min' => 1 ),
		) ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'add_posts', 0, false, array( 'togglesOn' => array( 'faker_post_range' ) ) ) );
		$form->add( new \IPS\Helpers\Form\NumberRange('post_range', array( 'start' => 3, 'end' => 5 ), false, array(
			'start' => array( 'min' => 1 ),
		), null, null, null, 'faker_post_range' ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'add_tags', 0 ) );
		$form->add( new \IPS\Helpers\Form\CheckboxSet( 'after_posting', array(), false, array(
			'options' => array( 'create_topic_locked', 'create_topic_pinned', 'create_topic_hidden', 'create_topic_featured' )
		) ) );

		if ( $values = $form->values() ) {
			return $this->_generateTopics( $values );
		}

		return \IPS\Output::i()->output = $form;
	}

	protected function _generateTopics($values)
	{
		$faker = \Faker\Factory::create();
		die(var_dump($values));
	}
}