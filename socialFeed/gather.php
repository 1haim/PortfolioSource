<?php
	
	$fileContents;

	function expandShortUrl($url) {
		$headers = get_headers($url, 1);

		$loc = $headers['Location'];

		if(is_array($loc)){
			$key = max(array_keys( $loc));
			return $loc[$key];
		} else {
			return $loc;
		}
	}

	function onlyInstgram($target) { // filter through all of the returned images to make sure only instgram photos carry on
		$x = 0;

		foreach ($target as $image) {
			$trueURL = expandShortUrl($image['url']);

			$mystring = $trueURL;
			$findme   = 'http://instagr.am';
			$pos = strpos($mystring, $findme);
			
			if($pos !== 0) {
				unset($target[$x]);
			} else {
				$target[$x]['newURL'] = $trueURL;
			}

			$x++;
		}
		return $target;
	}

	function objectToArray($object) // converts objects to arrays, so I don't have to deal with two different formats
	{
		$array = array();
		foreach($object as $member=>$data)
		{
			$array[$member]=$data;
		}
		return $array;
	}


	$favs 			= "https://twitter.com/favorites/zachwolfstats.xml"; 			// get favorites from my stat collecting twitter account
	$xml 			= simplexml_load_file($favs) or die("could not connect"); 		// parse the information
	$images 		= array(); 														// create a blank array to hold the images
	$textTweets 	= array(); 														// create a blank array to hold the text only tweets

	foreach($xml->status as $status){

		$text 		= $status->text; 												// get just the text from the statue
		$findme   	= 'http://t.co';												// standard twitter URL shortened
		$pos 		= strpos($text, $findme);										// get the position of the url in the text

		if($pos != "") {
			$textArray = explode(" ", $text); 										// split apart each word to search for urls

			foreach($textArray as $word) {
				$findme   = 'http://t.co'; 											// twitter's url
				$pos = strpos($word, $findme); 										// search for the url in each word
				$justText = str_replace($word, "", $text->asXML()); 				// remove the old url from the text

				if($pos === 0) { 													// if there was a url that matched {
					$tempArray = array(	'url' => $word, 
										'text' => htmlspecialchars($justText), 
										'newURL' => ""); 							// create template array
					array_push($images, $tempArray); 								// insert template array into the images array
				}
			}
		} else {																	// if there was no URL, insert them into a text-tweet only array
			$status->text = htmlspecialchars($status->text);
			array_push($textTweets, $status); 										// insert template array into the images array
		}
	}

	$instagrams = onlyInstgram($images); 											// get ride of the twitpic pictures
	$finalInstagrams = array(); 													// created a blank array to hold only the desired amount of images to be displayed
	$displayCount = 4; 																// Display this many images

	foreach ($instagrams as $gram) if (count($finalInstagrams) < $displayCount) { 	// loop through images with limit
		$jsonurl = "http://api.instagram.com/oembed?url=" . $gram['newURL']; 		// ping the instgram API to get the information
		$json = file_get_contents($jsonurl,0,null,null); 							// gather file contents
		$json_output = json_decode($json); 											// turns the JSON response into PHP

		$tempArray = array(	'url' => $gram['url'], 
							'text' => $gram['text'], 
							'newURL' => $gram['newURL'], 
							'instagramInfo' => objectToArray($json_output)); 		// template array
		array_push($finalInstagrams, $tempArray); 									// insert template array
	}

	$ch = curl_init("http://api.dribbble.com/players/hellozachwolf/shots/?page=1&per_page=8");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	$dribbleFeedRaw = curl_exec($ch);
	curl_close($ch);
	$dribbleFeed = json_decode($dribbleFeedRaw);

	$fileContents .= "<div id=\"media-wrap\" class=\"news-feed\">";
		$fileContents .= "\n\t\t\t\t<h3>recent:</h3>";
		$fileContents .= "\n\t\t\t\t<div id=\"tweets\" class=\"feed-section\">";
			$fileContents .= "\n\t\t\t\t\t<h2>Tweets</h2>";
			$fileContents .= "\n\t\t\t\t\t<marquee class=\"sarcastic-marquee\">";
				$fileContents .= "\n\t\t\t\t\t\t<ul>";

					foreach ($textTweets as $tweet):
						$fileContents .= "\n\t\t\t\t\t\t\t<li>";
						$fileContents .= "<a href=\"https://twitter.com/#!/" . $tweet->user->screen_name . "/status/" . $tweet->id  . "\" target=\"_blank\">";
						$fileContents .= "\n\t\t\t\t\t\t\t\t" . $tweet->text;
						$fileContents .= "</a>";
						$fileContents .= "\n\t\t\t\t\t\t\t</li>";
					endforeach;

				$fileContents .= "\n\t\t\t\t\t\t</ul>";
			$fileContents .= "\n\t\t\t\t\t</marquee>";
		$fileContents .= "\n\t\t\t\t</div>";


		$fileContents .= "\n\t\t\t\t<div id=\"instagram\" class=\"feed-section\">";
			$fileContents .= "\n\t\t\t\t\t<h2>Instagrams</h2>";
			$fileContents .= "\n\t\t\t\t\t<ul>";
				
				foreach ($finalInstagrams as $instagram):
					$fileContents .= "\n\t\t\t\t\t\t<li>";
					$fileContents .= "<a href=\"" . $instagram['newURL'] . "\" target=\"_blank\">";
					$fileContents .= "\n\t\t\t\t\t\t\t<img src=\"" . $instagram['instagramInfo']['url'] . "\" alt=\"" . $instagram['instagramInfo']['title'] . "\" />";
					$fileContents .= "\n\t\t\t\t\t\t\t<p>";
					$fileContents .= "\n\t\t\t\t\t\t\t\t" . $instagram['instagramInfo']['title'];
					$fileContents .= "\n\t\t\t\t\t\t\t</p>";
					$fileContents .= "\n\t\t\t\t\t\t</a>";
					$fileContents .= "</li>";
				endforeach;

			$fileContents .= "\n\t\t\t\t\t</ul>";
		$fileContents .= "\n\t\t\t\t</div>";

		$fileContents .= "\n\t\t\t\t<div id=\"dribbble\" class=\"feed-section\">";
			$fileContents .= "\n\t\t\t\t\t<a href=\"" . $dribbleFeed->shots[0]->player->url . "\" target=\"_blank\"><h2>Shots</h2></a>";
			$fileContents .= "\n\t\t\t\t\t<ul>";

			if(is_object($dribbleFeed)):
				foreach ($dribbleFeed->shots as $shot): 
					$fileContents .= "\n\t\t\t\t\t\t<li>";
					$fileContents .= "<a href=\"" . $shot->url . "\" target=\"_blank\">";
					$fileContents .= "\n\t\t\t\t\t\t\t<img src=\"" . $shot->image_teaser_url . "\" alt=\"" . $shot->title . "\" class=\"dribbble-thumb\" />";
					$fileContents .= "</a>";
					$fileContents .= "\n\t\t\t\t\t\t</li>";
				endforeach;
			endif; 

			$fileContents .= "\n\t\t\t\t\t</ul>";
		$fileContents .= "\n\t\t\t\t</div>";
		
	$fileContents .= "\n\t\t\t</div>\n";

$myFile = "media.php";
$fh = fopen($myFile, 'w') or die("can't open file");
fwrite($fh, $fileContents);
fclose($fh);


