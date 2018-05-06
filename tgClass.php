<?

/**
 * @param $string
 */
function my_log($string){
			$log_file_name = $_SERVER['DOCUMENT_ROOT']."/my_log.txt";
			$now = date("Y-m-d H:i:s");
			file_put_contents($log_file_name, $now." SQL: ".$string."\r\n", FILE_APPEND);
		}
	
include 'tgMySql.php';
//include 'Config.php';


class telegram extends TelegramMysql
{


    /**
     * telegram constructor.
     * @param null $keyNew
     */
    public function __construct($keyNew=null)
		{
			
			parent::__construct();
			if($keyNew!==NULL)
			{
				$this->ApiKey=$keyNew;
				$this->baseURL = 'https://api.telegram.org/bot' . $this->ApiKey . "/";
			}
			//$this->ApiKey = $key[0]['botToken'];
		   // $this->$baseURLRequest = 'https://api.telegram.org/bot' . $this->ApiKey . "/";
		}
		// дичь какая то
		private function Init()
		{
			$this->request = $this->baseURL;
		}	
		// вывести апи ключ 
		public function GetApiKey()
		{
			return $this->ApiKey;

		}
		//Установка файла для общения с телеграмм апи (перенаправляет сообщения бота в этот фаил и отвечает на комманды)

    /**
     * @param $url
     * @param null $cert
     * @param null $botToken
     * @return array|mixed
     */
    public function setWebHook($url, $cert=null, $botToken=null)
		{
			if($botToken!=null)
			{
				$telegramurl = "https://api.telegram.org/bot".$botToken."/setWebhook";
				$request = curl_init($telegramurl);
				curl_setopt($request, CURLOPT_POST, true);
				$query=array('url' => $url);
				if($cert!=null){
					$query["certificate"] = new CurlFile(realpath($cert));
				}
				curl_setopt($request, CURLOPT_POSTFIELDS, $query);
				curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($request);
				curl_close($request);
				
			}
			else
			{
				$FreeBots=$this->GetFreeBot();
				foreach($FreeBots as $bot)
				{
					$url="https://store.planum.pro/telegram-api/tgWebhook".$bot['botToken'].".php";
					$telegramurl = "https://api.telegram.org/bot".$bot['botToken']."/setWebhook";
					$request = curl_init($telegramurl);
					curl_setopt($request, CURLOPT_POST, true);
					$query=array('url' => $url);
					if($cert!=null){
						$query["certificate"] = new CurlFile(realpath($cert));
					}
					curl_setopt($request, CURLOPT_POSTFIELDS, $query);
					curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
					$result[] = curl_exec($request);
					curl_close($request);
					sleep(2);
					
				}
			}
			return $result;			
		}

    /**
     * @param $botToken
     * @return bool|int
     */
    public function AddNewBot($botToken)
		{
			$botUrl = "https://api.telegram.org/bot".$botToken."/getMe";
			$url=NULL;
			$request = curl_init($botUrl);
			curl_setopt($request, CURLOPT_POST, true);
			$query=array('url' => $url);
			
			curl_setopt($request, CURLOPT_POSTFIELDS, $query);
			curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($request);
			curl_close($request);
			
			$result=json_decode($result,true);
			if($result['ok']==true)
			{
				
				$botList = $this->AddBot($botToken);
				
				if($botList!==0)
				{
					$file = __DIR__."/tgWebhook.php";
					$newfile = __DIR__."/tgWebhook".$botToken.".php";
					
					if (!copy($file, $newfile)) {
					    echo "не удалось скопировать $file...\n";
					}
				}
				
				return $botList;

			}
			else{
				return false;
			}
			
		}
		
		/*Создам функцию,где проверяю наличие менеджера*/
    /**
     * @param $chat_id
     * @param $id_manager
     * @param $status
     * @return mixed
     */
    public function UpdateChat($chat_id, $id_manager, $status)
		{
		
			if(is_numeric($id_manager))
			{
				$this->chooseManagerForBot($chat_id,$id_manager);
				
				$res=$this->updChat($chat_id, $id_manager, $status);
			}
				
				return $res;

		
		}
		// создаем новый чат

