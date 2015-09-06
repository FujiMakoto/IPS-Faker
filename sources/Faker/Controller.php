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
abstract class _Controller extends \IPS\Dispatcher\Controller
{
	/**
	 * @brief   Controller name
	 */
	public static $controller = 'item';

	/**
	 * Bulk process generations
	 *
	 * @param   array|null  $values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	abstract protected function generateBulk( $values=NULL );

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * Get request extension data
	 *
	 * @return  array   Extension object, app name, extension name
	 */
	protected function extData()
	{
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
		}

		return array( $ext, $extApp, $extension, static::$controller );
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
		$form->langPrefix = "{$extApp}_faker_item";
		$ext->buildGenerateForm( $form );

		if ( $values = $form->values() )
		{
			\IPS\Output::i()->output = (string) $this->generateBulk( $values );
			return;
		}

		\IPS\Output::i()->output = $form;
	}

	/**
	 * Process a generation request
	 *
	 * @return  void
	 */
	public function process()
	{
		\IPS\Output::i()->output = (string) $this->generateBulk();
	}
}