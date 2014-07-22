// Ultimate PHP Board Javascripts
// Author: Chris Kent aka Clark and others for Ultimate PHP Board by Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.2.2

//Following script only shows text for javascript dependent content
(function () {
	var head = document.getElementsByTagName("head")[0];
	if (head) {
		var scriptStyles = document.createElement("link");
		scriptStyles.rel = "stylesheet";
		scriptStyles.type = "text/css";
		scriptStyles.href = "skins/enabled.css";
		head.appendChild(scriptStyles);
	}
}());

//START OF BBCODE SCRIPTS
var browserType;

if (document.layers) {browserType = "nn4"}
if (document.all) {browserType = "ie"}

var clientInfo = navigator.userAgent.toLowerCase();
var isIE = ( clientInfo.indexOf("msie") != -1 );
var isWin = ( (clientInfo.indexOf("win")!=-1) || (clientInfo.indexOf("16bit") != -1) );
if (window.navigator.userAgent.toLowerCase().match("gecko")) {
 browserType= "gecko"
}


// function bb_dropdown creates the bbcode for the value selected from the dropdown
// field is the document object containing the selected value, selectname is the name of the dropdown box
function bb_dropdown(field,selectname,txtArea)
{
val = field.options[field.selectedIndex].value;

if (selectname == 'colors')
{
  document.newentry.colors.selectedIndex = 0;
  createBBtag('[color='+val+']','[/color]',txtArea);
}
if (selectname == 'typeface')
{
  document.newentry.typeface.selectedIndex = 0;
  createBBtag('[font='+val+']','[/font]',txtArea);
}
if (selectname == 'size')
{
document.newentry.size.selectedIndex = 0;
createBBtag('[size='+val+']','[/size]',txtArea);
}
}

//function createBBtag chooses the correct function for the browser to enter the BBcode tags
//openerTag is the opening tag, closerTag is the closing tag, areaId is the name of the textarea
function createBBtag( openerTag , closerTag , areaId ) {
	if(isIE && isWin) {
		createBBtag_IE( openerTag , closerTag , areaId );
	}
	else {
		createBBtag_nav( openerTag , closerTag , areaId );
	}
	return;
}

//functions createBB_tag_IE creates the BBcode for IE browsers
//parameters are the same as for createBBTag

function createBBtag_IE( openerTag , closerTag , areaId ) {
	var txtArea = document.getElementById( areaId );
	var aSelection = document.selection.createRange().text;
	var range = txtArea.createTextRange();

	if(aSelection) {
		document.selection.createRange().text = openerTag + aSelection + closerTag;
		txtArea.focus();
		range.move('textedit');
		range.select();
	}
	else {
		var oldStringLength = range.text.length + openerTag.length;
		txtArea.value += openerTag + closerTag;
		txtArea.focus();
		range.move('character',oldStringLength);
		range.collapse(false);
		range.select();
	}
	return;
}

//functions createBB_tag_nav creates the BBcode for non-IE browsers
//parameters are the same as for createBBTag

function createBBtag_nav( openerTag , closerTag , areaId ) {
	var txtArea = document.getElementById( areaId );
	var counter = 1;
  if (txtArea.selectionEnd && (txtArea.selectionEnd - txtArea.selectionStart > 0) ) {

    var preString = (txtArea.value).substring(0,txtArea.selectionStart);
		var newString = openerTag + (txtArea.value).substring(txtArea.selectionStart,txtArea.selectionEnd) + closerTag;
		var postString = (txtArea.value).substring(txtArea.selectionEnd);

    txtArea.value = preString + newString + postString;
		txtArea.focus();
	}
	else {
		var offset = txtArea.selectionStart;
		var preString = (txtArea.value).substring(0,offset);
		var newString = openerTag + closerTag;
		var postString = (txtArea.value).substring(offset);
    txtArea.value = preString + newString + postString;
		txtArea.selectionStart = offset + openerTag.length;
		txtArea.selectionEnd = offset + openerTag.length;
		txtArea.focus();
	}	
  return;
}

//This function is only used for smilies that appear under the textbox