    /**
     * @param $Params
     * @return string
     */
    public function NewChat($Params)
		{
			 $manager=$this->getActiveManager();
			
			 if($manager)
			 {
				 foreach($manager as $m)
				 {
				  	$managers[] = $m["managerTelegramId"];
				  }	
			 }
			 else
			 {
				 echo 'No active manager';
			 }
			
						
			//раздает каждому чату уникальный номер
		    $chatId = time().'_'.mb_strtoupper(uniqid());
			
			 $Params=array(
			 	"chatId"            => '"'.$chatId.'"',
			 	"chatManager"		=>'"'.null.'"',
			 	"chatCustomerName"  =>'"'.$Params['chatCustomerName'].'"',
			 	"chatCustomerPhone" =>'"'.$Params['chatCustomerPhone'].'"',
			 	"chatBusy"			=>"0",
				);
				
						
			$chat=$this->creatChat($Params);
			
			
			
			if($chat)
			{
				
				//$this->chooseChatForBot($chatId);

                $bots=$this->GetFreeBot();

                if($bots)
                {
                    $this->chooseChatForBot($chatId);
                }
                else
                    {
                        if($managers)
                        {
                            foreach($managers as $id)
                            {
                                $this->SendMessage($id,"Боты кончились ...");
                            }
                        }

                    }





				if($managers)
				{
					foreach($managers as $id)
					{
						$this->SendMessage($id,"Новый клиент" . $client . " начал чат. Для перехода к чату нажмите /chat_" . $chatId . " или дождитесь сообщения от клиента.");
					}
				}
			}	
					
			return $chatId;
			
		}
		/*данные по всем менеджерам */
    /**
     * @return array
     */
    public function GetManagers()
		{
			 $res=$this->getActiveManager();
					
			return $res;
		}

    /**
     * @param $id
     * @return mixed
     */
    public function ChooseChat($id)
		{
			$is_chat=$this->getChat($id);
			
			return $is_chat;
		}
		
		// отправка сообщения 

    /**
     * @param $chatId
     * @param $message
     * @return mixed
     */
    public function SendMessage($chatId, $message)
		{
			my_log($this->ApiKey);
		    $telegramurl = "https://api.telegram.org/bot" . $this->ApiKey . "/sendMessage";
		    $request = curl_init($telegramurl);
		    curl_setopt($request, CURLOPT_POST, true);
		    $query = ['chat_id' => $chatId, "text" => $message];
		    curl_setopt($request, CURLOPT_POSTFIELDS, $query);
		    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		    $result = curl_exec($request);
		    curl_close($request);
			
			return $result;
		}
	/*	// отправка фото     --------------АТТАЧИ пока не надо
		public function SendPhoto()
		{
			
		}
		// отправка документа
		public function SendDocument()
		{
			
		}
		// получить фото
		public function GetPhoto()
		{
			
		}
		// получить документ
		public function GetDocument()
		{
			
		}*/ //-------------------------------
		/* Создание менеджера передаем id - телеграмм, name - Имя пользователя в телеграмме */

    /**
     * @param $id
     * @param $name
     * @return bool|string
     */
    public function NewManager($id, $name)
		{
			if(is_numeric($id))
			{
				
				$result=$this->createManager($id, $name);
								
				return $result;

			}
			else
			{
				return false;	
			}
		}
		// проверяем зарегистрирован пользователь телеграмм в боте

    /**
     * @param $id
     * @return bool
     */
    public function IsReg($id)
		{
			if(is_numeric($id))
			{
				
				$result=$this->reg($id);
								
				return $result;

			}
			else
			{
				return false;
			}
		}
		// меняем статус менеджера онлайн офлайн

    /**
     * @param $id
     * @param $status
     * @return mixed
     */
    public function StatusManager($id, $status)
		{
			if(is_numeric($id))
			{
				$res=$this->updateManager($id, $status);
			}
				
				return $res;
			
		}
		
		// удалить менеджера

    /**
     * @param $id
     * @return mixed
     */
    public function RemoveManager($id)
		{
		
			if(is_numeric($id))
			{

				$res=$this->removeAcc($id);
			}
				
				return $res;
		}
			
