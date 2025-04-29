<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function createWhiteboard() {
    echo "Step 1: Starting Whiteboard Creation<br>";

    $client_id = '92411c1d60ca1c0b68abbd94665f1323';
    $client_secret = '521d66095d27051f0cc5dc5b8c696c16';

    // Step 2: Get access token using cURL
    $auth_url = 'https://api.whiteboard.team/v1/auth';
    $auth_payload = json_encode([
        'client_id' => $client_id,
        'client_secret' => $client_secret
    ]);

    echo "Step 2: Sending auth request...<br>";

    $ch = curl_init($auth_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $auth_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $auth_result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "❌ Auth cURL error: " . curl_error($ch);
        return;
    }

    $auth_response = json_decode($auth_result, true);
    curl_close($ch);

    if (!isset($auth_response['access_token'])) {
        echo "❌ Auth failed. Response:<br>";
        print_r($auth_response);
        return;
    }

    $access_token = $auth_response['access_token'];
    echo "✅ Auth Success. Got access token.<br>";

    // Step 3: Create whiteboard
    $board_url = 'https://api.whiteboard.team/v1/boards';
    $board_payload = json_encode([
        'title' => 'FlexiDesk Project Board'
    ]);

    $ch2 = curl_init($board_url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $board_payload);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    $board_result = curl_exec($ch2);

    if (curl_errno($ch2)) {
        echo "❌ Board cURL error: " . curl_error($ch2);
        return;
    }

    $board_response = json_decode($board_result, true);
    curl_close($ch2);

    if (isset($board_response['link'])) {
        echo "✅ Whiteboard Created: <a href='{$board_response['link']}' target='_blank'>{$board_response['link']}</a>";
        return $board_response['link'];
    } else {
        echo "❌ Failed to create whiteboard. Response:<br>";
        print_r($board_response);
        return;
    }
}

// TEST the function
createWhiteboard();