function setsmilies(Tag,areaId) 
{
  var pos = document.getElementById(areaId).selectionStart;
  var scrollPos = document.getElementById(areaId).scrollTop
  if(document.selection) 
  {
    if( !document.getElementById(areaId).focus() )
      document.getElementById(areaId).focus();
    document.selection.createRange().text=Tag;
  }
  else 
  {
    document.getElementById(areaId).value =
    document.getElementById(areaId).value.substr(0, pos) + Tag + document.getElementById(areaId).value.substr(pos);
    document.getElementById(areaId).selectionStart = pos + Tag.length;
    document.getElementById(areaId).selectionEnd = pos + Tag.length;
  }
  document.getElementById(areaId).scrollTop = scrollPos;
}

//This function is used for smilies that appear on the 'more smilies' page

function moresmilies(Tag)
{
  var pos = opener.document.newentry.message.selectionStart;
  var scrollPos = opener.document.newentry.message.scrollTop
  if(document.selection) 
  {
    if( !opener.document.newentry.message.focus() )
      opener.document.newentry.message.focus();
    opener.document.selection.createRange().text=Tag;
  }
  else 
  {
    opener.document.newentry.message.value = opener.document.newentry.message.value.substr(0, pos) + Tag +      opener.document.newentry.message.value.substr(pos);
    opener.document.newentry.message.selectionStart = pos + Tag.length;
    opener.document.newentry.message.selectionEnd = pos + Tag.length;
  }
  opener.document.newentry.message.scrollTop = scrollPos;
  return;
}

//function add_link adds urls, images, videos or emails to the textbox
//parameters: type,areaId (type is type of link, areaId is name of textbox)
//function will eventually place the email, image link, url or embed video where the cursor is.
function add_link(type,areaId)
{
	if(isIE && isWin) {
		add_link_IE(type,areaId );
	}
	else {
		add_link_nav(type,areaId );
	}
	return;
}

function add_link_IE(type,areaId) {
	//alert(areaId)
  var link = select = url = text = '';
  var txtArea = document.getElementById( areaId );
	var aSelection = document.selection.createRange().text;
	
  var range = txtArea.createTextRange();

	if(aSelection) {
		select = aSelection;
  
    if (type == 'url' || type == 'img')
		{
      found = select.indexOf("http://")
      if (found == -1)
      {
        url = 'http://' + select;
      }
      else
        url = select;
    }
    else
      url = select;

    document.selection.createRange().text = '['+type+']'+ url+'[/'+type+']';
    txtArea.focus();
		range.move('textedit');

		return;
	}
	else 
  {
    var Tag = '['+type;
    if (type == 'email')
		{
      url = prompt('Enter the email address:','');
    }
    else if (type == 'url')
    { 
      url = prompt('Enter the url:','http://');
    }
    else if (type == 'google')
    {
      url = prompt('Enter the video code\nThis is the code after docId= in the URL of the video');
    }
    else if (type == 'youtube')
    {
      url = prompt('Enter the video code\nThis is the code after v= in the URL of the video');
    }
    else
    { 
      url = prompt('Enter the url of the image:','http://');
    }
    
    if (url.length > 0)
    {
      if (type == 'url' || type == 'img')
      {
        found = url.indexOf("http://")

        if (found == -1)
        {
          url = 'http://'+url;
        }
      }
    }
    else
      return; 
      
    if (type == "url" || type == "email")
    {
      link = prompt('Enter the link text (optional):','');
    
      if (link.length > 0)
      {
        if (type == 'email')
          Tag += '='+url+']'+link;
        else
          Tag += '='+url+']'+link;
      }
      else
      {
        Tag += "]"+url;
      }
    }
    else
    {
      Tag += "["+type+"]"+url;
    }
    
    Tag += "[/"+type+"]";
    txtArea.value += Tag;
		txtArea.focus();
		range.collapse(false);
		range.select();
    return;
  }
}