		// обновить Имя менеджера

    /**
     * @param $id
     * @param $name
     * @return mixed
     */
    public function UpdateName($id, $name)
		{
			if(is_numeric($id))
			{
				$res=$this->nameUpdate($id,$name);
			}
				
				return $res;
		}

    /**
     * @param $chat_id
     * @param $id
     */
    public function UpdateManagerChat($chat_id, $id)
		{
			$this->updManagerChat($chat_id,$id);
		}

    /**
     * @return array
     */
    public function Get_Manager_Online()
		{
			 $manager=$this->getActiveManager();
			  return $manager;
			 
						
		}

    /**
     * @param $Params
     * @return mixed
     */
    public function SendClientMessage($Params)
		{
			if($Params['msgSend']=='N')
			{
				$mess=$this->sendClient($Params);
			}
			elseif($Params['msgSend']=='Y')
			{
				if($Params['msgManager']!==NULL)
				{
					$mess=$this->sendClient($Params);
					if($mess)
					{
						if(is_array($Params['msgManager']))
						{
							foreach($Params['msgManager'] as $manager)
							{
								$this->SendMessage($manager,'Сообщение из чата: /chat_'.$Params['msgChatId'].' с сообщением: '.$Params['msgText']);
							}
						}
						else
						{
							$this->SendMessage($Params['msgManager'],$Params['msgText']);
						}
					}	
				}	
			}
			return $mess;
		}

    /**
     * @param $chatId
     * @return string
     */
    public function GetMessage($chatId)
		{
			$mess=$this->getMesslist($chatId);
			
			return $mess;
		}

    /**
     * @param $chatId
     * @param $lastIdMessage
     * @return string
     */
    public function lastMessage($chatId, $lastIdMessage)
		{
			$last=$this->GetLastMessage($chatId, $lastIdMessage);
			
			return $last;
		}

    /**
     * @return array
     */
    public function GetHookInfo()
		{
			$FreeBots=$this->GetFreeBot();
				foreach($FreeBots as $bot)
				{
					$telegramurl = "https://api.telegram.org/bot".$bot['botToken']."/getWebhookinfo";
					$request = curl_init($telegramurl);
					curl_setopt($request, CURLOPT_POST, true);
					$query=array('url' => $url);
					if($cert!=null){
						$query["certificate"] = new CurlFile(realpath($cert));
					}
					curl_setopt($request, CURLOPT_POSTFIELDS, $query);
					curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
					$result[] = curl_exec($request);
					curl_close($request);
					
					$file = __DIR__."/tgWebhook.php";
					$newfile = __DIR__."/tgWebhook".$bot['botToken'].".php";
					
					if (!copy($file, $newfile)) {
					    echo "не удалось скопировать $file...\n";
					}	
					
				}
				return $result;
		}

    /**
     * @return array
     */
    public function GetBusyBots()
		{
			$arBots=$this->busyBots();
			
			return $arBots;
		}

    /**
     * @param $chatId
     * @return mixed
     */
    public function LastUpdateTime($chatId)
		{
			$res=$this->LastTimeMessage($chatId);
			
			return $res;
		}
		/*выход из чата*/
    /**
     * @param $BotId
     * @param $chatId
     */
    public function Refresh($BotId, $chatId)
		{
			$this->removeChatAfterExit($chatId);
			$this->removeBotAfterExit($BotId);
		}

    /**
     * @param $m_id
     */
    public function DeleteManager($m_id)
		{
		$this->removeManagerAfterExit($m_id);
		}
		/**/
    /**
     * @return mixed
     */
    public function GetBotsData()
		{
			$res = $this->getBotData();
			
			return $res;
		}
		/*получаем статус чата - занят,свободен*/
    /**
     * @param $chatId
     * @return string
     */
    public function getStatus($chatId)
		{
			$status = $this->GetStatusChat($chatId);
		//my_log('get $res');
					
			$status = json_encode([
                "command"       => "statusIs",
                "statusIs"      =>$status['chatBusy']
            ],true);
		   		my_log($status);
            	return $status;	
          
		}
		
}
	
	
?>