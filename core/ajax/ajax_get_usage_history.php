<?php 
	include_once(dirname(__FILE__)."/../../lib/session_manager.php");
	include_once(dirname(__FILE__)."/../../lib/billing.php");
	
	// - - - - - - - - - - - - - - - - -
	// On Session Expire Load ROOT_URL
	// - - - - - - - - - - - - - - - - -
	CSessionManager::OnSessionExpire();
	// - - - - - - - - - - - - - - - - -
	
	$user_id = CSessionManager::Get(CSessionManager::STR_USER_ID);
	
	if(isset($_POST['from_date']))
	{
		$objBilling = new CBilling();
		$objBilling->PopulateUsageHistory($user_id, $_POST['from_date'], $_POST['to_date']);
	}
?>