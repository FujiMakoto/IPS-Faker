<?php

namespace IPS\faker\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Content item abstract class
 */
abstract class _Item implements Extensible
{
	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller = 'items';

	/**
	 * @brief   Application name
	 */
	public static $app;

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction;

	/**
	 * @brief   Comment generator extension
	 */
	public static $commentExtension;

	/**
	 * @brief	Node Class
	 */
	public static $nodeClass;

	/**
	 * @brief	[Content\Comment]	Item Class
	 */
	public static $itemClass;

	/**
	 * @brief   Generator form title language string
	 */
	public static $title;

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message;

	/**
	 * @brief   Comment extension container
	 */
	protected $_commentExtension = NULL;

	/**
	 * @brief   Faker decorator container
	 * @var     \IPS\faker\Content\Generator
	 */
	public $generator = NULL;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->generator = new \IPS\faker\Content\Generator();
	}

	/**
	 * Load the Comments extension for this Item
	 *
	 * @return  mixed   The extension if it exists, otherwise NULL
	 */
	protected function commentExt()
	{
		/* Return the extension if it has already been loaded */
		if ( $this->_commentExtension ) {
			return $this->_commentExtension;
		}

		$extensions = \IPS\faker\Faker::allExtensions( \IPS\faker\Faker::COMMENTS );

		/* Do we have an explicitly defined app for the Comment extension? */
		$app = static::$app;
		$commentExtension = static::$commentExtension;
		if ( is_array( $commentExtension ) )
		{
			$app = $commentExtension[0];
			$commentExtension = $commentExtension[1];
		}

		/* Return the extension if it exists */
		if ( isset($extensions[ $app . '_' . $commentExtension ]) ) {
			return $this->_commentExtension = $extensions[ $app . '_' . $commentExtension ];
		}

		return NULL;
	}

	/**
	 * Generate fake content
	 *
	 * @param   \IPS\Node\Model	$node	The item container
	 * @param   array           $values Generator form values
	 * @return  \IPS\Content\Item
	 */
	abstract public function generateSingle( \IPS\Node\Model $node = null, array $values );

	/**
	 * Bulk process generations
	 *
	 * @param   array       $extData            Extension data ( $ext, $extApp, $extension, $controller )
	 * @param   array|null  $values             Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( array $extData, $values=NULL )
	{
		list( $ext, $extApp, $extension, $controller ) = $extData;
		$vCookie = $extApp . '_faker_' . $controller . '_generator_values';
		$mCookie = $extApp . '_faker_' . $controller . '_generator_map';

		/* If this is a form submission, store our values now */
		if ( $values )
		{
			unset( \IPS\Request::i()->cookie[ $vCookie ] );
			unset( \IPS\Request::i()->cookie[ $mCookie ] );
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
		}
		$values = $values ?: json_decode(\IPS\Request::i()->cookie[ $vCookie ], true);
		$perGo = isset( $values['per_go'] ) ? (int) $values['per_go'] : 25;

		/**
		 * How many items should we generate for each node?
		 * We calculate this information beforehand so we can track our progress in MultipleRedirect
		 */
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
			$generated = array();
			foreach( $nodeMap['nodes'] as $node => &$limit )
			{
				/* Have we reached our per go limit? */
				if ( count($generated) >= $perGo ) {
					break;
				}

				/* Load our node container */
				$containerNodeClass = $ext::$containerNodeClass;
				$_node = $containerNodeClass::load( $node );

				/* Process up to $perGo items from this node */
				$count = 0;
				$_limit = $limit;
				while ( ($count < $_limit) and (count($generated) < $perGo) )
				{
					++$count;
					--$limit;
					$generated[] = $ext->generateSingle( $_node, $values );
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
			return array( $doneSoFar, end($generated), ( 100 * $doneSoFar ) / $total );

		}, function() use( $values, $controller, $extApp, $extension )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal(
				"app=faker&module=generator&controller={$controller}&extApp={$extApp}&extension={$extension}"
			), 'completed' );
		} );
	}
}