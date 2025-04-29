<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function createWhiteboard() {
    echo "Step 1: Starting function<br>";

    $client_id = '92411c1d60ca1c0b68abbd94665f1323';
    $client_secret = '521d66095d27051f0cc5dc5b8c696c16';

    $auth_url = 'https://api.whiteboard.team/v1/auth';
    $board_url = 'https://api.whiteboard.team/v1/boards';

    echo "Step 2: Preparing auth data<br>";

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
    echo "Step 3: Sending auth request...<br>";
    $auth_result = file_get_contents($auth_url, false, $auth_context);

    if ($auth_result === false) {
        echo "Auth request failed.<br>";
        return;
    }

    echo "Step 4: Auth result received.<br>";
    $auth_response = json_decode($auth_result, true);
    print_r($auth_response); echo "<br>";

    if (!isset($auth_response['access_token'])) {
        echo "No access token found!<br>";
        return;
    }

    $access_token = $auth_response['access_token'];
    echo "Step 5: Got access token<br>";

    // Step 2: Create board
    $board_options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $access_token,
            'method'  => 'POST',
            'content' => json_encode(['title' => 'FlexiDesk Project Board'])
        ]
    ];

    $board_context = stream_context_create($board_options);
    echo "Step 6: Creating board...<br>";
    $board_result = file_get_contents($board_url, false, $board_context);

    if ($board_result === false) {
        echo "Board creation failed.<br>";
        return;
    }

    echo "Step 7: Board created<br>";
    $board_response = json_decode($board_result, true);
    print_r($board_response); echo "<br>";

    if (isset($board_response['link'])) {
        echo "✅ Board link: " . $board_response['link'];
    } else {
        echo "❌ Failed to retrieve board link.";
    }
}

createWhiteboard();
