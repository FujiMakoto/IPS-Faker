<?php

namespace IPS\faker\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Generator
{
	/**
	 * @brief	Faker instance
	 * @var		\Faker
	 */
	public $faker;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->faker = \Faker\Factory::create();
	}

	/**
	 * Generate a title
	 *
	 * @param	int	$maxChars	Maximum character length of the title
	 *
	 * @return	string
	 */
	public function title( $maxChars = 50 )
	{
		return $this->faker->text( $maxChars );
	}

	/**
	 * Generate a comment
	 *
	 * @param	int 	$minParagraphs	Minimum number of paragraphs
	 * @param	int 	$maxParagraphs	Maximum number of paragraphs
	 * @param 	int 	$images			Number of images to include in the comment
	 * @param	int 	$minSentences	Minimum number of sentences per paragraph
	 * @param	int 	$maxSentences	Maximum number of sentences per paragraph
	 *
	 * @return	string
	 */
	public function comment( $minParagraphs = 1, $maxParagraphs = 4, $images = 0, $minSentences = 3, $maxSentences = 9 )
	{
		/* Generate ipsum text */
		$count = mt_rand( $minParagraphs, $maxParagraphs );
		$ipsumArray = array();
		for ( $c = 0 ; $c < $count ; $c++ ) {
			$ipsumArray[] = $this->faker->paragraph( mt_rand( $minSentences, $maxSentences ) );
		}

		/* Add in images at random */
		for ( $c = 0 ; $c < $images ; $c++ ) {
			$image = "<img src='{$this->faker->imageUrl()}' alt='faker_image' class='ipsImage'>";
			array_splice( $ipsumArray, mt_rand( 0, count($ipsumArray) ), $image );
		}

		/* Generate HTML output from our array */
		$ipsumText = '';
		foreach ( $ipsumArray as $line ) {
			$ipsumText = $ipsumText . "<p>{$line}</p>";
		}

		return $ipsumText;
	}

	/**
	 * Generate an array of tags
	 *
	 * @param	int	$prefixChance	Percent chance the result will have a prefix assigned
	 * @param	int	$min			Minimum number of tags
	 * @param	int	$max			Maximum number of tags
	 *
	 * @return	array
	 */
	public function tags( $prefixChance = 25, $min = 1, $max = 7 )
	{
		$tags = $this->faker->words( mt_rand( $min, $max ) );
		$prefix = ( mt_rand( 0, 100 ) < $prefixChance ) ? array_rand( $tags ) : null;  // 25% chance to add a tag prefix

		return array( 'tags' => $tags, 'prefix' => $prefix );
	}

	/**
	 * Generate a username
	 *
	 * @return	string
	 */
	public function userName()
	{
		return $this->faker->userName;
	}

	/**
	 * Generate an email address
	 *
	 * @return	string
	 */
	public function email()
	{
		return $this->faker->safeEmail;
	}

	/**
	 * Generate URL to a random photo
	 *
	 * @param    null|string $category
	 * @param    int         $width
	 * @param    int         $height
	 *
	 * @return string
	 */
	public function photoUrl( $category = null, $width = 640, $height = 480 )
	{
		return $this->faker->imageUrl( $width, $height, $category );
	}

	/**
	 * Generate a password
	 *
	 * @return	string
	 */
	public function password()
	{
		return $this->faker->password();
	}

	/**
	 * Return a random fake member account (or guest account if none exist)
	 *
	 * @return	\IPS\faker\Content\Member
	 */
	public function fakeMember()
	{
		if ( $fakeMembers = \IPS\faker\Content\Member::allFake() ) {
			return $fakeMembers[ array_rand( $fakeMembers ) ];
		}

		return $this->guest();
	}

	/**
	 * Generate a random guest member object
	 *
	 * @return	\IPS\Member
	 */
	public function guest()
	{
		$member = \IPS\faker\Content\Member::load( 0 );
		$member->name = $this->userName();

		return $member;
	}

	/**
	 * Generate a fake IP address
	 *
	 * @param	int	$ipv6Chance	Percent chance the returned address will be in ipv6 format
	 *
	 * @return	string
	 */
	public function ipAddress( $ipv6Chance = 25 )
	{
		return ( mt_rand( 0, 100 ) < $ipv6Chance ) ? $this->faker->ipv6 : $this->faker->ipv4;
	}
}