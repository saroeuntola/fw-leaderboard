<?php
include('../library/auth.php');
$userAuth = new Auth();

$response = ['status' => false, 'message' => ''];

// Get language (default to 'en')
$lang = isset($_POST['lang']) && in_array($_POST['lang'], ['en', 'bn']) ? $_POST['lang'] : 'bn';

// Messages for live check
$messages = [
    'en' => [
        'username' => 'Username already exists',
        'email'    => 'Email already registered',
        'phone'    => 'Phone number already used'
    ],
    'bn' => [
        'username' => 'ইউজারনেম ইতিমধ্যেই বিদ্যমান',
        'email'    => 'ইমেল ইতিমধ্যেই নিবন্ধিত',
        'phone'    => 'ফোন নম্বর ইতিমধ্যেই ব্যবহৃত'
    ]
];

if (isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];

    if ($userAuth->exists($field, $value)) {
        $response['status'] = true;
        if (isset($messages[$lang][$field])) {
            $response['message'] = $messages[$lang][$field];
        } else {
            $response['message'] = 'Already exists';
        }
    }
}

echo json_encode($response);
exit;
