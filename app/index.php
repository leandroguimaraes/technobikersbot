<?php
require_once("config.php");

global $TELEGRAM_CHANNEL_ID, $TELEGRAM_BOT_TOKEN;

$url = "https://api.telegram.org/bot{$TELEGRAM_BOT_TOKEN}/sendPoll";

$data = array(
  "chat_id" => "-{$TELEGRAM_CHANNEL_ID}",
  "question" => "Você vai ao pedal deste domingo 10/JUL?",
  "options" => ["Bora! 🚴‍♂️", "Neste, não... 👎"],
  "type" => "quiz",
  "correct_option_id" => 0,
  "is_anonymous" => "False"
);

$additional_headers = array(
  'Content-Type: application/json'
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $additional_headers);

$server_output = curl_exec($ch);

header('Content-Type: application/json');
echo json_encode(json_decode($server_output), JSON_PRETTY_PRINT);
