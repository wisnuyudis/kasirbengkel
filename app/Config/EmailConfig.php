<?php
namespace Config;

class EmailConfig {
	
	public $provider = 'Standard';
	// public $provider = 'Google';
	// public $provider = 'AmazonSES';

	public $client = [	'standard' => [
										'host' => 'mail.ebengkel.fatihhanunnah.com'
										, 'username' => 'support@ebengkel.fatihhanunnah.com'
										, 'password' => 'Password'
									]
						,'google' => ['client_id' => ''
										, 'client_secret' => ''
										, 'refresh_token' => ''
									]
						, 'ses' => ['username' => ''
										, 'password' => ''
									]
					];
	
	// Disesuaikan dengan konfigurasi username
	public $from = 'support@ebengkel.fatihhanunnah.com';
	public $fromTitle = 'ebengkel.fatihhanunnah.com';
	public $emailSupport = 'support@ebengkel.fatihhanunnah.com';
}