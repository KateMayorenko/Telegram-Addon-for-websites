<?
header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);	
//$keyNew="510255677:AAFXToPsudrTVrgjt_1GslyERq3jZygXxu41111";
//setcookie('chatId','1517826900_5A7833546275A');
if (isset($_COOKIE['BotId']))   
{   
  $keyNew=$_COOKIE['BotId'];  
  
}
	include 'tgClass.php';
	$bot= new telegram($keyNew);


	
if(isset($_SERVER['HTTP_X_AJAX'])&&!empty($_SERVER['HTTP_X_AJAX'])&&$_SERVER['HTTP_X_AJAX'] == 'Y') {
	
    // Если к нам идёт Ajax запрос, то ловим его
	if($_GET['act']=='newChat')
	{
		if($_POST['chatCustomerName']!=NULL || $_POST['chatCustomerPhone']!=NULL)
		{
			$Params=array( 
				"chatCustomerName"	=>$_POST['chatCustomerName'],
				"chatCustomerPhone"	=>$_POST['chatCustomerPhone'],
			);
		}
		else
		{
			$Params=array(
				"chatCustomerName"	=>NULL,
				"chatCustomerPhone"	=>NULL,
			);
		}
		
		$chat	= $bot->NewChat($Params);
		//setcookie('botPl',$bot->GetApiKey());
		if($chat)
		{
				echo json_encode(["status" => "ok", "chatId" => $chat]);
		}else{
				echo json_encode(["status" => "error", "chatId" => 'Error create chat']);
		}
	}
	if($_GET['act']=='sendMessage')
	{
		if (isset($_COOKIE['chatId']))  //если нет куки,то
		{
			if($_POST['message']!=NULL || $_POST['chatId']!=NULL)
			{
					$Params=array(
						"msgChatId"	=> 	$_COOKIE['chatId'],
						"msgFrom"	=>	"0",
						"msgTime"	=>	time(),
						"msgText"	=>	$_POST['message'],
						"msgFile"	=>	NULL,
						"msgSend"	=>	"Y",
						"msgManager"=>	NULL,
						"managerName"=> NULL
							
					);
				$check = false; 
				
				$manager = $bot->Get_Manager_Online(); //обращаюсь Get_Manager_Online
				foreach($manager as $oneManager) //прохожусь по всем менеджерам из Get_Manager_Online
				{
	
					if($_COOKIE['chatId']==$oneManager['managerNowChat']) //если chatId чата совпал с chatId менеджера, то
					{
						$Params['msgFrom']=$oneManager['managerId'];
						$Params['msgManager']=$oneManager['managerTelegramId'];
						$Params['managerName']=$oneManager['managerName'];
	
						$check=true;			
					}
					else
					{
						if($check==false)
						{	
							$Params['msgManager'][]=$oneManager['managerTelegramId'];
						}	
					}
				}
			//	$setBotManager	= $bot->ChooseManagerForBot(); 
				$sendMsg = $bot->SendClientMessage($Params);
							
				if($sendMsg)
				{
					//echo json_encode(["status" => "ok", "lastMessageId" => $sendMsg]);
					echo json_encode(["status" => "ok","command" => "newMessages", "lastMessageId" => $sendMsg]);
				}
				else
				{
					echo json_encode(["status" => "error", "lastMessageId" => '0']);
				}
						
			}	
		}else
		{
						//$chat	= $bot->NewChat($Params); //иначе создаю новый чат
						echo json_encode(["status" => "ok", "chatId" => $_COOKIE['chatId']]); 
						
				
		}
	}
	if($_GET['act']=='pollMessages')
	{
		if($_GET['statusIs']==0 || $_GET['statusIs']==1)
		{
			if($_GET['lastMessageId']==0)
			{
				$allmass=$bot->GetMessage($_GET['chatId']);
				echo $allmass;
			}
			elseif($_GET['lastMessageId']>0)
			{
				$lastMessage=$bot->lastMessage($_GET['chatId'],$_GET['lastMessageId']);
				echo $lastMessage;
		
			}
			
		}
		else
		{
			$bot->SendMessage($_GET['chatId'],"Менеджер вышел из чата");
			//unset($_COOKIE['BotId']);
			//unset($_COOKIE['chatId']);
		}		
	}
	
}
else
{
	
	//$lastMessage=$bot->lastMessage('1517826900_5A7833546275A','83');
	//echo $lastMessage;
	//$allmass=$bot->GetMessage('1517826900_5A7833546275A');
	//echo'<pre>';
	//print_r($lastMessage);
	//echo '</pre>';
///	$ttt=$bot->chooseManagerForBot('1518094481_5A7C4891CC2BE','270180588');
	
	//$manager = $bot->AddNewBot('468095847:AAFAiauY2kJWxYUOseqL_qbYsrFbhKcRKN4');
	//$status=$bot->getStatus('1518763717_5A867EC521DFC','json');
		//echo $status;
	
	//$mData = $bot->getStatus('1518763717_5A867EC521DFC');
	$bData = $bot->GetBotsData();
	$mData=$bot->Get_Manager_Online();
//	$manager = $bot->setWebHook('https://store.planum.pro/telegram-api/tgWebhook.php');
//	$getInfo = $bot->GetHookInfo();
	echo'<pre>';
	print_r($manager);
	echo '</pre>'; //проверка
	echo'<pre>';
	print_r($mData);
	echo '</pre>'; //проверка
	echo'<pre>';
	print_r($bData);
	echo '</pre>'; //проверка
	echo'<pre>';
	print_r($getInfo);
	echo '</pre>'; //проверка
	echo'<pre>';
	print_r($ttt);
	echo '</pre>'; //проверка
?>

<html>
<head>
<!---------------------------------------STYLES---------------------------------------------->
	<link rel="stylesheet" href="https://store.planum.pro/telegram-api/css/main.css">
	<link rel="stylesheet" href="https://store.planum.pro/telegram-api/css/bootstrap.min.css">
<!---------------------------------------END STYLES---------------------------------------------->
	
</head>
 <body>
 <!---------------------------------------POST---------------------------------------------->
<!---------------------------------------END POST---------------------------------------------->
<script>
 telegram={"apiUrl":document.location.href, 
 "translationType":"lp", 
 "type":"popup",
 "showLabel":true, 
 "parentElementId":"", 
 "popupbyelement":"",
 "chatPosition":"rb", 
 "attachFile":false, 
 "maxFileSize":"2048",
 "requireName":true,
 "overrideChatCustomerName":null,
 "requirePhone":true, 
 "mainColor":"#eeeeee", 
 "accentColor":"", 
 "textColor":"#000000",
 "fontFamily":"", 
 "boxWidth":"",
 "boxHeight":"",
 "boxZindex":"",
 "label":"Планум чат", 
 "yourName":"Введите Ваше имя",
 "yourPhone":"Введите Ваш телефон",
 "startChat":"Начать чат", 
 "enterYourMessage":"Введите Ваше сообщение и нажмите Enter",
 "you":"Вы", 
 "manager":"<?=$oneManager['managerName']?>", 
 "noManagersAvailable":"Нет менеджеров онлайн", 
 "attachFileTitle":"Прикрепить файл",
 "maxFileSizeError":"Ошибка: максимальный размер файла:",
 "BotId": "<?=$bot->GetApiKey()?>" }
 
 document.addEventListener("DOMContentLoaded", function(){tgChat(telegram);});
</script>
 </body>
 
 <footer>
<!------------------------------SCRIPTS-------------------------------------------->

<script type="text/javascript" src="https://store.planum.pro/telegram-api/js/tgMain.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>

<!------------------------------END SCRIPTS----------------------------------------->
</html>
<?}?>