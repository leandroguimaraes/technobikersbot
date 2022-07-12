<?php
require_once("app/config.php");

global $private_dir, $public_dir, $github_repo_name, $github_secret;

error_reporting(-1);

try {
	// signature check
	$post_data = file_get_contents('php://input');
	$sig_check = 'sha1='.hash_hmac('sha1', $post_data, $github_secret);
	if (!hash_equals($sig_check, $_SERVER['HTTP_X_HUB_SIGNATURE'])) {
		throw new Exception('Invalid signature');
	}

	$payload = json_decode($_REQUEST['payload']);
	if (($payload->ref == 'refs/heads/master') // pushed to master?
			&& ($payload->repository->name == $github_repo_name)) // is this the correct repo?
	{
		chdir($private_dir);
		shell_exec('git pull');
		shell_exec('cp -R '.$private_dir.'app '.$public_dir);
	}
}
catch(Exception $e) {
	http_response_code(500);
}
