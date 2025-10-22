<?php

class MailingList
{

	public static function add_user($email, $firstname='', $lastname='', $source='') 
	{
		$DB = DB::fetch();

		$DB->execute('DELETE FROM tblMailingList WHERE userEmail='.$DB->pdb($email).' AND userSource='.$DB->pdb($source));
		
		$DB->insert('tblMailingList', [
			'userEmail'     => $email,
			'userFirstName' => $firstname,
			'userLastName'  => $lastname,
			'userSource'    => $source,
			'userAdded'     => Util::time_now(),
		]);

	}

}