function add_link_nav(type,areaId)
{
	var link = url = text = '';
  var txtArea = document.getElementById( areaId );
	
	var closerTag = "[/"+type+"]";

  var openerTag = "["+type+"]";
  
  if (txtArea.selectionEnd && (txtArea.selectionEnd - txtArea.selectionStart > 0) ) {
    var preString = (txtArea.value).substring(0,txtArea.selectionStart);
		url = (txtArea.value).substring(txtArea.selectionStart,txtArea.selectionEnd)
    
    
    
    if (type == 'url' || type == 'img')
		{
      found = url.indexOf("http://")
      if (found == -1)
      {
        link = 'http://' + url;
      }
      else
        link = url;
    }
    else
    link = url;
    var newString = openerTag + link + closerTag;
		var postString = (txtArea.value).substring(txtArea.selectionEnd);
		txtArea.value = preString + newString + postString;
		txtArea.focus();
		return;
	}
	else 
  {
		if (type == 'email')
		  link = prompt('Enter the email address:','');
    else if (type == 'url')
      link = prompt('Enter the url:','http://');
    else if (type == 'google')
    {
      link = prompt('Enter the video code\nThis is the code after docId= in the URL of the video');
    }
    else if (type == 'youtube')
    {
      link = prompt('Enter the video code\nThis is the code after v= in the URL of the video');
    }
    else
      link = prompt('Enter the url of the image:','http://');
    
    if (link.length > 0)
    {
      if (type == 'url' || type == 'img')
      {
        found = link.indexOf("http://")

        if (found == -1)
        {
          url = 'http://'+link;
        }
        else
          url = link;
        
        link = url;
      }
    } 
    else
      return;
    
    var open = '['+type;
    if (type == 'url' || type == 'email')
    {
      linktext = prompt('Enter the link text (optional):','');
      
      if (linktext.length > 0)
        open += '='+link+"]"+linktext;
      else
        open += ']'+link;
    }
    else
    {
      open += ']'+link;
      
    } 
    open += '[/'+type+']';
  }
    
    
    var offset = txtArea.selectionStart;
		var preString = (txtArea.value).substring(0,offset);
		var postString = (txtArea.value).substring(offset);
		txtArea.value = preString + open + postString;
		txtArea.selectionStart = offset + openerTag.length;
		txtArea.selectionEnd = offset + openerTag.length;
		txtArea.focus();
	  return;
}

function add_list(type,areaId)
{
	if(isIE && isWin) {
		add_list_IE(type,areaId );
	}
	else {
		add_list_nav(type,areaId );
	}
	return;
}

function add_list_nav(type,areaId)
{
  var txtArea = document.getElementById(areaId);
  var offset = txtArea.selectionStart;
  var minus = 0;
  var closerTag = "[/"+type+"]";

  var openerTag = "["+type+"]";

  minus +=1
  
  var items = new Array();
  var itemString = "";
  var x;
  
	while (item = prompt('Enter an item\r\nLeave the box empty or click cancel to complete the list',''))
	 items.push("[*]"+item+"[/*]");
	
	itemString = items.join('');
  itemsize = items.length;
  
  minus += itemsize;
	
  var preString = (txtArea.value).substring(0,offset);
	var newString = openerTag + itemString + closerTag;
	var postString = (txtArea.value).substring(offset);
	txtArea.value = preString + newString + postString;
	txtArea.selectionStart = offset + newString.length - minus;
	txtArea.selectionEnd = offset + newString.length - minus;
	txtArea.focus();
  return;
}

function add_list_IE(type,areaId)
{
  var txtArea = document.getElementById(areaId);
	var aSelection = document.selection.createRange().text;
	var range = txtArea.createTextRange();

	var minus = 0;
  var closerTag = "[/"+type+"]";

  var openerTag = "["+type+"]";

  minus +=1
  
  var items = new Array();
  var itemString = "";
  var item;
  
  while (item = prompt('Enter an item\r\nLeave the box empty or click cancel to complete the list',''))
	 items.push("[*]"+item+"[/*]");
	
	itemString = items.join('');
  itemsize = items.length;
  
  minus += itemsize;
  
  Tag = openerTag + itemString + closerTag;
  
	var oldStringLength = range.text.length + Tag.length - minus;
	txtArea.value += Tag;
	txtArea.focus();
	range.move('character',oldStringLength);
	range.collapse(false);
	range.select();
	
	return;
}

//END OF BBCODE SCRIPTS

//START OF FORM SCRIPTS

var ns6=document.getElementById&&!document.all

function restrictinput(maxlength,e,placeholder){
if (window.event&&event.srcElement.value.length>=maxlength)
return false
else if (e.target&&e.target==eval(placeholder)&&e.target.value.length>=maxlength){
var pressedkey=/[a-zA-Z0-9\.\,\/]/ //detect alphanumeric keys
if (pressedkey.test(String.fromCharCode(e.which)))
e.stopPropagation()
}
}

function countlimit(maxlength,e,placeholder){
var theform=eval(placeholder)
var lengthleft=maxlength-theform.value.length
var placeholderobj=document.all? document.all[placeholder] : document.getElementById(placeholder)
if (window.event||e.target&&e.target==eval(placeholder)){
if (lengthleft<0)
theform.value=theform.value.substring(0,maxlength)
placeholderobj.innerHTML=lengthleft
}
}


