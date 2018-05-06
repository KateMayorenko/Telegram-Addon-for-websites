<?

//echo '<pre>';print_r($_SERVER);echo '</pre>';

$_SERVER['DOCUMENT_ROOT'] = '/home/p/planum/store.planum.pro/public_html';

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);	
	
		include 'tgClass.php';
		
		$bot=new telegram();
		
		$bot_list=$bot->GetBusyBots();
		
		foreach($bot_list as $bots)
		{
			$time=$bot->LastUpdateTime($bots['chatId']);
		
			$diff = strtotime(date("Y-m-d H:i:s",time())) - strtotime(date("Y-m-d H:i:s",$time['msgTime']));
			$hours = $diff/36000;
			$time=round($hours, 2);
			
			if($time >0.5)
			{
				$bot->Refresh($bots['botToken'],$time['msgChatId']);
			}
			
			
		}

		
		
?>