//*******************************************************************************
//Title:      FCP Combo-Chromatic Color Picker
//URL:        http://www.free-color-picker.com
//Product No. FCP201a
//Version:    1.2
//Date:       10/01/2006
//Modified:   Clark (http://www.myupb.com)
//Mod Date:   02/06/2009
//NOTE:       Permission given to use this script in ANY kind of applications IF
//            script code remains UNCHANGED and the anchor tag "powered by FCP"
//            remains valid and visible to the user.
//
//  Call:     showColorGrid3("input_field_id","span_id")
//  Add:      <DIV ID="COLORPICKER301" CLASS="COLORPICKER301"></DIV> anywhere in body
//*******************************************************************************


function getScrollY()
{
  var scrOfX = 0,scrOfY=0;
  if(typeof(window.pageYOffset)=='number')
  {
    scrOfY=window.pageYOffset;
    scrOfX=window.pageXOffset;
  }
  else if(document.body&&(document.body.scrollLeft||document.body.scrollTop))
  {
    scrOfY=document.body.scrollTop;scrOfX=document.body.scrollLeft;
  }
  else if(document.documentElement&&(document.documentElement.scrollLeft||document.documentElement.scrollTop))
  {
    scrOfY=document.documentElement.scrollTop;scrOfX=document.documentElement.scrollLeft;
  }
  return scrOfY;
}

  document.write("<style>.colorpicker301{text-align:center;visibility:hidden;display:none;position:absolute;background-color:#FFF;border:solid 1px #CCC;padding:4px;z-index:999;filter:progid:DXImageTransform.Microsoft.Shadow(color=#D0D0D0,direction=135);}.o5582brd{border-bott6om:solid 1px #DFDFDF;border-right:solid 1px #DFDFDF;padding:0;width:12px;height:14px;}a.o5582n66,.o5582n66,.o5582n66a{font-family:arial,tahoma,sans-serif;text-decoration:underline;font-size:9px;color:#666;border:none;}.o5582n66,.o5582n66a{text-align:center;text-decoration:none;}a:hover.o5582n66{text-decoration:none;color:#FFA500;cursor:pointer;}.a01p3{padding:1px 4px 1px 2px;background:whitesmoke;border:solid 1px #DFDFDF;}</style>");





function gett6op6()
{
  csBrHt=0;
  if(typeof(window.innerWidth)=='number')
  {
  csBrHt=window.innerHeight;}
  else if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight))
  {
    csBrHt=document.documentElement.clientHeight;
  }
  else if(document.body&&(document.body.clientWidth||document.body.clientHeight))
  {
    csBrHt=document.body.clientHeight;
  }
  ctop=((csBrHt/2)-132)+getScrollY();

  return ctop;
}

function getLeft6()
{
  var csBrWt=0;
  if(typeof(window.innerWidth)=='number')
  {
    csBrWt=window.innerWidth;
  }
  else if(document.documentElement&&(document.documentElement.clientWidth||document.documentElement.clientHeight))
  {
    csBrWt=document.documentElement.clientWidth;
  }
  else if(document.body&&(document.body.clientWidth||document.body.clientHeight))
  {
  csBrWt=document.body.clientWidth;
  }
  cleft=(csBrWt/2)-125;
  return cleft;
}

var clos1="&#67;&#76;&#79;&#83;&#69;";
var tt6="&#70;&#82;&#69;&#69;&#45;&#67;&#79;&#76;&#79;&#82;&#45;&#80;&#73;&#67;&#75;&#69;&#82;&#46;&#67;&#79;&#77;";
var hm6="&#104;&#116;&#116;&#112;&#58;&#47;&#47;&#119;&#119;&#119;&#46;";
hm6+=tt6;
tt6="&#80;&#79;&#87;&#69;&#82;&#69;&#68;&#32;&#66;&#89;&#32;&#70;&#67;&#80;";

function setCCbldID6(objID,val)
{
  //document.getElementById(objID).value=val;

  if (val != "")
    createBBtag('[color='+val+']','[/color]',objID);
}

function setCCbldSty6(objID,prop,val)
{
  switch(prop)
  {
    case "bc":
      if(objID!='none')
      {
        document.getElementById(objID).style.backgroundColor=val;
      }
      break;

    case "vs":
      document.getElementById(objID).style.visibility=val;
      break;

    case "ds":
      document.getElementById(objID).style.display=val;
      break;

    case "tp":
      document.getElementById(objID).style.top=val;
      break;

    case "lf":
      document.getElementById(objID).style.left=val;
      break;
  }
}

function putOBJxColor6(OBjElem,Samp,pigMent)
{
  if(pigMent!='x')
  {
    setCCbldID6(OBjElem,pigMent);
    setCCbldSty6(Samp,'bc',pigMent);
  }

  setCCbldSty6('colorpicker301','vs','hidden');
  setCCbldSty6('colorpicker301','ds','none');
}

function showColorGrid3(OBjElem,Sam)
{
  var objX=new Array('00','33','66','99','CC','FF');
  var objXcolors = new Array("800000","8B4513","006400","2F4F4F","000080","4B0082","800080","000000","FF0000","DAA520","6B8E23","708090","0000CD","483D8B","C71585","696969","FF4500","FFA500","808000","4682B4","1E90FF","9400D3","FF1493","A9A9A9","FF6347","FFD700","32CD32","87CEEB","00BFFF","9370DB","FF69B4","DCDCDC","FFDAB9","FFFF00","98FB98","E0FFFF","87CEFA","E6E6FA","DDA0DD","FFFFFF");

  var c=0;
  var z='"'+OBjElem+'","'+Sam+'",""';
  var xl='"'+OBjElem+'","'+Sam+'","x"';
  var mid='';
  mid+='<center><table class="area_1" border="0" cellpadding="2" cellspacing="2" style="border:solid 1px #F0F0F0;padding:2px;"><tr>';
  mid+="<td class='area_2' colspan='8' align='right' style='margin:0;padding:2px;height:14px;'><a href='"+hm6+"' style='color:#666;font-size:8px;font-family:arial;text-decoration:none;lett6er-spacing:1px;'>"+tt6+"</a></td></tr><tr>";

  var br=1;

//START MY CODE
  for (i = 0;i<40;i++)
  {
    if (i%8 == 0)
      mid+='</tr><tr>';
    var grid='';
    grid = objXcolors[i];
    var b="'"+OBjElem+"', '"+Sam+"','#"+grid+"'";
    mid+='<td class="o5582brd" style="background-color:#'+grid+'"><a class="o5582n66"  href="javascript:onclick=putOBJxColor6('+b+');" onmouseover=javascript:document.getElementById("o5582n66a").style.backgroundColor="#'+grid+'";  alt="" title=""><div style="width:12px;height:14px;"></div></a></td>';
  }
  
//END MY CODE
  mid+='</tr>';

  mid+="<tr><td colspan='8' class='area_2' align='right' style='margin:0;padding:2px;height:14px;' ><a class='o5582n66' href='javascript:onclick=putOBJxColor6("+xl+")'><span style='text-decoration:none;'>"+clos1+"</span></a></td>";
  
  mid+='</tr>'
  mid+='</table></center>';
  setCCbldSty6('colorpicker301','tp','500px');
  document.getElementById('colorpicker301').style.top=gett6op6();
  document.getElementById('colorpicker301').style.left=getLeft6();
  setCCbldSty6('colorpicker301','vs','visible');setCCbldSty6('colorpicker301','ds','block');
  document.getElementById('colorpicker301').innerHTML=mid;
}