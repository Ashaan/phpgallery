
function setVisibility(element,mode)
{
  if (element.style) {
    style = element.style;
  } else
  if (document.getElementById(element)) {
    style = document.getElementById(element).style
  } else {
    return false;
  }

  if (mode) {
    style.display = mode;
  } else
  if (style.display == 'none') {
    style.display = 'block';
  } else {
    style.display = 'none';
  } 

  return true;
}
function switchClass(element,class1,class2)
{
  if (!element.style) {
    if (document.getElementById(element)) {
      element = document.getElementById(element);
    } else {
      return false;
    }
  }

  if(element.className == class1) {
    element.className = class2;
  } else {
    element.className = class1;
  }
}

function serialize(a)
{
  var a_php = '';
  var total = 0;
  for (var key in a) {
    ++total;
    aphp = a_php + 's:' +
           String(key).length + ":\"" + String(key) + "\";s" +
           String(a[key]).length + ":\"" + String(a[key]) + "\";";
  }
  a_php = "a:" + total + ":{" + a_php + "}";
  return a_php;
}

function base64_encode(str)
{
  var charmap = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  var ret		  = "";
  var c, i, acc = 0;
  var div	      = 1;
  
  for (i=0,c=0;i<str.length;i++,c++) {
    acc = acc*256 + str.charCodeAt(i);
    div = div*4;
    ret = ret + base64.charmap.charAt(parseInt(acc/div));
    acc = acc % div;
    if (div==64) {
      ret	= ret + base64.charmap.charAt(parseInt(acc)), acc = 0, div = 1,c++;
    }
    if (c>=75) {
      c=-1, ret = ret + "\n";
    }
  }
  if (i%3) {
    ret = ret + base64.charmap.charAt(parseInt(acc*((i%3==1)?16:4)));
    ret = ret + ((i%3)==1?"==":"=");
  }
  return ret;
}
