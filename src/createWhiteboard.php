<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// your function code below...

function createWhiteboard() {
    $client_id = '92411c1d60ca1c0b68abbd94665f1323';
    $client_secret = '521d66095d27051f0cc5dc5b8c696c16';

    $auth_url = 'https://api.whiteboard.team/v1/auth';
    $board_url = 'https://api.whiteboard.team/v1/boards';

    // Step 1: Get access token
    $auth_data = [
        'client_id' => $client_id,
        'client_secret' => $client_secret
    ];

    $auth_options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => json_encode($auth_data)
        ]
    ];

    $auth_context = stream_context_create($auth_options);
    $auth_result = file_get_contents($auth_url, false, $auth_context);
    $auth_response = json_decode($auth_result, true);

    if (!isset($auth_response['access_token'])) {
        return "Error: Could not authenticate.";
    }

    $access_token = $auth_response['access_token'];

    // Step 2: Create board
    $board_options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $access_token,
            'method'  => 'POST',
            'content' => json_encode(['title' => 'FlexiDesk Project Board'])
        ]
    ];

    $board_context = stream_context_create($board_options);
    $board_result = file_get_contents($board_url, false, $board_context);
    $board_response = json_decode($board_result, true);

    if (isset($board_response['link'])) {
        return $board_response['link'];
    } else {
        return "Error: Could not create board.";
    }
}

// TEST (uncomment this to test it directly)
// echo createWhiteboard();
