<?php
date_default_timezone_set('America/Sao_Paulo');

require_once("config.php");

global $TELEGRAM_BOT_TOKEN;

$dbFile = 'db.php';
$url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/";

$isTodayFriday = date('w') == 5;
$isTimeToSend = date('H') == 9;
if ($isTodayFriday && $isTimeToSend) {
  $db = getDbData();
  if (!isset($db->lastSent) || date('Y-m-d', strtotime($db->lastSent)) != date('Y-m-d')) {
    createPoll();
  }
}

function createPoll() {
  $months = [
    'JAN',
    'FEV',
    'MAR',
    'ABR',
    'MAI',
    'JUN',
    'JUL',
    'AGO',
    'SET',
    'OUT',
    'NOV',
    'DEZ'
  ];
  
  $daysToSunday = 7 - date('w');
  $today = date('Y-m-d');
  $sundayTime = strtotime("{$today} + {$daysToSunday} days");
  
  $sunday = date('d/', $sundayTime).$months[date('n', $sundayTime)-1];

  $data = array(
    "question" => "Você vai ao pedal deste domingo {$sunday}?",
    "options" => ["Bora! 🚴‍♂️", "Neste, não... 👎"],
    "type" => "quiz",
    "correct_option_id" => 0,
    "is_anonymous" => "False"
  );

  $db = getDbData();
  $db->lastSent = date('Y-m-d H:i');
  saveDbData($db);

  $result = doPost($data, 'sendPoll');
  $db->messageId = $result->result->message_id;


  $data = array(
    'message_id' => $db->messageId,
    'disable_notification' => true
  );
  $result = doPost($data, 'pinChatMessage');

  saveDbData($db);
}

function getDbData() {
  global $dbFile;

  $dbStr = @file_get_contents($dbFile);
  if ($dbStr) {
    return json_decode($dbStr);
  } else {
    return new stdClass();
  }
}

function saveDbData($data) {
  global $dbFile;

  file_put_contents($dbFile, json_encode($data));
}

function doPost($data, $endPoint) {
  global $TELEGRAM_CHANNEL_ID, $url;

  $data['chat_id'] = "-{$TELEGRAM_CHANNEL_ID}";

  $additional_headers = array(
    'Content-Type: application/json'
  );
  
  $ch = curl_init($url.$endPoint);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $additional_headers);

  return json_decode(curl_exec($ch));
}

function echoResult($result) {
  header('Content-Type: application/json');
  echo json_encode($result, JSON_PRETTY_PRINT);
}
