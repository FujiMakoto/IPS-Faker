<?php


namespace IPS\faker\modules\admin\membergen;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member generator
 */
class _member extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'member_manage' );
		parent::execute();
	}

	/**
	 * Display the content generation form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = \IPS\faker\Content\Member::buildGenerateForm();

		if ( $values = $form->values() ) {
			return $this->_generateMembers( $values );
		}

		return \IPS\Output::i()->output = $form;
	}

	/**
	 * Generate member accounts
	 * TODO: Implement MultiRedirect support
	 *
	 * @param	array	$values	Generator form values
	 */
	protected function _generateMembers( $values )
	{
		for ( $c = 0 ; $c < $values['member_count'] ; $c++ ) {
			\IPS\faker\Content\Member::create( $values );
		}
	}
}