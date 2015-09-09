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
abstract class _Node extends \IPS\faker\Content
{
	/**
	 * @brief   Controller name for menu generation, this should not be modified
	 */
	public static $_controller = 'nodes';

	/**
	 * @brief	Node Class
	 */
	public static $nodeClass;

	/**
	 * Assign default permissions to the node
	 *
	 * @param   \IPS\Node\Model $node
	 */
	public function setPermissions( \IPS\Node\Model $node )
	{
		$nodeClass = static::$nodeClass;

		/* Recommended permissions */
		$current = array();
		foreach ( $nodeClass::$permissionMap as $k => $v )
		{
			switch ( $k )
			{
				case 'view':
				case 'read':
					$current["perm_{$v}"] = '*';
					break;

				case 'add':
				case 'reply':
				case 'review':
				case 'upload':
				case 'download':
				default:
					$current["perm_{$v}"] = implode( ',', array_keys( \IPS\Member\Group::groups( TRUE, FALSE ) ) );
					break;
			}
		}

		$_perms = array();

		/* Check for "all" checkboxes */
		foreach ( $nodeClass::$permissionMap as $k => $v )
		{
			if ( isset( \IPS\Request::i()->__all[ $k ] ) )
			{
				$_perms[ $v ] = '*';
			}
			else
			{
				$_perms[ $v ] = array();
			}
		}

		/* Prepare insert */
		$insert = array( 'app' => $nodeClass::$permApp, 'perm_type' => $nodeClass::$permType, 'perm_type_id' => $node->_id );
		if ( isset( $current['perm_id'] ) )
		{
			$insert['perm_id'] = $current['perm_id'];
		}

		/* Loop groups */
		/*foreach ( $current as $group => $perms )
		{
			foreach ( $nodeClass::$permissionMap as $k => $v )
			{
				if ( isset( $perms[ $k ] ) and $perms[ $k ] and is_array( $current[ $v ] ) )
				{
					$current[ $v ][] = $group;
				}
			}
		}*/

		/* Finalise */
		foreach ( $current as $k => $v )
		{
			$insert[ $k ] = is_array( $v ) ? implode( $v, ',' ) : $v;
		}

		/* Delete existing permissions */
		\IPS\Db::i()->delete( 'core_permission_index', array( 'app=? AND perm_type=? AND perm_type_id=?', $nodeClass::$permApp, $nodeClass::$permType, $node->_id ) );

		/* Insert */
		\IPS\Db::i()->insert( 'core_permission_index', $insert );
	}

	/**
	 * Generate fake content
	 *
	 * @param   \IPS\Node\Model|null    $parent Parent node, or NULL to generate a root node
	 * @param   array                   $values Generator form values
	 * @return  \IPS\Node\Model
	 */
	abstract public function generateSingle( $parent=NULL, array $values );

	/**
	 * Bulk process generations
	 *
	 * @param   array       $extData            Extension data ( $ext, $extApp, $extension, $controller )
	 * @param   array|null  $values             Form submission values
	 * @return  \IPS\Helpers\MultipleRedirect
	 */
	public function generateBulk( $values=NULL )
	{
		$self = $this;
		$vCookie = static::$app . '_faker_' . static::$_controller . '_generator_values';
		$mCookie = static::$app . '_faker_' . static::$_controller . '_generator_map';

		/* If this is a form submission, store our values now */
		if ( $values )
		{
			/* If we have parents defined, we need to save the ID manually for json encoding */
			$pids = array();
			if ( !empty($values['parent_ids']) ) {
				foreach ( $values['parent_ids'] as $id => $node ) {
					$pids[] = $id;
				}
				$values['parent_ids'] = $pids;
			}

			unset( \IPS\Request::i()->cookie[ $vCookie ] );
			unset( \IPS\Request::i()->cookie[ $mCookie ] );
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
		}
		$values = $values ?: json_decode(\IPS\Request::i()->cookie[ $vCookie ], true);
		$perGo = isset( $values['per_go'] ) ? (int) $values['per_go'] : 25;

		/**
		 * How many items should we generate for each parent?
		 * We calculate this information beforehand so we can track our progress in MultipleRedirect
		 */
		$parentMap = isset( \IPS\Request::i()->cookie[ $mCookie ] )
			? json_decode( \IPS\Request::i()->cookie[ $mCookie ], true )
			: NULL;

		if ( !$parentMap )
		{
			$parentMap = array( 'total' => 0, 'parents' => array() );
			if ( is_array($values['parent_ids']) )
			{
				foreach ( $values['parent_ids'] as $parent ) {
					$parentMap['parents'][ $parent ] = mt_rand( $values['node_range']['start'], $values['node_range']['end'] );
				}
			}
			else
			{
				$parentMap['parents'][0] = mt_rand( $values['node_range']['start'], $values['node_range']['end'] );
			}
			$parentMap['total'] = array_sum( $parentMap['parents'] );

			\IPS\Request::i()->setCookie( $mCookie, json_encode($parentMap) );
		}
		$total = $parentMap['total'];

		/* Generate the MultipleRedirect page */
		$reflect    = new \ReflectionClass( $this );
		$extension  = $reflect->getShortName();
		$processUrl = \IPS\Http\Url::internal(
			"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}&do=process"
		);
		return new \IPS\Helpers\MultipleRedirect( $processUrl, function( $doneSoFar ) use( $self, $perGo, $values, $total, $parentMap, $vCookie, $mCookie )
		{
			/* Have we processed everything? */
			if ( !array_sum( $parentMap['parents'] ) ) {
				return NULL;
			}

			/* Process our nodes */
			$generated = array();
			foreach( $parentMap['parents'] as $node => &$limit )
			{
				/* Have we reached our per go limit? */
				if ( count($generated) >= $perGo ) {
					break;
				}

				/* Process up to $perGo items from this node */
				$count = 0;
				$_limit = $limit;
				while ( ($count < $_limit) and (count($generated) < $perGo) )
				{
					++$count;
					--$limit;
					$generated[] = $self->generateSingle( $node, $values );
				}

				/* If we've cleared out this node, remove it from our map and proceed to the next one */
				if ( !$parentMap['parents'][$node] ) {
					unset( $parentMap['parents'][$node] );
				}
			}

			$doneSoFar += $perGo;

			/* Update our session cookies and proceed to the next chunk */
			\IPS\Request::i()->setCookie( $vCookie, json_encode($values) );
			\IPS\Request::i()->setCookie( $mCookie, json_encode($parentMap) );
			return array( $doneSoFar, end($generated), ( 100 * $doneSoFar ) / $total );

		}, function() use( $self, $extension )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal(
				"app=faker&module=generator&controller={$self::$_controller}&extApp={$self::$app}&extension={$extension}"
			), 'completed' );
		} );
	}
}