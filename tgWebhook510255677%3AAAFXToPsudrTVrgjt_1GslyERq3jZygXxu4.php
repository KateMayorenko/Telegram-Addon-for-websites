<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$file = basename($_SERVER['SCRIPT_FILENAME'], ".php"); 
$key=trim (str_replace('tgWebhook', ' ', $file));



$input=file_get_contents("php://input");

	include 'tgClass.php';
	include 'Config.php';

	$log_file_name = $_SERVER['DOCUMENT_ROOT']."/my_log.txt";
    $now = date("Y-m-d H:i:s");
	$new_input=json_decode($input,true);
    file_put_contents($log_file_name, $now." webhook ".$input."\r\n", FILE_APPEND);
    
    $msgText=$new_input["message"]["text"];
    $chat=$new_input["message"]['chat'];
    
    file_put_contents($log_file_name, $now." text ".$msgText."\r\n", FILE_APPEND);
    
	$message='Ты написал(а) '.$msgText. '-'.$chat['first_name'];
	$bot= new telegram($key); //!!!	
	
	//$bot->SendMessage($chat['id'],$message);

	
	$reg=$bot->IsReg($chat['id']);
	if($reg)
	{
		/*Is Authorized*/
		if(mb_substr($msgText,0,6)=="/login")
		{
			$bot->SendMessage($chat['id'],json_decode('"\ud83d\udd10"')."Вы вошли как менеджер.Команда  \"/login  для регистрации нового менеджера \"");
			
		}
		else
		{	
			if($msgText=="/commands" || $msgText=="/help")
			{
				$bot->SendMessage($chat['id'],"Список команд:\n/offline - статус офлайн (НЕ принимать сообщения от новых клиентов)\n/online - статус онлайн (принимать сообщения от новых клиентов)\n/logout - Удалить себя из системы\n/chat_ID - перейти в чат для общения с клиентом (вместо ID - идентификатор чата)\n/hystory_ID - получить историю сообщений чата (вместо ID - идентификатор чата)\n/newname Name - смена имени менеджера в чате");
			}
			elseif($msgText=="/offline")
			{
				$bot->StatusManager($chat['id'],"0");
				$bot->SendMessage($chat['id'],json_decode('"\u26d4"'). " Вы оффлайн и не будете получать сообщения от новых пользователей");
			}
			elseif($msgText=="/online")
			{
				$bot->StatusManager($chat['id'],"1");
				$bot->SendMessage($chat['id'],json_decode('"\u2705"')." Вы снова онлайн и будете получать сообщения от новых пользователей");
			}
			elseif($msgText=="/logout")
			{
				$bot->RemoveManager($chat['id']);
				$bot->SendMessage($chat['id'],json_decode('"\ud83d\udd10"')."Вы  успешно удалены. Команда  \"/login  для регистрации нового менеджера \"");
			}
			/*ВЫХОД ИЗ ЧАТА-----------------------*/
			elseif($msgText=="/exit")
			{
				$data = $bot->GetBotsData();
				file_put_contents($log_file_name, $now." exit ".json_encode($data)."\r\n", FILE_APPEND);
				if($data['managerId']==$chat['id']) //
				{
					$bot->Refresh($data['botToken'],$data['chatId']);
					$bot->UpdateChat($chat_id['chatId'],$chat['id'],"2");
					$bot->SendMessage($chat['id'],json_decode('"\u2714"')." Вы вышли из чата /chat_".$data['chatId']."");
					$bot->DeleteManager($chat['id']);
					
					$managers=$bot->GetManagers();
						if($managers)
						{
							foreach($managers as $m)
							{
								if($m['managerTelegramId']== $chat['id']) continue;
								
								$bot->SendMessage($m['managerTelegramId'],json_decode('"\u2714"')." Завершен в обслуживании /chat_".$chat_id['chatId']."");
							}
						}

				}
			}
			elseif(mb_substr($msgText,0,6)=="/chat_")
			{
				/*какая то ебань была тут*/
				//$bot->UpdateChat($chat['id'],"1");
				//$bot->SendMessage($chat['id'],json_decode('"\ud83d\udd10"')." Чат занят менеджером");
				
				
				/*А теперь пишем норм код*/
				$chatId=trim(mb_substr($msgText,6));
				 file_put_contents($log_file_name, $now." chatId ".$chatId."\r\n", FILE_APPEND);
				
				$chat_id=$bot->ChooseChat($chatId);
				
				
				if($chat_id['chatId'])
				{
					if($chat_id['chatManager']==$chat['id'])
					{
						$bot->SendMessage($chat['id'],"Ты уже в этом чате");
					}
					elseif($chat_id['chatManager']==0||$chat_id['chatManager']==NULL)
					{
					//	$bot->chooseManagerForBot($chat['chatId'],$chat['id']);
						$bot->UpdateChat($chat_id['chatId'],$chat['id'],"1");
						$bot->UpdateManagerChat($chat_id['chatId'],$chat['id']);
						$bot->SendMessage($chat['id'],json_decode('"\u2714"')." Вы перешли в чат /chat_".$chat_id['chatId']."");
						
						$managers=$bot->GetManagers();
						if($managers)
						{
							foreach($managers as $m)
							{
								if($m['managerTelegramId']== $chat['id']) continue;
								
								$bot->SendMessage($m['managerTelegramId'],json_decode('"\u2714"')." Чат занял другой менеджер /chat_".$chat_id['chatId']."");
							}
						}
						
					}
					else
					{
						$bot->SendMessage($chat['id'],"Чат уже занят другим менеджером");
					}
				}
				else
				{
					$bot->SendMessage($chat['id'],json_decode('"\ud83d\udeab"')." Чат /chat_".$chatId." не существует");
				}	
				
			}
			elseif(mb_substr($msgText,0,9)=="/hystory_")
			{
			
			}
			elseif(mb_substr($msgText,0,8)=="/newname")
			{
				$name=trim(mb_substr($msgText,8));
				
				if($name==NULL)
				{
					$bot->SendMessage($chat['id'],"Заполни имя");	
				}
				else
				{
					$bot->UpdateName($chat['id'],$name);
					$bot->SendMessage($chat['id'],"Ваше имя измененено на ".$name);
				}	
			}
			elseif($reg['managerNowChat']!=null)//нужно изменить поле на bot['managerId']
			{
				$Params=array(
					"msgChatId"	=> $reg['managerNowChat'],
					"msgFrom"	=> "client",
					"msgTime"	=> time(),
					"msgText"	=> $msgText,
					"msgFile"	=>NULL,
					"msgSend"	=>"N",
					"msgManager"=>$chat['id']
					
				);
				
				$bot->SendClientMessage($Params);
				
				//$bot->SendMessage($chat['id'],"я пишу в чате ".$reg['managerNowChat'] );
			}
			else
			{
				$bot->SendMessage($chat['id'],"Для получения списка команд введи \"/commands  или /help \" или выбери чат ");
			}			
		}	
	}
	else
	{
		/*no reg*/
		
		if(mb_substr($msgText,0,6)=="/login")
		{
						
			$password=trim(mb_substr($msgText,6));
			
				if($password==$pass)
				{
					$name=$chat['first_name'].' '.$chat['last_name'];
			
					
					$manager=$bot->NewManager($chat['id'], $name);
	
					if($manager=='error')
					{
						$bot->SendMessage($chat['id'],"Ошибка Вы зарегистрированы! .");
					}
					else
					{
						$bot->SendMessage($chat['id'],"Пароль верный. Вы вошли в систему.");
						$bot->SendMessage($chat['id'],"Ваш статус - /online. Для отключения введите /offline\nЧтобы удалить себя из системы введите /logout");
						$bot->SendMessage($chat['id'],"Ваш имя: ".$name.". Если хотите сменить имя в чате - введите \"/newname Новое Имя\"");
						
					}
				}
				else
				{				
					$bot->SendMessage($chat['id'],"Пароль не верный. Уточните пароль менеджера у администратора системы");				
	
				}
		}
		else
		{
			$bot->SendMessage($chat['id'], json_decode('"\ud83d\udd10"')." Для авторизации введите \"/login пароль_менеджера\"");			
		}
	}
	
	?>