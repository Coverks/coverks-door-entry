<?php

function coverks_post_to_slack($message, $channel, $username, $icon_emoji) {
	
	// Slack webhook endpoint from Slack settings
	$slack_endpoint = "https://hooks.slack.com/services/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";
	
	// Prepare the data / payload to be posted to Slack
	$data = array(
		'payload'   => json_encode( array(
			"channel"       =>  $channel,
			"text"          =>  $message,
			"username"	=>  $username,
			"icon_emoji"    =>  $icon_emoji
			)
		)
	);
	// Post our data via the slack webhook endpoint using wp_remote_post
	$posting_to_slack = wp_remote_post( $slack_endpoint, array(
		'method' => 'POST',
		'timeout' => 30,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => $data,
		'cookies' => array()
		)
	);
}