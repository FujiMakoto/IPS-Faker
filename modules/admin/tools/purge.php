<?php


namespace IPS\faker\modules\admin\tools;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Purge fake content
 */
class _purge extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'faker_tools_purge' );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'faker_title_purge' );
		parent::execute();
	}

	protected function getContentTypes()
	{
		return array(
				'topics' 	=> 'faker_form_topics',
				'members'	=> 'faker_form_members'
		);
	}

	/**
	 * Display the content generation form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = new \IPS\faker\Decorators\Form( 'form', 'faker_purge' );
		$form->langPrefix = 'faker_form';
		$form->add( new \IPS\Helpers\Form\CheckboxSet( 'content_types', 0, true, array(
			'options' => $this->getContentTypes()
		) ) );

		if ( $values = $form->values() ) {
			$this->_purgeContent( $values );
			return \IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=faker&module=tools&controller=purge' ),
				'faker_purge_success' );
		}

		return \IPS\Output::i()->output = $form;
	}

	/**
	 * Purge fake content from the database
	 * TODO: Implement MultiRedirect support
	 *
	 * @param	array	$values	Generator form values
	 */
	protected function _purgeContent($values)
	{
		if ( in_array('topics', $values['content_types']) )
		{
			$topics = \IPS\faker\Content\Forum\Topic::allFake();

			foreach ( $topics as $topic ) {
				$topic->delete();
			}
		}

		if ( in_array('members', $values['content_types']) )
		{
			$members = \IPS\faker\Content\Member::allFake();

			foreach ( $members as $member ) {
				$member->delete();
			}
		}
	}
}