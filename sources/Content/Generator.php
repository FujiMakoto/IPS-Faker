<?php

namespace IPS\faker\Content;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class Generator
{
	/**
	 * @brief	Faker instance
	 * @var		\Faker
	 */
	protected $faker;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->faker = \Faker\Factory::create();
	}

	/**
	 * Generate a comment
	 *
	 * @param 	int 	$images			Number of images to include in the comment
	 * @param	int 	$minParagraphs	Minimum number of paragraphs
	 * @param	int 	$maxParagraphs	Maximum number of paragraphs
	 * @param	int 	$minSentences	Minimum number of sentences per paragraph
	 * @param	int 	$maxSentences	Maximum number of sentences per paragraph
	 * @return	string
	 */
	public function comment($images = 0, $minParagraphs = 1, $maxParagraphs = 4, $minSentences = 3, $maxSentences = 9)
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
	 * Generate a fake IP address
	 *
	 * @param	int	$ipv6Chance	Percent chance the returned address will be in ipv6 format
	 * @return	string
	 */
	public function ipAddress($ipv6Chance = 25)
	{
		return ( mt_rand( 0, 100 ) < $ipv6Chance ) ? $this->faker->ipv6 : $this->faker->ipv4;
	}
}