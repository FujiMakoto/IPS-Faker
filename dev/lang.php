<?php

$lang = array(
	'__app_faker'           => "Fake Content Generator",
	'menutab__faker'        => "Faker",
	'menutab__faker_icon'   => "plus-square",
	'faker_generator_title' => "Generating %s",

	/* AdminCP Permissions */
	'r__faker_generate' => 'Generate Fake Content',

	/* Extensions */
	'ext__NodeGenerator'            => 'Generate fake container Nodes (e.g. forums)',
	'ext__ItemGenerator'            => 'Generate fake Content Item submissions (e.g. forum topics)',
	'ext__CommentGenerator'         => 'Generate fake Content Comment submissions (e.g. forum topic posts)',
	'ext__ActiveRecordGenerator'    => 'Generate fake ActiveRecord submissions (e.g. members)',

	/********************************
	 * Forum Generators             *
	 ********************************/
	'menu__faker_forums'    => 'Forum Generators',

	'r__forums_faker_generate_forums'   => 'Generate Forums',
	'r__forums_faker_generate_topics'   => 'Generate Forum Topics',
	'r__forums_faker_generate_posts'    => 'Generate Topic Posts',

	# [Node] Forums
	'menu__faker_forums_Forum'  => 'Forums',

	'forums_faker_nodes_title'              => 'Generate Forums',
	'forums_faker_nodes_generator_message'  => 'Generating forums in %s',

	'forums_faker_nodes_description'    => 'Include description',
	'forums_faker_nodes_forum_type'     => 'Type',
	'forums_faker_nodes_node_range'     => 'Node creation count',
	'forums_faker_nodes_parent_ids'     => 'Parent Forums',
	'forums_faker_nodes_icon'           => 'Include icon',
	'forums_faker_nodes_password_on'    => 'Password protected?',
	'forums_faker_nodes_password'       => 'Password',

	# [Item] Topics
	'menu__faker_forums_ForumTopic' => 'Forum Topics',

	'forums_faker_items_title'               => 'Generate Forum Topics',
	'forums_faker_items_generator_message'   => 'Generating forum topics in %s',

	'forums_faker_items_nodes'           => 'Forums',
	'forums_faker_items_author_type'     => 'Author type',
	'forums_faker_items_author'          => 'Member name',
	'forums_faker_items_item_range'      => 'Topic creation count',
	'forums_faker_items_add_comments'    => 'Create additional posts in topics',
	'forums_faker_items_comment_range'   => 'Post creation count',
	'forums_faker_items_add_tags'        => 'Add tags to topics',
	'forums_faker_items_after_posting'   => 'After posting...',

	# [Comment] Posts
	'menu__faker_forums_TopicPost'  => 'Topic Posts',

	'forums_faker_comments_title'                => 'Generate Topic Posts',
	'forums_faker_comments_generator_message'    => 'Generating topic posts in in %s',

	'forums_faker_comments_item_url'        => 'Topic URL',
	'forums_faker_comments_author_type'     => 'Author type',
	'forums_faker_comments_author'          => 'Member name',
	'forums_faker_comments_comment_range'   => 'Post creation count',
	'forums_faker_comments_hide_comment'    => 'Hide posts',

	/********************************
	 * Core Generators              *
	 ********************************/
	'menu__faker_core'  => 'System Generators',

	'r__core_faker_generate_members'   => 'Generate Member Accounts',

	# [ActiveRecord] Members
	'menu__faker_core_Members'  => 'Member Accounts',

	'core_members_faker_activerecords_title'                => 'Generate Member Accounts',
	'core_members_faker_activerecords_generator_message'    => 'Generating member account %s',

	'core_members_faker_activerecords_record_range'     => 'Member creation count',
	'core_members_faker_activerecords_member_group'     => 'Member group',
	'core_members_faker_activerecords_profile_photo'    => 'Profile photo',
	'core_members_faker_activerecords_password'         => 'Use username as password',

	/* Tools */
	'menu__faker_tools'         => 'Tools',
	'menu__faker_tools_purge'   => 'Delete Fake Content',

	'faker_title_purge'         => 'Purge Faked Content',

	'faker_purge_content_types' => 'Content types',
	'faker_purge'               => 'Purge content',

	/* Misc. */
	'faker_random_fake'         => 'Random fake member',
	'faker_custom_author'       => 'Manually specify a member',
	'faker_generate_success'    => 'Content successfully generated',
	'faker_purge_success'       => 'Fake content successfully deleted',
	'faker_form_generate'       => 'Generate',
	'faker_perGo'               => 'Cycle',


	'faker_form_purge'  => 'Delete Content'
);