function displaylimit(theform,thelimit){
var limit_text='<b><span id=\"'+theform.toString()+'\">'+thelimit+'</span></b> characters remaining on your input limit'
if (document.all||ns6)
document.write(limit_text)
if (document.all){
eval(theform).onkeypress=function(){ return restrictinput(thelimit,event,theform)}
eval(theform).onkeyup=function(){ countlimit(thelimit,event,theform)}
}
else if (ns6){
document.body.addEventListener('keypress', function(event) { restrictinput(thelimit,event,theform) }, true);
document.body.addEventListener('keyup', function(event) { countlimit(thelimit,event,theform) }, true);
}
}

var counter=0;
function check_submit()
{
counter++;
if (counter>1)
{
alert('You cannot submit the form again! Please Wait.');
return false;
}
}

function validate_reply()
{
  if (trim(document.newentry.message.value) == "") {
    document.getElementById('msg_err').innerHTML = "^^^ You need to enter a message";
    return false;
  }
  document.newentry.submit.disabled = true;
  return true;
}

function validate_topic()
{
  if (trim(document.newentry.subject.value) == "" || trim(document.newentry.message.value) == "")
  {
    if (trim(document.newentry.subject.value) == "") {
      document.getElementById('sub_err').innerHTML = "<-- You need to enter a subject";
    }
    if (trim(document.newentry.message.value) == "") {
    document.getElementById('msg_err').innerHTML = "^^^ You need to enter a message";
    }
    return false;
  }
  document.newentry.submit.disabled = true;
  return true;
}
//END OF FORM SCRIPTS

//START OF AJAX SCRIPTS

var div="";
var what="";
var Utf8 = {

	// public method for url encoding
	encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},

	// public method for url decoding
	decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}

		return string;
	}

}

