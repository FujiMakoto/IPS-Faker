<?php


namespace IPS\faker\Faker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker base controller
 */
class _Controller extends \IPS\Dispatcher\Controller
{
	/**
	 * @brief   Controller name
	 */
	public static $controller = 'item';

	/**
	 * @brief   Extension data container
	 */
	protected $extData = array();

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		/* Make sure we have permission to be here */
		list( $ext ) = $this->extData();
		$acpPermission = property_exists( $ext, 'acpRestriction' ) ? $ext::$acpRestriction : 'faker_generate';
		\IPS\Dispatcher::i()->checkAcpPermission( $acpPermission );

		parent::execute();
	}

	/**
	 * Get request extension data
	 *
	 * @return  array   Extension object, app name, extension name
	 */
	protected function extData()
	{
		/* Return pre-generated extension data if we have it */
		if ( $this->extData )
			return $this->extData;

		/* Make sure our extension app and extension name have been defined */
		if ( !($extApp = \IPS\Request::i()->extApp) or !($extension = \IPS\Request::i()->extension) )
		{
			\IPS\Output::i()->error( 'generic_error', 'FAKER_BAD_REQUEST', 400 );
		}

		/* Try and fetch the requested extension or display a generic 404 error if we can't find it */
		try
		{
			$extensions = \IPS\faker\Faker::allExtensions(
				constant( '\IPS\faker\Faker::' . mb_strtoupper(static::$controller) )
			);
			$ext = $extensions[ \IPS\Request::i()->extApp . '_' . \IPS\Request::i()->extension ];
		}
		catch ( \Whoops\Exception\ErrorException $e )
		{
			\IPS\Output::i()->error( 'node_error', 'FAKER_EXTENSION_NOT_FOUND', 404 );
			return array();
		}

		return $this->extData = array( $ext, $extApp, $extension, static::$controller );
	}

	/**
	 * Display the generator form
	 *
	 * @return  void
	 */
	protected function manage()
	{
		list( $ext, $extApp, $extension, $controller ) = $this->extData();

		/* Build the generator form */
		$form = new \IPS\faker\Decorators\Form( 'form', 'faker_form_generate', \IPS\Http\Url::internal(
			"app=faker&module=generator&controller={$controller}&extApp={$extApp}&extension={$extension}"
		));
		$form->langPrefix = "{$extApp}_faker_{$controller}";
		$ext->buildGenerateForm( $form );

		if ( $values = $form->values() )
		{
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'faker_generator_title', true, array(
				'sprintf' => \IPS\Member::loggedIn()->language()->addToStack( "menu__faker_{$extApp}_{$extension}" )
			) );
			\IPS\Output::i()->output = (string) $ext->generateBulk( $values );
			return;
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( $ext::$title );
		\IPS\Output::i()->output = $form;
	}

	/**
	 * Process a generation request
	 *
	 * @return  void
	 */
	public function process()
	{
		list( $ext ) = $this->extData();
		\IPS\Output::i()->output = (string) $ext->generateBulk();
	}
}