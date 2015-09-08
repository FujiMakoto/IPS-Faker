<?php
/**
 * @brief		Faker Node Generator : Forum
 * @author		<a href='https://www.Makoto.io'>Makoto Fujimoto</a>
 * @copyright	(c) 2015 www.Makoto.io
 * @license		<a href='http://opensource.org/licenses/MIT'>MIT License</a>
 * @package		Faker
 * @subpackage	Fake Content Generator
 * @since		08 Sep 2015
 * @version		0.2.0
 */

namespace IPS\faker\extensions\faker\NodeGenerator;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Faker Node Generator Extension: Forum
 */
class _Forum extends \IPS\faker\Content\Node
{
	/**
	 * @brief   Application name
	 */
	public static $app = 'forums';

	/**
	 * @brief   AdminCP tab restriction
	 */
	public static $acpRestriction = 'faker_generate';

	/**
	 * @brief   Item generator extension
	 */
	public static $commentExtension = 'ItemGenerator';

	/**
	 * @brief	Node Class
	 */
	public static $nodeClass = 'IPS\forums\Forum';

	/**
	 * @brief	Comment Class
	 */
	public static $commentClass = 'IPS\faker\Comment';

	/**
	 * @brief   Generator form title language string
	 */
	public static $title = 'forums_faker_nodes_title';

	/**
	 * @brief   Generator progress message language string
	 */
	public static $message = 'forums_faker_nodes_generator_message';

	/**
	 * Generate a fake node
	 *
	 * @param   \IPS\Node\Model|null    $parent Parent node, or NULL to generate a root node
	 * @param   array                   $values Generator form values
	 * @return  \IPS\Node\Model
	 */
	public function generateSingle( $parent=NULL, array $values )
	{
		$nodeClass = static::$nodeClass;
		$node = new $nodeClass;

		/* Handle submissions */
		if ( isset( $node::$databaseColumnOrder ) )
		{
			$orderColumn = $node::$databaseColumnOrder;
			$node->$orderColumn = \IPS\Db::i()->select( 'MAX(' . $node::$databasePrefix . $orderColumn . ')', $node::$databaseTable  )->first() + 1;
		}

		$nodeValues = array(
			'forum_name'        => $this->generator->title(),
			'forum_type'        => $values['forum_type'],
			'forum_parent_id'   => $parent,
			'forum_password'    => $values['password']
		);

		if ( $values['description'] )
			$nodeValues['forum_description'] = $this->generator->comment();

		if ( $parent )
		{
			$parentColumn = NULL;

			if ( \IPS\Request::i()->subnode )
			{
				if ( isset( $nodeClass::$parentNodeColumnId ) )
				{
					$parentColumn = $nodeClass::$parentNodeColumnId;
				}
			}
			elseif ( isset( $nodeClass::$databaseColumnParent ) )
			{
				$parentColumn = $nodeClass::$databaseColumnParent;
			}

			if ( $parentColumn !== NULL )
			{
				$node->$parentColumn = $parent;
			}
		}

		$node->saveForm( $node->formatFormValues( $nodeValues ) );
		return $node;
	}

	/**
	 * Build a generator form for this node
	 *
	 * @param   \IPS\faker\Decorators\Form  $form
	 * @return  void
	 */
	public function buildGenerateForm( &$form )
	{
		$form->add( new \IPS\Helpers\Form\YesNo( 'description', FALSE ) );

		$form->add( new \IPS\Helpers\Form\NumberRange('node_range', array( 'start' => 3, 'end' => 5 ), TRUE, array(
			'start' => array( 'min' => 1 ),
		) ) );

		$form->add( new \IPS\Helpers\Form\Radio( 'forum_type', 'normal', TRUE, array(
			'options' => array(
				'normal' 	=> 'forum_type_normal',
				'qa' 		=> 'forum_type_qa',
				'category'	=> 'forum_type_category',
				'redirect'	=> 'forum_type_redirect'
			),
			'toggles'	=> array(
				'normal'	=> array( // make sure when adding here that you also add to qa below
					'forum_password_on',
					'forum_ipseo_priority',
					'forum_viglink',
					'forum_min_posts_view',
					'forum_can_view_others',
					'forum_permission_showtopic',
					'forum_permission_custom_error',
					"form_new_header_permissions",
					"form_new_tab_forum_display",
					"form_new_tab_posting_settings",
					"form_new_header_forum_display_topic",
					'forum_preview_posts',
					'forum_icon',
				),
				'qa'	=> array(
					'forum_password_on',
					'forum_ipseo_priority',
					'forum_viglink',
					'forum_min_posts_view',
					'forum_can_view_others_qa',
					'forum_permission_showtopic_qa',
					'forum_permission_custom_error',
					"form_new_header_permissions",
					"form_new_tab_forum_display",
					"form_new_tab_posting_settings",
					"form_new_header_forum_display_question",
					'forum_can_view_others_qa',
					'bw_enable_answers_member',
					'forum_qa_rate_questions',
					'forum_qa_rate_answers',
					'forum_preview_posts_qa',
					'forum_icon',
				),
				'redirect'	=> array(
					'forum_password_on',
					'forum_redirect_url',
					'forum_redirect_hits'
				),
			)
		) ) );

		$form->add( new \IPS\Helpers\Form\Node( 'parent_ids', NULL, FALSE, array(
			'class'		      	=> '\IPS\forums\Forum',
			'multiple'          => TRUE,
			'disabled'	      	=> array(),
			'zeroVal'         	=> 'node_no_parentf',
			// 'zeroValTogglesOff'	=> array( 'form_new_faker_type', '{node}_icon' ),
			'permissionCheck' => function( $node )
			{
				return !isset( \IPS\Request::i()->id ) or ( $node->id != \IPS\Request::i()->id and !$node->isChildOf( $node::load( \IPS\Request::i()->id ) ) );
			}
		), function( $val )
		{
			if ( !$val and \IPS\Request::i()->forum_type !== 'category' )
			{
				throw new \DomainException('faker_parent_id_error');
			}
		} ) );

		$form->add( new \IPS\Helpers\Form\YesNo( 'icon', FALSE ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'password_on', FALSE, FALSE, array( 'togglesOn' => array( 'password', 'password_override' ) ), NULL, NULL, NULL, 'password_on' ) );
		$form->add( new \IPS\Helpers\Form\Password( 'password', NULL, FALSE, array(), NULL, NULL, NULL, 'password' ) );

		/* Defaults */
		$hiddenValues = array(
			'forum_ipseo_priority'          => '0.1',
			'forum_min_posts_view'          => 0,
			'forum_can_view_others'         => TRUE,
			'forum_can_view_others_qa'      => TRUE,
			'forum_permission_showtopic'    => TRUE,
			'forum_permission_showtopic_qa' => TRUE,
			'forum_sort_key'                => 'last_post',
			'forum_show_rules'              => 0,
			'forum_preview_posts'           => FALSE,
			'forum_preview_posts_qa'        => FALSE,
			'forum_inc_postcount'           => TRUE,
			'forum_allow_poll'              => TRUE,
			'forum_min_posts_post'          => TRUE,
			'bw_disable_tagging'            => FALSE,
			'bw_disable_prefixes'           => FALSE
		);

		$form->hiddenValues = array_merge( $form->hiddenValues, $hiddenValues );
	}
}