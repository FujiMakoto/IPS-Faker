<?php


namespace IPS\faker\modules\admin\generator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Item generator
 */
class _items extends \IPS\faker\Faker\Controller
{
	/**
	 * @brief   Controller name
	 */
	public static $controller = 'items';

	/**
	 * Bulk process generations
	 *
	 * @param   array|null  $values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( $values=NULL )
	{
		list( $ext, $extApp, $extension, $controller ) = $this->extData();

		/* If this is a form submission, store our values now */
		$vCookie = $ext::$app . '_faker_' . static::$controller . '_generator_values';
		if ( $values )
		{
			unset( \IPS\Request::i()->cookie['faker_generator_values'] );
			unset( \IPS\Request::i()->cookie['faker_generator_map'] );
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
		}
		$values = $values ?: json_decode(\IPS\Request::i()->cookie[ $vCookie ], true);
		$perGo = isset( $values['per_go'] ) ? (int) $values['per_go'] : 25;

		/**
		 * How many items should we generate for each node?
		 * We calculate this information beforehand so we can track our progress in MultipleRedirect
		 */
		$mCookie = $ext::$app . '_faker_' . static::$controller . '_generator_map';
		$nodeMap = isset( \IPS\Request::i()->cookie[ $mCookie ] )
			? json_decode( \IPS\Request::i()->cookie[ $mCookie ], true )
			: NULL;

		if ( !$nodeMap )
		{
			$nodeMap = array( 'total' => 0, 'nodes' => array() );
			foreach ( $values['nodes'] as $id => $node ) {
				$nodeMap['nodes'][ $id ] = mt_rand( $values['item_range']['start'], $values['item_range']['end'] );
			}
			$nodeMap['total'] = array_sum( $nodeMap['nodes'] );

			\IPS\Request::i()->setCookie( $mCookie, json_encode($nodeMap) );
		}
		$total = $nodeMap['total'];

		/* Generate the MultipleRedirect page */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('foo');
		$processUrl = \IPS\Http\Url::internal(
			"app=faker&module=generator&controller={$controller}&extApp={$extApp}&extension={$extension}&do=process"
		);
		return new \IPS\Helpers\MultipleRedirect( $processUrl, function( $doneSoFar ) use( $perGo, $ext, $values, $total, $nodeMap, $vCookie, $mCookie )
		{
			/* Have we processed everything? */
			if ( !array_sum( $nodeMap['nodes'] ) ) {
				return NULL;
			}

			/* Process our nodes */
			$itemsGenerated = array();
			foreach( $nodeMap['nodes'] as $node => &$limit )
			{
				/* Have we reached our per go limit? */
				if ( count($itemsGenerated) >= $perGo ) {
					break;
				}

				/* Process up to $perGo items from this node */
				$containerNodeClass = $ext::$containerNodeClass;
				$count = 0;
				$_limit = $limit;
				while ( ($count < $_limit) and (count($itemsGenerated) < $perGo) )
				{
					++$count;
					--$limit;
					$itemsGenerated[] = $ext::generateSingle( $containerNodeClass::load( $node ), $values );
				}

				/* If we've cleared out this node, remove it from our map and proceed to the next one */
				if ( !$nodeMap['nodes'][$node] ) {
					unset( $nodeMap['nodes'][$node] );
				}
			}

			$doneSoFar += $perGo;

			/* Update our session cookies and proceed to the next chunk */
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
			\IPS\Request::i()->setCookie( $mCookie, json_encode($nodeMap) );
			return array( $doneSoFar, \IPS\Member::loggedIn()->language()->addToStack('generating'), ( 100 * $doneSoFar ) / $total );

		}, function() use( $values )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=gallery&module=gallery&controller=settings' ), 'completed' );
		} );
	}

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}
}