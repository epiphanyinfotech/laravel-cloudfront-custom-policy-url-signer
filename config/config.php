<?php

/*
 * You can place your custom package configuration in here.
 */
return [
	"KEY-ID" => env("AWS_KEY_ID", NULL),
	"AWS-PRIVATE-KEY" => env("AWS_PRIVATE_KEY_FILENAME", NULL),
	"CLIENT_IP" => env("AWS_CLIENT_IP", NULL),
	"EXPIRY_TIME" => env("AWS_EXPIRY_TIME", NULL),
	"AWS_URL" => env("AWS_URL", NULL),
];