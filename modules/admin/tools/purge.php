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

	/**
	 * Get a class map for all enabled Faker extensions
	 *
	 * @return  string[]
	 */
	protected function getContentTypes()
	{
		$extensions = \IPS\faker\Faker::allExtensions();

		$contentMap = array();
		foreach ( $extensions as $extension )
		{
			$reflect = new \ReflectionClass( $extension );
			$contentMap[ $extension::$class ] = "menu__faker_{$extension::$app}_" . $reflect->getShortName();
		}

		return $contentMap;
	}

	/**
	 * Display the content generation form
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$form = new \IPS\faker\Decorators\Form( 'form', 'faker_purge' );
		$form->langPrefix = 'faker_purge';
		$form->add( new \IPS\Helpers\Form\CheckboxSet( 'content_types', 0, true, array(
			'options' => $this->getContentTypes()
		) ) );

		if ( $values = $form->values() )
		{
			$this->_purgeContent( $values );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=faker&module=tools&controller=purge' ),
				'faker_purge_success' );
			return;
		}

		\IPS\Output::i()->output = $form;
		return;
	}

	/**
	 * Purge fake content from the database
	 * TODO: Implement MultiRedirect support
	 * TODO: This is horribly inefficient (rewrite this as an IN() query and manually construct data)
	 *
	 * @param	array	$values	Generator form values
	 */
	protected function _purgeContent( $values )
	{
		foreach ( $values['content_types'] as $class )
		{
			$fakes = \IPS\faker\Faker::allFake( $class );
			foreach ( $fakes as $fake )
			{
				try
				{
					$obj = $class::load( $fake->content_id );
					$obj->delete();
				}
				catch ( \UnderflowException $e ) {}

				$fake->delete();
			}
		}
	}
}