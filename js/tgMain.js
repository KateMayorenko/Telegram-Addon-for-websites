function tgChat(telegram){
	
 	/* Проверяем,подключен ли файл с классами */
	if(!telegram.apiUrl){
		console.error("telegram > "+"Нет доступа к файлу..");
		return false;
	}
		console.log(telegram.apiUrl);
		console.log("Телеграм скрипт запущен");
/*-----------------------------ВНЕШНИЙ ВИД ФОРМЫ------------------------------*/	
		/* Проверяем тип бокса */
		if(telegram.type!="popup" && telegram.type!="embed"){
			telegram.type="popup";
		}
		/* Проверка parent element id (для режима embed) */
		if(telegram.type=="embed"){
			if(!telegram.parentElementId){
				console.error("Planum Chat > "+"Parent Element Id не определен! Он необходим в режиме embed!");
				return false;
			}
		}else{
			/* Проверяем позицию на экране  */
			var pos=telegram.chatPosition
			if(pos!="rb" && pos!="br"){
				telegram.chatPosition="br"; /*default  -  bottom right*/
			}
		}	
		/* Проверяем Ярлык -  настройки текстовых полей */
		if(!telegram.type || telegram.type==""){
			telegram.type="lp"
		}
		/* Проверяем Ярлык - настройки текстовых полей */
		if(!telegram.label || telegram.label==""){
			telegram.label="Планум чат"
		}
		/* Проверяем Имя -  настройки текстовых полей */
		if(!telegram.yourName || telegram.yourName==""){
			telegram.yourName="Введите Ваше имя"
		}
		/* Проверяем Телефон - настройки текстовых полей */
		if(!telegram.yourPhone || telegram.yourPhone==""){
			telegram.yourPhone="Введите Ваш телефон"
		}
		/* Проверяем НачатьЧат - настройки текстовых полей */
		if(!telegram.startChat || telegram.startChat==""){
			telegram.startChat="Начать чат"
		}
		/* Проверяем Введите сообщение -  настройки текстовых полей */
		if(!telegram.enterYourMessage || telegram.enterYourMessage==""){
			telegram.enterYourMessage="Введите Ваше сообщение и нажмите Enter"
		}
		/* Проверяем настройки текстовых полей */
		if(!telegram.you || telegram.you==""){
			telegram.you="You"
		}
		/* Проверяем  настройки текстовых полей */
		if(!telegram.attachFileTitle || telegram.attachFileTitle==""){
			telegram.attachFileTitle="Прикрепить файл"
		}
		/* Проверяем  настройки текстовых полей */
		if(!telegram.maxFileSize || telegram.maxFileSize==""){
			telegram.maxFileSize=2048
		}
		/* Проверяем  настройки текстовых полей */
		if(!telegram.maxFileSizeError || telegram.maxFileSizeError==""){
			telegram.maxFileSizeError="Ошибка: максимальный размер файла:"
		}
		/* Проверяем настройки текстовых полей*/
		if(!telegram.manager || telegram.manager==""){
			telegram.manager=""
		}
		/* Проверяем основной цвет*/
		if(!telegram.mainColor || telegram.mainColor==""){
			telegram.mainColor="#E8E8E8";
		}
		/* Проверяем доп.цвет*/
		if(!telegram.accentColor || telegram.accentColor==""){
			telegram.accentColor="linear-gradient(90deg, #0fb5f3 , #0ebfb7);";
		}
		/* Проверяем цвет шрифта*/
		if(!telegram.textColor || telegram.textColor==""){
			telegram.textColor="#000";
		}
		/* Проверяем шрифт*/
		if(!telegram.fontFamily || telegram.fontFamily==""){
			telegram.fontFamily="arial";
		}
		
		/* Проверяем ширину бокса*/
		if(!telegram.boxWidth || telegram.boxWidth==""){
			if(telegram.type=="embed"){
				telegram.boxWidth="100%";
			}else{
				telegram.boxWidth="350px";
			}
		}
		/* Проверяем высоту бокса*/
		if(!telegram.boxHeight || telegram.boxHeight==""){
			if(telegram.type=="embed"){
				telegram.boxHeight="100%";
			}else{
				telegram.boxHeight="290px";
			}
		}
		/* Проверяем z-index бокса*/
		if(!telegram.boxZindex || telegram.boxZindex==""){
			telegram.boxZindex="9999";
		}
		/* Классы CSS для ярлыка чата (зависят от позиции на странице) */
		if(telegram.type=="popup" && telegram.showLabel==true){
		if(telegram.chatPosition=="rb"){
				var labelClasses="tg-label tg-rotate90 tg-label-"+telegram.chatPosition;
			}
		/* CSS для ярлыка чата */
		var labelStyle=telegram.logo+"font-family:"+telegram.fontFamily+"; background:"+telegram.accentColor+"; z-index:"+telegram.boxZindex;
		
		/* Рисуем ярлык чата */
 		var chatBoxLabelNode = document.createElement("div");
		chatBoxLabelNode.id="telegramChatLabel";
		chatBoxLabelNode.setAttribute("class", labelClasses);
		chatBoxLabelNode.setAttribute("style", labelStyle);
		chatBoxLabelNode.innerHTML=telegram.label;
		console.log(chatBoxLabelNode);
 		document.body.appendChild(chatBoxLabelNode);
		}
		
		/* CSS и классы чатбокса */	
		var chatboxStyle="background-color:"+telegram.mainColor+"; color:"+telegram.textColor+"; font-family:"+telegram.fontFamily+"; width:"+telegram.boxWidth+";height:"+telegram.boxHeight+";z-index:"+telegram.boxZindex+";"
		if(telegram.type=="popup"){
			var chatboxClasses="tg-chatbox tg-chatbox-"+telegram.chatPosition
			if(pos=="lt" || pos=="lb" ){
				chatboxClasses+=" slideInLeft";
			}else if(pos=="rt" || pos=="rb"){
				chatboxClasses+=" slideInRight";
			}else if(pos=="tl" || pos=="tr"){
				chatboxClasses+=" slideInDown";
			}else if(pos=="bl" || pos=="br"){
				chatboxClasses+=" slideInUp";
			}
			chatboxStyle+="display:none; position:fixed;";
		}else{
			var chatboxClasses="tg-chatbox";
			chatboxStyle+="display:block; position:relative;";
		}
 
		var chatBoxNode = document.createElement("div"); 
		chatBoxNode.id="telegramChatBox";
		chatBoxNode.setAttribute("class", chatboxClasses+"  animated");
		chatBoxNode.setAttribute("style", chatboxStyle);
			
		/* Создаем чат бокс */
		var	chatBox="<div id=\"telegramChatBox-header\" class=\"tg-chatbox-header\" style=\"background:"+telegram.accentColor+";\">";
		chatBox+= telegram.label;
		if(telegram.type=="popup"){
		/* Добавляем закрыть в поп-ап форму */
			chatBox+="<div id=\"telegramChatBox-close\" class=\"tg-chatbox-close\">&times;</div>";
		}
		chatBox+="</div>";
		chatBox+="<div id=\"telegramChatBox-greeting\" class=\"tg-chatbox-greeting\">";

		if(telegram.requireName){
		if(telegram.overrideChatCustomerName!=null && telegram.overrideChatCustomerName!=""){
			var chatCustomerName=telegram.overrideChatCustomerName;
			var disableChatCustomerName="disabled"
		}else{
			var chatCustomerName="";	
			var disableChatCustomerName=""		
		}
		chatBox+="<input type=\"text\" "+disableChatCustomerName+" value=\""+chatCustomerName+"\" id=\"chatCustomerName\" class=\"tg-chatbox-greeting-input\" placeholder=\""+telegram.yourName+"\">";
	}
	/*--------------------------условия пользовательские поля-----------------------------*/
		if(telegram.requirePhone){
			chatBox+="<input type=\"text\" id=\"chatCustomerPhone\" class=\"tg-chatbox-greeting-input\" placeholder=\""+telegram.yourPhone+"\">";
		}
		chatBox+="<button id=\"telegramStartChat\" class=\"tg-chatbox-greeting-button\" style=\"background:"+telegram.accentColor+"\">"+telegram.startChat+"</button>";
		chatBox+="</div>";
		chatBox+="<div id=\"telegramChatBox-container\" class=\"tg-chatbox-container\"></div>";
		chatBox+="<div id=\"telegramChatBox-input\" class=\"tg-chatbox-inputArea\">";
		if(telegram.attachFile){
			var paddingForAttachStyle="left:30px;"
		}else{
			var paddingForAttachStyle="left:0px;"
		}
		chatBox+="<div class=\"tg-chatbox-message-container\" id=\"telegramMessageContainer\" style=\""+paddingForAttachStyle+"\">";	
		chatBox+="<textarea id=\"telegramMessage\" class=\"tg-chatbox-message\" placeholder=\""+telegram.enterYourMessage+"\"></textarea>";
		chatBox+="</div>";

		if(telegram.attachFile){
			chatBox+="<div id=\"telegramAttach\" class=\"tg-chatbox-attach\" title=\""+telegram.attachFileTitle+"\"></div>";
			chatBox+="<input type=\"file\" id=\"telegramAttachInput\">";
		}
		chatBox+="<div id=\"telegramEnter\" class=\"tg-chatbox-enter\"></div>";
		chatBox+="</div>";
	
		/* Добавляем чат бокс к тегу body */
		if(telegram.type=="embed"){
			if(telegram.parentElementId){
				var parentElement = document.getElementById(telegram.parentElementId);
				if(parentElement){
					parentElement.appendChild(chatBoxNode);
				}else{
					console.error("Planum Chat > "+"Неивестный parentElementId в чат боксе");
				}
			}
		}else{
			document.body.appendChild(chatBoxNode);
		}

		document.getElementById("telegramChatBox").innerHTML=chatBox;

		if(telegram.attachFile){
			var photoShowNode=document.createElement("div");
			photoShowNode.id="tg-photo-show";
			document.body.appendChild(photoShowNode);

			document.getElementById("tg-photo-show").onclick=function(){
				document.getElementById("tg-photo-show").style.display="none"
				document.getElementById("tg-big-image").remove()
			} 
	 	bindAttachFile();
		}

	/* Привязываем клик на открытие и закрытие ярлыка чата*/
	if(telegram.type=="popup"){
		if(telegram.popupbyelement==null || telegram.popupbyelement==""){
			document.getElementById('telegramChatLabel').onclick=function(){
				document.getElementById('telegramChatBox').style.display="block";
				document.getElementById('telegramChatLabel').style.display="none";
				tgScrollDown();
			};
			document.getElementById('telegramChatBox-close').onclick=function(){
				document.getElementById('telegramChatLabel').style.display="block";
				document.getElementById('telegramChatBox').style.display="none";
			};
		}else{
			document.getElementById(telegram.popupbyelement).onclick=function(){
				document.getElementById('telegramChatBox').style.display="block";
				tgScrollDown();
			};
			document.getElementById('telegramChatBox-close').onclick=function(){
				document.getElementById('telegramChatBox').style.display="none";
			};
		}
	}
 
	/* Привязываем клик на отправку сообщения */
	document.getElementById('telegramEnter').onclick=function(){
		sendMessage();
	};

	/* Привязываем нажатие ctrl+enter в текстовом поле для отправки сообщения */
	document.getElementById('telegramMessage').onkeypress=function(e){
		if ((e.ctrlKey || e.metaKey) && (e.keyCode == 13 || e.keyCode == 10)) {
			document.getElementById('telegramMessage').value+="\r"
		}else if(e.keyCode == 13 || e.keyCode == 10){
			sendMessage();
		}
	};

	/* Проверяем куки*/
	var chatId = telegramGetCookie("chatId");
	var botId = telegramGetCookie("botPl");
	console.log("bot id cookie "+ botId);
	telegram.chatId=chatId;
	console.log("chatId " + chatId);

	/* Если chatId существует -> выдаем все сообщения и обновления */
	if(telegram.chatId){
		if(telegram.translationType=="sse"){
			startTranslation();
		}else{
			startLongPoll();
		}
	/* If chatId нет и персональные поля не требуются -> начинаем новый чат */
	}else if(telegram.requireName==false && telegram.requirePhone==false && telegram.type=="embed"){
		newChat();
	}else{
		document.getElementById("telegramStartChat").onclick=function(){
			
			newChat();
		};
	}
	
	
	
/*-----------------------------------------------------------*/		
/*-----------------------------LongPoll запрос------------------------------- */	

var lastMessageId=0;
var statusIs=0;
longPoll=null;
longPollTimer=null;
function startLongPoll(){
	//debugger;
	console.log("Planum Chat > Получаю данные через LongPoll ");
	
	
	document.getElementById('telegramChatBox-greeting').style.display="none";
	document.getElementById('telegramChatBox-container').style.display="block";
	document.getElementById('telegramChatBox-input').style.display="block";
	try{longPoll.abort();}catch(e){}
	try{clearTimeout(longPollTimer);}catch(e){}
	/*пользователь запрашивает ответ от сервера*/
	longPoll = new XMLHttpRequest();
	longPoll.timeout=20000;
	/*debugger;*/
	var longPollURL=telegram.apiUrl+"?act=pollMessages&type=lp&chatId="+telegram.chatId+"&lastMessageId="+lastMessageId+"&statusIs="+statusIs;
 	longPoll.open('POST', longPollURL, true);
	longPoll.setRequestHeader('X-AJAX', 'Y');
	longPoll.send();
 	longPoll.onreadystatechange = function(){
		if(this.readyState==4){

			if(this.status == 200){
				if(this.responseText){
					try{
						//debugger;
						var data = JSON.parse(this.responseText);
				//	console.log(data.messages[0]['msgText']);
					console.log(data);
		
														
						if(data.command=="allMessages"){
								if(data.messages[0]['msgText']!="exit")
							{
								addMessages(data.messages);
							}
					//		addMessages(data.messages)
							lastMessageId=data.lastMessageId;
							statusIs=data.statusIs;
							
							if(statusIs==2)
							{
							//	debugger;
								longPoll.abort();
								addSystemMessage("Чат завершен", "tg-danger");	
								clearTimeout(longPollTimer);
								telegramDeleteChatCookie("BotId");
								telegramDeleteChatCookie("chatId");
							}else{
								if(lastMessageId==0){
								longPollTimer=setTimeout(function(){
									startLongPoll();
								}, 20000);
								}else{
								startLongPoll();
								}
							}
							
								
						}else if(data.command=="newMessages"){
							//debugger;
						//	console.log(data);
								if(data.messages[0]['msgText']!="exit")
							{
								addMessages(data.messages);
							}
						//	addMessages(data.messages);
							lastMessageId=data.lastMessageId;
							statusIs=data.statusIs;
							if(statusIs==2)
						{
						//	debugger;
							longPoll.abort();
							addSystemMessage("Чат завершен", "tg-danger");	
							clearTimeout(longPollTimer);
							telegramDeleteChatCookie("BotId");
							telegramDeleteChatCookie("chatId");
						}else{
							if(telegram.sound){
								telegram.sound.play();
							}
							
							if(lastMessageId==0){
								longPollTimer=setTimeout(function(){
									startLongPoll();
								}, 2000);
							}else{
								startLongPoll();
							}
						}
							
								
						}else if(data.command=="timeout"){
							startLongPoll();
						}else if(data.command=="statusIs") //оповещение о состоянии чата менеджерам
						{
							statusIs = data.statusIs;
						//	console.log(statusIs);
							startLongPoll();
						}
						else if(data.command=="error"){
							if(data.error=="Неверный ID чата"){
								try{
									longPoll.abort();
									clearTimeout(longPollTimer);
									newChat()
								}catch(e){}
							}else{
								console.error("Planum Chat > "+data.error)
								longPollTimer=setTimeout(function(){
									startLongPoll();
								}, 2000);
							}
						}
					}catch(e){
						//console.log(this.responseText);
						longPollTimer=setTimeout(function(){
							startLongPoll();
						}, 2000);
					}
				}else{
				//	console.log(this.responseText);
					longPollTimer=setTimeout(function(){
						startLongPoll();
					}, 2000);
				}
			}else{
		//		console.log(this);
				longPollTimer=setTimeout(function(){
					startLongPoll();
				}, 2000);
			}
		}
	}

}
	
/*------------------------------------------------------------ */	
/*-----------------------------Новый чат--------------------------------------*/	
function newChat(){
	console.log("Planum Chat > "+"Начинаю новый чат");
	//debugger;
	try{serverSideEvent.close();}catch(e){}
	try{longPoll.abort();}catch(e){}
	try{clearTimeout(longPollTimer);}catch(e){}

	var xhr = new XMLHttpRequest();
	
	xhr.open('POST', telegram.apiUrl+"?act=newChat", true);
	xhr.setRequestHeader('X-AJAX', 'Y');
	var formData = new FormData();
	if(telegram.requireName || telegram.overrideChatCustomerName!=null){
		if(telegram.overrideChatCustomerName!=null && telegram.overrideChatCustomerName!=""){
			var chatCustomerName=telegram.overrideChatCustomerName;
		}else{
			var chatCustomerName= document.getElementById("chatCustomerName").value			
		}
		formData.append("chatCustomerName",chatCustomerName);
	}
	if(telegram.requirePhone){
		formData.append("chatCustomerPhone", document.getElementById("chatCustomerPhone").value)
	}
	xhr.send(formData);
	xhr.onreadystatechange = function(){
	//	console.log(this);
		if(this.readyState==4){
			if(this.status == 200){
				if(this.responseText){
					try{
						var answer = JSON.parse(this.responseText);
						if(answer.status=="ok"){
							console.log(answer);
							telegram.chatId=answer.chatId;
							telegram.manager=answer.manager;
							telegramSetCookie("chatId",telegram.chatId,{"expires":360000,"path":"/"});
							telegramSetCookie("BotId",telegram.BotId,{"expires":360000,"path":"/"});
							if(telegram.translationType=="sse"){
								startTranslation();
							}else{
								startLongPoll();
									addSystemMessage("Задайте свой вопрос нашему менеджеру", "tg-start");	
							}
						}else{
							document.getElementById('telegramChatBox-greeting').style.display="none";
							document.getElementById('telegramChatBox-container').style.display="block";
						
 							if(answer.error=="NO_MANAGERS_AVALIABLE"){
								
							}else{
								addSystemMessage(answer.error, "tg-danger");
							}
						}
					}catch(e){
						console.error("Planum Chat > "+"Не могу создать новый чат");
						console.log(this.responseText);
					}
				}else{
					console.error("Planum Chat > "+"Не могу создать новый чат");
					console.log(this.responseText);
				}
			}else{
				console.error("Planum Chat > "+"Не могу создать новый чат");
				console.log(this);
			}
		}
	}
}
/*-------------------------------------------------------------------*/	
/*-----------------------------РАБОТА С СООБЩЕНИЯМИ-----------------------------------*/

function addMessages(messages){
	messages.forEach(function(message, i) {
		addMessage(message)
	});
}



function addMessage(message){
	//debugger;
	try{
		document.getElementById("tg_"+message.msgId).remove();
	}catch(e){}
	var msg="";
	if(message.msgFrom!="client"){
		var c="tg-right";
		var n=telegram.you;
	}else{
		var c="";
		if(message.managerName!=null){
		//	var n=message.managerName;
		var n=telegram.chatId;	
		
		}else{
			var n="Менеджер Planum";	
		}
	}
	msg+="<div class=\"tg-msg "+c+"\" id=\"tg_"+message.msgId+"\">";
	msg+="<div class=\"tg-msg-header\">"+n+" <span class=\"tg-time\">"+message.msgTime+"<span></div>";
	if(message.msgText==null){
		message.msgText=""
	}
 	try{
		var msgJSON = JSON.parse(message.msgText);
		//if(msgJSON=="exit"){continue;}
		//console.log("msgJSON "+msgJSON);
		
		if(msgJSON.file && msgJSON.filename){
			message.msgText="<a href=\""+telegram.apiUrl+"?act=getDocument&fileId="+msgJSON.file+"&filename="+encodeURIComponent(msgJSON.filename)+"\" target=\"_blank\" class=\"tg-file\" id=\"file_"+message.msgId+"\">"+msgJSON.filename+"</a>";
		}else if(msgJSON.photo){
			message.msgText="<div onclick=\"bigImg('img_"+message.msgId+"')\" class=\"tg-img\" id=\"image_"+message.msgId+"\"><img  id=\"img_"+message.msgId+"\" src=\""+telegram.apiUrl+"?act=getPhoto&fileId="+msgJSON.photo+"\"></div>";
		}else if(msgJSON.text){
			/* looking for links here */
			var pattern = '(?:(?:ht|f)tps?://)?(?:[\\-\\w]+:[\\-\\w]+@)?(?:[0-9a-z][\\-0-9a-z]*[0-9a-z]\\.)+[a-z]{2,6}(?::\\d{1,5})?(?:[?/\\\\#][?!^$.(){}:|=[\\]+\\-/\\\\*;&~#@,%\\wА-Яа-я]*)?';
			var reg = new RegExp(pattern);
			message.msgText = msgJSON.text.replace(reg, function(s){
				var str = (/:\/\//.exec(s) === null ? "http://" + s : s );
				return "<a target=\"_blank\" href=\""+ str + "\">" + str /*s*/ + "</a>"; 
			});
		}
	}catch(e){}
 	msg+="<div class=\"tg-msg-body\">"+message.msgText+"</div>";
	msg+="</div>";
	document.getElementById("telegramChatBox-container").innerHTML+=msg;
	setTimeout(function(){tgScrollDown();},100);
}


/* Adding system message */
function addSystemMessage(message, msgclass){
	var msg="";
	msg+="<div class=\"tg-msg tg-system "+msgclass+"\">";
	msg+="<div class=\"tg-msg-header\"></div>";
	msg+="<div class=\"tg-msg-body\">"+message+"</div>";
	msg+="</div>";
	document.getElementById("telegramChatBox-container").innerHTML+=msg;
	tgScrollDown();
}


/* Sending message function */
function sendMessage(){
	/*debugger;*/
	var message=document.getElementById("telegramMessage").value;
	if(message!=null && message!=""){
		var xhr = new XMLHttpRequest();
		xhr.open('POST', telegram.apiUrl+"?act=sendMessage", true);
		xhr.setRequestHeader('X-AJAX', 'Y');
		var formData = new FormData();
		formData.append("message", message)
		formData.append("chatId", telegram.chatId);
		xhr.send(formData);
		setTimeout(function(){
			document.getElementById("telegramMessage").value="";
		},20);
		xhr.onreadystatechange = function(){
			if(this.readyState==4){
				if(this.status == 200){
					if(this.responseText){
						try{
							var answer = JSON.parse(this.responseText);
							if(answer.status=="ok"){
								console.log(answer);	
							}else{
								console.error("Не могу отправить сообщение...");
								console.log(answer.error);
								if(answer.error=="NO_MANAGER"){
									addSystemMessage("Ошибка: нет менеджера", "danger");
									newChat();
								}
							}
						}catch(e){
							console.error("Не могу отправить сообщение...");
							console.log(this.responseText);
						}
					}else{
						console.error("Не могу отправить сообщение...");
						console.log(this.responseText);
					}
				}else{
					console.error("Не могу отправить сообщение...");
					console.log(this);
				}
			}
		}
	}
}


/*--------------------------------------------------------------------*/

/* Прокрутка внутри чата */
function tgScrollDown(){
	var h = document.getElementById("telegramChatBox-container").scrollHeight;
	document.getElementById("telegramChatBox-container").scrollTop=h+200;
}

/* Получить куки */
function telegramGetCookie(name){
	var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}

/* Установить куки */
function telegramSetCookie(name, value, options){
	options = options || {};
	var expires = options.expires;
	if(typeof expires == "number" && expires){
		var d = new Date();
		d.setTime(d.getTime() + expires * 1000);
		expires = options.expires = d;
	}
	if(expires && expires.toUTCString){
		options.expires = expires.toUTCString();
	}
	value = encodeURIComponent(value);
	var updatedCookie = name + "=" + value;
	for (var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}
	document.cookie = updatedCookie;
	
} 
/*удаляем куки*/
function telegramDeleteChatCookie(name) {
  document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
}




		
}//end main function
