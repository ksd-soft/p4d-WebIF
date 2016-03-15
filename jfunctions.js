/*
JavaScript-File for the p4d Web-InterFace
Copyright by Stefan Doering and Joerg Wendel
used for different functions in all pages
please do not modify unless you know what you are doing!
*/

function confirmSubmit(msg)
{
   if (confirm(msg))
      return true;

   return false;
} 

function showContent(elm){
		if (document.getElementById(elm).style.display == "block") {document.getElementById(elm).style.display = "none"} else {document.getElementById(elm).style.display = "block"}
}

function readonlyContent(elm, chk)
{
  var elm = document.querySelectorAll('[id*=' + elm + ']');  var i;
	if (chk.checked == 1){ 
		for (i = 0; i < elm.length; i++) {
			elm[i].readOnly = false;
			elm[i].style.backgroundColor = "#fff"; 
		}
	}else{
		for (i = 0; i < elm.length; i++) {
			elm[i].readOnly = true;
			elm[i].style.backgroundColor = "#ddd"; 
		} 
	}
}

function disableContent(elm, chk)
{
  var elm = document.querySelectorAll('[id*=' + elm + ']');  var i;
	if (chk.checked == 1){ 
		for (i = 0; i < elm.length; i++) {
			elm[i].disabled = false;
		}
	}else{
		for (i = 0; i < elm.length; i++) {
			elm[i].disabled = true;
		} 
	}
}
   
function displayCoords(elmX,elmY,e)
{
if (document.getElementById(elmX) != null)
{
	document.getElementById(elmX).value = e.offsetX;
  document.getElementById(elmY).value = e.offsetY;
}
/*	if(!e) e = window.event;
	var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? window.document.documentElement : window.document.body;
	xpos = e.pageX ? e.pageX : e.clientX + body.scrollLeft  - body.clientLeft
	ypos = e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
  txt = "Xpos="+(xpos-8)+"; Ypos="+(ypos-285)+"  ";
  document.getElementById(elmX).value = xpos;
  document.getElementById(elmY).value = ypos;
*/
}

function colorSelect(elm) {
	var dummy = document.getElementById(elm),
	color = dummy.options[dummy.selectedIndex].value;
	dummy.className = color;
	dummy.blur(); 

}

function getEventOffsetXY(evt)
{
    if (evt.offsetX != null)
        return [evt.offsetX, evt.offsetY];
       
    var  top = 0, left = 0, o = evt.target || evt.srcElement;
    while (o.offsetParent)
     {
         left += o.offsetLeft ;
         top += o.offsetTop ;
         o = o.offsetParent ;
    };
    return [(evt.clientX - left), (evt.clientY - top)];
};
 
