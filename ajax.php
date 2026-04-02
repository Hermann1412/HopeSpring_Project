<?php 

include("classes/autoload.php");

$data = file_get_contents("php://input");
if($data != ""){
	$data = json_decode($data);
}

if(isset($data->action) && $data->action == "like_post")
{
	include "ajax/like.ajax.php";
}

if(isset($data->action) && $data->action == "check_messages")
{
	include "ajax/messages.ajax.php";
}

if(isset($data->action) && $data->action == "check_posts")
{
	include "ajax/posts.ajax.php";
}

// Direct GET request for checks
if(isset($_GET['action']) && $_GET['action'] == "check_messages")
{
	include "ajax/messages.ajax.php";
}

if(isset($_GET['action']) && $_GET['action'] == "check_posts")
{
	include "ajax/posts.ajax.php";
}