var http_request = false;
   
   function makePOSTRequest(url, parameters,type){
      http_request = false;

      //select type of request according to browser
      
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }
      
      if (type == 'edit')
        http_request.onreadystatechange = EditContents;
      else if (type == 'getpost')
        http_request.onreadystatechange = GetPost;
      else if (type == 'reply')
        http_request.onreadystatechange = ReplyContents;
      else if (type == 'sig')
        http_request.onreadystatechange = Sig;
      else if (type == 'sort')
        http_request.onreadystatechange = SortForums;
      else if (type == 'username')
        http_request.onreadystatechange = CheckUsername;
      else if (type == 'emailcheck')
        http_request.onreadystatechange = EmailCheck;
      else if (type == 'emailvalid')
        http_request.onreadystatechange = EmailValid;
      else if (type == 'delfile')
        http_request.onreadystatechange = DelFile;
      else if (type == 'preview')
        http_request.onreadystatechange = PreviewPost;
      else
        http_request.onreadystatechange = Error;
      http_request.open('POST', url, true);
      http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_request.setRequestHeader("Content-length", parameters.length);
      http_request.setRequestHeader("Content-disposition",'form-data; name='+type)
      http_request.setRequestHeader("Connection", "close");
      http_request.send(parameters);
   }

   function Sig()
   {
      if (http_request.readyState == 3) {
      document.getElementById('sig_preview').innerHTML = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>";
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            result_array = result.split("<!--divider-->");
            document.getElementById('sig_preview').innerHTML = result_array[0];
            document.getElementById('sig_title').innerHTML = result_array[1];       
         } else {
            alert(http_request.status)
            alert('Error');
         }
      }
   }
   
   function Error()
   {
    alert('An error has occured');
   }
   
   function SortForums() {
    if (http_request.readyState == 3) {
      if (what == 'forum')
        waitwhat = 'Forums';
      else
        waitwhat = 'Categories';
      html = "<div class='main_cat_wrapper'><div class='cat_area_1'>Quick Reply</div><table class='main_table'><tbody><td class='area_2' style='text-align:center'><img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>&nbsp;<strong>Sorting "+waitwhat+"</strong></td></tr></tbody></table><div class='footer'></div></div>";
      document.getElementById(div).innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            
            document.getElementById(div).innerHTML = result;       
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function GetPost() {
      if (http_request.readyState == 3)
        document.getElementById(div).innerHTML = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>&nbsp;Getting Post from Database....Please Wait";
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            
            document.getElementById(div).innerHTML = result;       
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function EditContents() {
      if (http_request.readyState == 3)
        document.getElementById(div).innerHTML = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>&nbsp;Editing Post....Please Wait";
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            
            result_array = result.split("<!--divider-->");
            var editdiv = "edit"+div;
            document.getElementById(div).innerHTML = result_array[0]; 
            
            document.getElementById(editdiv).innerHTML = result_array[1];       
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function ReplyContents() {
      
      if (http_request.readyState == 3)
      {
        html = "<div class='main_cat_wrapper'><div class='cat_area_1'>Quick Reply</div><table class='main_table'><tbody><td class='area_2' style='text-align:center'><img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>&nbsp;<strong>Adding Quick Reply....Please Wait</strong></td></tr></tbody></table><div class='footer'></div></div>";
        document.getElementById('quickreplyform').innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            
            result_array = result.split("<!--divider-->");
            document.getElementById('current_posts').innerHTML = result_array[0];
            
            document.getElementById('pagelink1').innerHTML = result_array[1];
            document.getElementById('pagelink2').innerHTML = result_array[2];
            document.getElementById('quickreplyform').innerHTML = result_array[3];
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function CheckUsername() {
      
      if (http_request.readyState == 3)
      {
        html = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>Checking Username";
        document.getElementById('namecheck').innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            result_array = result.split("<!--divider-->");
            if (result_array[0] == "false")
              document.getElementById('submit').disabled = true;
            else
              document.getElementById('submit').disabled = false;
            document.getElementById('namecheck').innerHTML = result_array[1];
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function EmailValid() {
      
      if (http_request.readyState == 3)
      {
        html = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>Checking Email Address";
        document.getElementById('emailvalid').innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            result_array = result.split("<!--divider-->");
            if (result_array[0] == "false")
              document.getElementById('submit').disabled = true;
            else
              document.getElementById('submit').disabled = false;
            document.getElementById('emailvalid').innerHTML = result_array[1];
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function EmailCheck() {
      
      if (http_request.readyState == 3)
      {
        html = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>Checking Email Address";
        document.getElementById('emailcheck').innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            result_array = result.split("<!--divider-->");
            if (result_array[0] == "false")
              document.getElementById('submit').disabled = true;
            else
              document.getElementById('submit').disabled = false;
            document.getElementById('emailcheck').innerHTML = result_array[1];
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function DelFile() {
      if (http_request.readyState == 3)
      {
        html = "<img src='images/spinner.gif' alt='' title='' style='vertical-align: middle;'>Deleting File";
        document.getElementById(div).innerHTML = html;
      }
      if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            result_array = result.split("<!--divider-->");
            document.getElementById(div).innerHTML = result_array[0];
            editdiv = 'edit'+div.replace('-attach','');
            document.getElementById(editdiv).innerHTML = result_array[1];
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }

   function PreviewPost() {
    if (http_request.readyState == 3)
    {
      html = "<img src='images/spinner.gif' alt='' title='' style='vertical-align:middle;'>Creating Post Preview";
      document.getElementById('preview').innerHTML = html;
    }
    if (http_request.readyState == 4) {
         if (http_request.status == 200) {
            result = http_request.responseText;
            document.getElementById('preview').innerHTML = result;
         } else {
            alert(http_request.status)
            alert('There was a problem with the request.');
         }
      }
   }
   
   function getEdit(obj,divname) {
      div = divname;
      var poststr = "newedit=" + escape(Utf8.encode( replaceSubstring(document.getElementById("newedit").value,"+","&#43;")));
      poststr += "&forumid="+escape(Utf8.encode(document.getElementById("forumid").value));
      poststr += "&userid="+escape(Utf8.encode( document.getElementById("userid").value ));
      poststr += "&threadid="+escape(Utf8.encode( document.getElementById("threadid").value ));
      poststr += "&postid="+escape(Utf8.encode( document.getElementById("postid").value ));
      poststr += "&type=edit";
      
      makePOSTRequest('./ajax.php', poststr,'edit');     
   }
   
   function getReply(obj) {
      if (document.getElementById("newentry").value == "")
      {
        alert("Error: Empty Post");
        document.quickreplyfm.quickreply.disabled = false;
      }
      else
      {
      document.quickreplyfm.quickreply.value = "Adding Quick Reply...";
      var poststr = "id="+escape(Utf8.encode( document.getElementById("id").value));
      poststr += "&t_id="+escape(Utf8.encode( document.getElementById("t_id").value));
      poststr += "&page="+escape(Utf8.encode( document.getElementById("page").value));
      poststr += "&user_id="+escape(Utf8.encode( document.getElementById("user_id").value));
      poststr += "&icon="+escape(Utf8.encode( document.getElementById("icon").value));
      poststr += "&newentry=" + escape(Utf8.encode( replaceSubstring(document.getElementById("newentry").value,"+","&#43;")));
      poststr += "&username="+escape(Utf8.encode( document.getElementById("username").value));
      poststr += "&type=reply";
      
      makePOSTRequest('./ajax.php', poststr,'reply');
      }
   }
    
   function getPost(userid,divname,method)
   {  
      div = divname;
      splitstring = divname.split("-");
      var poststr = "forumid="+escape(Utf8.encode(splitstring[0]));
      poststr += "&postid="+escape(Utf8.encode(splitstring[2]));
      poststr += "&userid="+escape(Utf8.encode(userid));
      poststr += "&threadid="+escape(Utf8.encode(splitstring[1]));
      poststr += "&divname="+escape(Utf8.encode(divname));
      poststr += "&method="+escape(Utf8.encode(method));
      poststr += "&type=getpost";

      makePOSTRequest('./ajax.php', poststr,'getpost');  
   }
   
   function forumSort(type,where,id)
   {
      div = 'sorting';
      if (type == "forum")
        what = 'forum';
      else
        what = 'cat';
      var poststr = "what="+escape(Utf8.encode(type));
      poststr += "&where="+escape(Utf8.encode(where));
      poststr += "&id="+escape(Utf8.encode(id));
      poststr += "&divname=sorting";
      poststr += "&type=sort";
      
      makePOSTRequest('./ajax.php', poststr,'sort');

   }
   
    function sigPreview(obj,id,status)
    {
    var poststr = "sig="+escape(Utf8.encode(replaceSubstring(document.getElementById("u_sig").value,"+","&#43;")));
    poststr += "&id="+escape(Utf8.encode(id));
    poststr += "&status="+escape(Utf8.encode(status));
    poststr += "&type=sig";
    
    makePOSTRequest('./ajax.php', poststr,'sig');  
    }
    
    function postPreview(obj)
    {
      var poststr = "message="+escape(Utf8.encode(document.getElementById("look1").value));
      poststr += "&type=preview";
      makePOSTRequest('./ajax.php',poststr,'preview');
    }
    
    function getUsername(username,area)
    {
      var poststr = 'username='+escape(Utf8.encode(username));
      poststr += '&type=username';
      poststr += '&area='+escape(Utf8.encode(area));
      makePOSTRequest('./ajax.php',poststr,'username');
    }
    
    function ValidEmail(email)
    {
      var poststr = 'email='+escape(Utf8.encode(email));
      poststr += '&type=emailvalid';
      makePOSTRequest('./ajax.php',poststr,'emailvalid');
    }
    
    function CheckEmail(email1,email2)
    {
      var poststr = 'email1='+escape(Utf8.encode(email1));
      poststr += '&email2='+escape(Utf8.encode(email2));
      poststr += '&type=emailcheck';
      makePOSTRequest('./ajax.php',poststr,'emailcheck');
    }
    
    function deleteFile(fId,tId,pId,fileId,filename,userid,divname)
    {
      answer = confirm('Are you sure you want to delete '+filename+'?');
      if (answer)
      {
        div = divname;
        var poststr = "forumid="+escape(fId);
        poststr += "&postid="+escape(pId);
        poststr += "&userid="+escape(userid);
        poststr += "&threadid="+escape(tId);
        poststr += "&divname="+escape(divname);
        poststr += "&fileid="+escape(fileId);
        poststr += "&filename="+escape(Utf8.encode(filename));
        poststr += '&type=delfile';

        makePOSTRequest('./ajax.php',poststr,'delfile');
      }
    }
    
    function changeCaptcha()
    {
      div = 'captcha';
      poststr = 'type=captcha';
      makePOSTRequest('./ajax.php',poststr,'captcha');
    }
//END OF AJAX SCRIPTS

//START OF MISCELLANEOUS SCRIPTS

function swap(source) {
    if (document.images) {
        document.images['myImage'].src = source;
    }
}

function switchElementDisable(field1, field2) {
            if(field1.value != '') {
		            field2.disabled = true;
		        } else field2.disabled = false;
		    }

function PopUp(where) {
window.open("where", "This PM has been Recieved Within the Last 5 Minutes", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,width=500,height=350");
}

function substr_count( haystack, needle, offset, length ) {
 
    var pos = 0, cnt = 0;
 
    if(isNaN(offset)) offset = 0;
    if(isNaN(length)) length = 0;
    offset--;
 
    while( (offset = haystack.indexOf(needle, offset+1)) != -1 ){
        if(length > 0 && (offset+needle.length) > length){
            return false;
        } else{
            cnt++;
        }
    }
 
    return cnt;
}

//removes all bbcode from post boxes

function removeBBcode(areaId) {
   var text1 = new String("");
   text1 = document.getElementById( areaId ).value;
   var pattern = new RegExp(/\[[^\]]*\]/g);
   var text2 = new String(document.getElementById( areaId ).value.replace(pattern,""));
   document.getElementById( areaId ).value = text2;
}

function submitonce(theform){
		if (document.all||document.getElementById){
		for (i=0;i<theform.length;i++){
		var tempobj=theform.elements[i]
		if (tempobj.type.toLowerCase()=='submit'||tempobj.type.toLowerCase()=='reset')
		tempobj.disabled=true
		}
		}
		}
		
    
function openChild(file,window) {
		childWindow=open(file,window,'resizable=no,width=400,height=200');
		if (childWindow.opener == null) childWindow.opener = self;
		}

function changeCheckboxValue(checked, object) {
                    if(checked) {
                        object.value = '1';
                    } else {
                        object.value = '0';
                    }
                }

function replaceSubstring(inputString, fromString, toString) {
      var temp = inputString;
   if (fromString == "") {
      return inputString;
   }
   if (toString.indexOf(fromString) == -1) { 
      while (temp.indexOf(fromString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(fromString));
         var toTheRight = temp.substring(temp.indexOf(fromString)+fromString.length, temp.length);
         temp = toTheLeft + toString + toTheRight;
      }
   } else { 
      var midStrings = new Array("~", "`", "_", "^", "#");
      var midStringLen = 1;
      var midString = "";

      while (midString == "") {
         for (var i=0; i < midStrings.length; i++) {
            var tempMidString = "";
            for (var j=0; j < midStringLen; j++) { tempMidString += midStrings[i]; }
            if (fromString.indexOf(tempMidString) == -1) {
               midString = tempMidString;
               i = midStrings.length + 1;
            }
         }
      }
      while (temp.indexOf(fromString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(fromString));
         var toTheRight = temp.substring(temp.indexOf(fromString)+fromString.length, temp.length);
         temp = toTheLeft + midString + toTheRight;
      }
      while (temp.indexOf(midString) != -1) {
         var toTheLeft = temp.substring(0, temp.indexOf(midString));
         var toTheRight = temp.substring(temp.indexOf(midString)+midString.length, temp.length);
         temp = toTheLeft + toString + toTheRight;
      }
   } 
   return temp; 
} 

function trim (str) {
	str = str.replace(/^\s+/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	return str;
}

function popDiv()
{
  mid = "Is this thing working????";
  alert(mid);
  document.getElementById('colorpicker301').innerHTML=mid;
}

function showhide(divname,linkdiv) {
  if (browserType == "gecko" )
     document.poppedLayer =
         eval('document.getElementById("'+divname+'")');
  else if (browserType == "ie")
     document.poppedLayer =
        eval('document.getElementById("'+divname+'")');
  else
     document.poppedLayer =
        eval('document.layers["'+divname+'"]');

  if (document.poppedLayer.style.display == "none")
  {
    document.poppedLayer.style.display = "inline";
    document.getElementById(linkdiv).innerHTML = "<img src='images/up.gif' alt='Hide Search Box' title='Hide Search Box' onClick=\"showhide('searchbox','showhidebuttons');\">";
  }
  else
  {
    document.poppedLayer.style.display = "none";
    document.getElementById(linkdiv).innerHTML = "<img src='images/down.gif' alt='Show Search Box' title='Show Search Box' onClick=\"showhide('searchbox','showhidebuttons');\">";
  }
}




//END OF MISCELLANEOUS SCRIPTS
