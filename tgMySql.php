<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");   
header('Content-Type: text/html; charset=utf-8');

	
class TelegramMysql
{
		
	//таблицы
	public $ApiKey; // Апи ключ бота
	public $baseURL; // ссылка апи телеграмма 
	static private $table_prefix;//префикс таблиц
	static private $chat;
	static private $manager;
	static private $massage;
	static private $bot;
	static private $history; //история всех чатов для повторного захода
		
	
	
	
	//текущие параметры
	protected $Option;


    /**
     * TelegramMysql constructor.
     */
    function __construct()
	{
		
		self::$table_prefix='pl_';//префикс таблиц
		self::$chat=self::$table_prefix.'chat';//список всех корзин
		self::$manager=self::$table_prefix.'manager';//созданные корзины
		self::$massage=self::$table_prefix.'massage';//свойства корзин
		self::$bot=self::$table_prefix.'bot';//список ботов
		self::$history=self::$table_prefix.'history';//история чатов
				
		$key=$this->GetFreeBot();
		
		$this->ApiKey = $key[0]['botToken'];
		$this->baseURL = 'https://api.telegram.org/bot' . $this->ApiKey . "/";
		
		if($Option){
			$Option = \Bitrix\Main\Config\Configuration::getInstance()->get("PLTELEGRAM");
			$this->Option=$Option;
		}
		//если таблицы не созданы, то создаем
		if(!$this->CheckSetup())
		{
			$this->CreateTable();
		}

	}
	/**
	*Функция проверяет установку таблиц.
	*/
	protected function CheckSetup()
	{
		//првоеряем установку
		if($Option["SetupTableTelegram"]!="Y"){
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	*Функция првоеряет или возвращает информацию о полях в таблицах
	* @$table - интересующая на таблица. Если указать какую либо таблицу, то будут перечисленны поля, которые в ней есть
	* @$field - поле, которое надо проверить в бд. Возвратит true/false если поле есть/нет  в таблице $table. Если не указать $table возвратит false
	*/
	protected function mapsTables($table=false,$field=false)
	{
		$arFields=array(
			self::chat=>array(
				"chatId",
				"chatManager",
				"chatCustomerName",
				"chatCustomerPhone",
				"chatBusy"
			),
			self::manager=>array(
				"managerId",
				"managerTelegramId",
				"managerName",
				"managerNowChat",
				"managerStatus"
			),
			self::massage=>array(
				"msgId",
				"msgChatId",
				"msgFrom",
				"msgTime",
				"msgText",
				"msgFile",
				
			),
			self::bot=>array(
				"botId",
				"botToken",
				"managerId",
				"chatId",
				"botBusy",			
			),
			self::history=>array(
				"historyId",
				"botToken",
				"chatId",
				"managerId"
							
			)
		);
		
		if($table!==false&&$field===false)
		{
			return $arFields[$table];
		}
		else if($table!==false&&$field!==false)
		{
			if(in_array($field,$arFields[$table]))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return $arFields;
	}
	
	/**
	*Создает необходимые таблицы
	*/
	protected final function CreateTable()/*добавила chatBusy*/
	{
		$chat_table=" 
		CREATE TABLE IF NOT EXISTS `".self::$chat."` (
		  `chatId` varchar(50) NOT NULL,
		  `chatManager` int(10) DEFAULT NULL,
		  `chatCustomerName` varchar(100) DEFAULT NULL,
		  `chatCustomerPhone` varchar(50) DEFAULT NULL,
		  `chatBusy` int(10) DEFAULT NULL, 
		  PRIMARY KEY (`chatId`),
		  KEY `chId` (`chatId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
		
		
		$manager_table="
		CREATE TABLE IF NOT EXISTS `".self::$manager."` (
		  `managerId` int(10) NOT NULL AUTO_INCREMENT,
		  `managerTelegramId` varchar(100) DEFAULT NULL,
		  `managerName` varchar(100) DEFAULT NULL,
		  `managerNowChat` varchar(100) DEFAULT NULL,
		  `managerStatus` int(1) DEFAULT NULL,
		  PRIMARY KEY (`managerId`),
		  KEY `mId` (`managerId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";

		$Messages_table="
		CREATE TABLE IF NOT EXISTS `".self::$massage."` (
		  `msgId` int(12) NOT NULL AUTO_INCREMENT,
		  `msgChatId` varchar(50) DEFAULT NULL,
		  `msgFrom` varchar(7) DEFAULT NULL,
		  `msgTime` varchar(12) DEFAULT NULL,
		  `msgText` text,
		  `msgFile` text,
		  PRIMARY KEY (`msgId`),
		  KEY `msgId` (`msgId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$Bot_table="
		CREATE TABLE IF NOT EXISTS `".self::$bot."` (
		`botId` int(12) NOT NULL AUTO_INCREMENT,
		`botToken` varchar(150) NOT NULL,
		`managerId` int(10) DEFAULT NULL,
		`chatId` varchar(50) DEFAULT NULL,
		`botBusy` int(10) DEFAULT NULL ,
		PRIMARY KEY (`botId`),
		KEY `botId` (`botId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		$History_table="
		CREATE TABLE IF NOT EXISTS `".self::$history."` (
		`historyId` int(12) NOT NULL AUTO_INCREMENT,
		`botToken` varchar(150) DEFAULT NULL,
		`chatId` varchar(150) DEFAULT NULL,
		`managerId` int(100) DEFAULT NULL,
		PRIMARY KEY (`historyId`), 
		KEY `historyId` (`historyId`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
				
		global $DB;
		//теперь сделаем запрос на создание таблиц
		if(!$this->CheckSetup()){
			
			$DB->Query($chat_table);
			
			$DB->Query($manager_table);
			
			$DB->Query($Messages_table);
			
			$DB->Query($Bot_table);	
			
			$DB->Query($History_table);
			
			//теперь проверим есть ли наши таблицы
			$err=5;
			
			$results = $DB->Query("SHOW TABLES LIKE '".self::$table_prefix."%'");
			while($row = $results->Fetch())
			{
				foreach($row as $k=>$v)
				{
					if($v==self::$chat){
						--$err;
					}
					if($v==self::$manager){
						--$err;
					}
					if($v==self::$massage){
						--$err;
					}
					if($v==self::$bot){
						--$err;
					}
					if($v==self::$history){
						--$err;
					}
				}
			}
			
			if($err>0)
			{
				$APPLICATION->ThrowException("not install table mysql");
				return false;
			}
			else
			{
				//ставим метку того, что все установленно
				$this->Option["SetupTableTelegram"]="Y";
				
				$conf=\Bitrix\Main\Config\Configuration::getInstance();
				$conf->add("PLTELEGRAM",$this->Option);
				$conf->saveConfiguration();				
				
				return true;
			}
			
		}else{
			return true;
		}
		
	}
	// получаем активных менеджеров

    /**
     * @return array
     */
    protected function getActiveManager()
	{
		global $DB;
		
		$str="SELECT * FROM ".self::$manager." WHERE managerStatus = '1' AND managerTelegramId IS NOT NULL";
		
		
		$results = $DB->Query($str);
		
		while($row = $results->Fetch())
		{
			$managers[]=$row;
		}
		
		return 	$managers;
		
	}
		
	// создаем менеджера по команде /login  обязательные параметры ИД - ид телеграма пользователя и имя пользователя в телеграмме

    /**
     * @param $id
     * @param $name
     * @return string
     */
    protected function createManager($id, $name)
	{
		global $DB;
		/*добавляем поле для занятых чатов*/
		$srt="SELECT * FROM ".self::$manager." WHERE managerTelegramId = ".$id."";
		//my_log($srt);
		
		$res=$DB->Query($srt);
		
		$manager = $res->fetch();
		
		if(is_array($manager))
		{
			$res = 'error';
		}
		else
		{
			$Params=array(
				"managerTelegramId" =>$id,
				"managerName"       =>'"'.$name.'"',
				"managerNowChat"	=>'"'.null.'"',
				"managerStatus"		=>"1"
				
			);
			$str="INSERT INTO ".self::$manager." (managerTelegramId , managerName , managerNowChat , managerStatus)
			VALUES (".implode(",", $Params).") " ;
			
			$res=$DB->Query($str);
			$res = $res->fetch();

			
		}	
		return $res;
	}
	/*создаем записи history*/
	
	// проверяем зарегистрировал пользователь телеграмма или нет
    /**
     * @param $id
     * @return mixed
     */
    protected function reg($id)
	{
		global $DB;
		
		$srt="SELECT * FROM ".self::$manager." WHERE managerTelegramId = ".$id."";
		
		$res=$DB->Query($srt);
		
		$manager = $res->fetch();
		
		return $manager;
	}
	// обновляем статус пользователя онлайн или оффлайн status принимает значение 1 или 0

    /**
     * @param $id
     * @param $status
     * @return mixed
     */
    protected function updateManager($id, $status)
	{
		global $DB;
		
		$str="UPDATE ".self::$manager." SET managerStatus=".$status." WHERE managerTelegramId=".$id."";
		
		$res=$DB->Query($str);
		
		$manager = $res->fetch();
		
		return $manager;
		
	}
	/*создам функцию,где проверяю chatManager - если есть, в поле chatBusy - 1, иначе - 0*/
    /**
     * @param $chat_id
     * @param $id_manager
     * @param $status
     * @return mixed
     */
    protected function updChat($chat_id, $id_manager, $status)
	{
		global $DB;
		
		$sct="UPDATE ".self::$chat." SET chatBusy='".$status."', chatManager = '".$id_manager."' WHERE chatId = '".$chat_id."'";
		//my_log($sct);
		$res=$DB->Query($sct);
		
		$chat = $res->fetch();
		
		return $chat;
	}
	// Удаление менеджера из системы 

    /**
     * @param $id
     * @return mixed
     */
    protected function removeAcc($id) //удалить менеджера из системы
	{
		global $DB;
		
		$sct="DELETE FROM ".self::$manager." WHERE managerTelegramId = ".$id."";
		
		$res=$DB->Query($sct);
		
		$manager = $res->fetch();
		
		return $manager;
	}
	
	// обновление имя менеджера 

    /**
     * @param $id
     * @param $name
     * @return mixed
     */
    protected function nameUpdate($id, $name) //обновить имя менеджера
	{
		global $DB;
		
		$str="UPDATE ".self::$manager." SET managerName='".$name."' WHERE managerTelegramId=".$id."";
		
		$res=$DB->Query($str);
		
		$manager = $res->fetch();
		
		return $manager;
	}
	/* создаем чат,записываем в таблицу chat*/
	protected function creatChat($Params)
	{
		global $DB;
		
		$str="INSERT INTO ".self::$chat." (chatId , chatManager , chatCustomerName , chatCustomerPhone, chatBusy)
			VALUES (".implode(",", $Params).") " ;
			
		//my_log($str);
		$res=$DB->Query($str);
		
		$id=$DB->LastID();
		if($id==0)
		{
			$id=1;
		}
		return $id;
	}
	/* получить chatId,чтобы потом занять */
    /**
     * @param $id
     * @return mixed
     */
    protected function getChat($id)
	{
		global $DB;
		
		$str="SELECT * FROM ".self::$chat." WHERE 	chatId = '".$id."'";
		
		$res=$DB->Query($str);
		$chat_id = $res->fetch();
		
		return $chat_id;
	}
	/* обновляем информацию о менеджере,когда он взял чат */
    /**
     * @param $chat_id
     * @param $id
     * @return mixed
     */
    protected function updManagerChat($chat_id, $id)
	{
		global $DB;
		
		$str="UPDATE ".self::$manager." SET managerNowChat='".$chat_id."' WHERE managerTelegramId='".$id."'";
		
		$res=$DB->Query($str);
		
		$manager = $res->fetch();
		
		return $manager;

	}

    /**
     * @param $Params
     * @return mixed
     */
    protected function sendClient($Params)
	 {
		 global $DB;

		 $str="INSERT INTO ".self::$massage." (msgChatId, msgFrom, msgTime, msgText, msgFile)
			VALUES ('".$Params['msgChatId']."','".$Params['msgFrom']."','".$Params['msgTime']."','".$Params['msgText']."','".$Params['msgFile']."')";
		//my_log($str);
		$DB->Query($str);

		$id=$DB->LastID();
		
		return $id;

	 }
	 /*получаем все сообщения чата*/
    /**
     * @param $chatId
     * @return string
     */
    protected function getMesslist($chatId)
	 {
		 global $DB;
		
		    $srt="SELECT * FROM ".self::$massage." WHERE msgChatId = '".$chatId."'  ORDER BY msgTime DESC, msgId DESC";
			$res=$DB->Query($srt);
			
			while($row = $res->Fetch())
			{
				$messages[]=array(  
                    "msgId"       => $row["msgId"],
                    "msgFrom"     => $row["msgFrom"],
                    "msgTime"     => date("j.m H:i:s", $row["msgTime"]),
                    "msgText"     => $row["msgText"],
				   // "managerName" => $row["managerName"],
                   
                );

                if ($lastMessageId == 0) {
                    $lastMessageId = $row["msgId"];
                }
			}
				$lastStatus=$this->GetStatusChat($chatId);
				
				if($lastStatus['chatBusy']==2)
		{
				$lastMessageId='999999999999999999999999999999999999999';
				$messages[]=array(  
                    "msgId"       => '99',
                    "msgFrom"     => 'bot',
                    "msgTime"     => date("j.m H:i:s", time()),
                    "msgText"     =>'exit',
				    //"managerName" => $row["managerName"],
                   
                );
		}		
				

            $result = json_encode([
                "command"       => "allMessages",
                "messages"      => array_reverse($messages),
                "lastMessageId" => $lastMessageId,
                "statusIs"      =>$lastStatus['chatBusy'],
            ]);
			
			return $result;

	 }
	 /*получаем id последнего сообщения в чате*/
    /**
     * @param $chatId
     * @param $lastIdMessage
     * @return string
     */
    protected function GetLastMessage($chatId, $lastIdMessage)
	 {
		 global $DB;
		 
		 $str="SELECT * FROM `".self::$massage."`  WHERE `msgId` > '".$lastIdMessage."' AND `msgChatId` = '".$chatId."' ORDER BY msgTime DESC, msgId DESC";
		// my_log($str);
		 $res=$DB->Query($str);
		 
		 while($row = $res->Fetch())
		{
				$messages[]=array(  
                    "msgId"       => $row["msgId"],
                    "msgFrom"     => $row["msgFrom"],
                    "msgTime"     => date("j.m H:i:s", $row["msgTime"]),
                    "msgText"     => $row["msgText"],
                   // "managerName" => $row["managerName"],
                );

                if ($lastMessageId == 0) {
                    $lastMessageId = $row["msgId"];
                }
		}
		
		
		$lastStatus=$this->GetStatusChat($chatId);
		
		/*if($messages)
		{*/	
		if($lastStatus['chatBusy']==2)
		{
				$lastMessageId='999999999999999999999999999999999999999';
				$messages[]=array(  
                    "msgId"       => '99',
                    "msgFrom"     => 'bot',
                    "msgTime"     => date("j.m H:i:s", time()),
                    "msgText"     =>'exit',
                    "managerName" => 'bot',
                );
		}	
		  $result = json_encode([
                "command"       => "newMessages",
                "messages"      => array_reverse($messages),
                "lastMessageId" => $lastMessageId,
                "statusIs"      =>$lastStatus['chatBusy'],
            ]);
		/*}*/
			
		return $result;	
	 }
	 /*добавляем нового бота в таблицу bot*/
    /**
     * @param $botToken
     * @return int
     */
    protected function AddBot($botToken)
	 {
	    global $DB;
		
		$Params=array(
				"botToken" 	=>'"'.$botToken.'"',
				"managerId" =>'"'.null.'"',
				"chatId"	=>'"'.null.'"',
				"botBusy"	=>"0"
				
			);
			
		$str="SELECT * FROM `".self::$bot."` WHERE `botToken` = '".$botToken."'";
		
		$res=$DB->Query($str);
		$res = $res->fetch();
		
		if($res['botToken']== $botToken)
		{
			$botId=0;
		}
		else
		{
			$stb = "INSERT INTO ".self::$bot." (botToken , managerId , chatId , botBusy)
			VALUES (".implode(",", $Params).") " ; 
				// my_log($stb);
			 $res=$DB->Query($stb);
		
		 
			 $botId=$DB->LastID();
		}
		
		
		
		 return $botId;
		
	 }
	 /*получить токен свободного бота*/
    /**
     * @return array
     */
    protected function GetFreeBot()
	 {
		 global $DB;
		 
		 $str="SELECT * FROM `".self::$bot."` WHERE `botBusy` = 0";
		 
		 $res=$DB->Query($str);
		 
		while($row = $res->Fetch())
		{
			$bots[]=$row;
		}
		
		return $bots;
	 }

    /**
     * @param $chatId
     */
    protected function chooseChatForBot($chatId)
	 {
		 global $DB;
		 
		// $sth = "SELECT * FROM".self::$chat." WHERE chatId=".$chatId."";
		
		 $sth="UPDATE `".self::$bot."` SET chatId='".$chatId."', botBusy = 1 WHERE botToken='".$this->ApiKey."'";
		 
		 $res = $DB->Query($sth);
		  
		 }
	 
	 
	 /*присваиваем менеджера боту*/
    /**
     * @param $chat_id
     * @param $managerId
     */
    protected function chooseManagerForBot($chat_id, $managerId)
	{
		global $DB;
		
		 $sth="UPDATE `".self::$bot."` SET managerId='".$managerId."' WHERE botToken='".$this->ApiKey."' AND chatId='".$chat_id."'";
		// my_log( $sth);
		 $res = $DB->Query($sth);
		 
	}
		// Удаление данных чата при выходе 

    /**
     * @param $chat_id
     */
    protected function removeChatAfterExit($chat_id)
	{
		global $DB;
		
		$str="UPDATE `".self::$chat."` SET chatBusy='2' WHERE chatId='".$chat_id."'";
		
		$res=$DB->Query($str);
	
	}
	// Удаление данных бота при выходе 

    /**
     * @param $botToken
     */
    protected function removeBotAfterExit($botToken)
	{
		global $DB;
		
		$str="UPDATE `".self::$bot."` SET managerId = '0',chatId = '0',botBusy = '0' WHERE botToken='".$botToken."'";
		
		$res=$DB->Query($str);
	
	}
	// Удаление данных менеджера при выходе 

    /**
     * @param $m_id
     */
    protected function removeManagerAfterExit($m_id)
	{
		global $DB;
		
		$str="UPDATE `".self::$manager."` SET managerNowChat = '0' WHERE managerTelegramId='".$m_id."'";
		
		$res=$DB->Query($str);
	
	}

    /**
     * @return array
     */
    protected function busyBots()
	{
		global $DB;
		 
		$str="SELECT * FROM `".self::$bot."` WHERE `botBusy` = 1";
		 
		$res=$DB->Query($str);
		 
		while($row = $res->Fetch())
		{
			$bots[]=$row;
		}
		
		return $bots;
	}

    /**
     * @param $chatId
     * @return mixed
     */
    protected function LastTimeMessage($chatId)
	{
		global $DB;
		 
		$str="SELECT * FROM `".self::$massage."` WHERE `msgChatId`='".$chatId."' ORDER BY msgTime DESC, msgId DESC  LIMIT 1";
		 
		$res=$DB->Query($str);
		 
		while($row = $res->Fetch())
		{
			$bots=$row;
		}
		
		return $bots;

	}

    /**
     * @return mixed
     */
    protected function getBotData()
	{
		global $DB;
		
		$str="SELECT * FROM `".self::$bot."` WHERE `botToken`='".$this->ApiKey."'";
		
		$res=$DB->Query($str);
		
		$botData = $res->Fetch();
		
		return $botData;
		
		
	}

    /**
     * @param $chatId
     * @return mixed
     */
    protected function GetStatusChat($chatId)
	{
		global $DB;
		
		$str="SELECT * FROM `".self::$chat."` WHERE `chatId`='".$chatId."'";
		
		$res=$DB->Query($str);
				
		$status = $res->Fetch();
		
		//  $result = json_encode($status,true);
			
		//	return $result;
		
		//	my_log($str);
		
		return $status;
		
		
	}
					
}
	
?>
