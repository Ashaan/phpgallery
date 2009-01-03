function setDisplay(id,value){
  document.getElementById(id).style.display = value;
}

function getElement(id) {
  if (document.getElementById) {
    return document.getElementById(id);
  } else if (document.all) {
    return document.all[id];
  }
}

function setClass(id,nclass){
  id.className = nclass; 
}

function setVisibility(element,mode)
{
  var style;
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

function getSecTime()
{
  var time = new Date();
  var str  = time.getMilliseconds() + time.getSeconds()*1000 + time.getMinutes()*1000*60 + time.getHours()*1000*60*60;
  return str;
}

function getMySize(size)
{
  if (size>1024*1024*1024) {
    return (Math.floor(size/(1024*1024*10,24))/100)+'Go';
  } else 
  if (size>1024*1024) {
    return (Math.floor(size/(1024*10,24))/100)+'Mo';
  } else 
  if (size>1024) {
    return (Math.floor(size/10,24)/100)+'ko';
  } else { 
    return size+'o';
  } 
}
function getMyChrono(time)
{
  if (time>1000*60*60) {
    return (Math.floor(time/(10*60*60))/100)+'h';
  } else 
  if (time>1000*60) {
    return (Math.floor(time/(10*60))/100)+'mn';
  } else 
  if (time>1000) {
    return (Math.floor(time/10)/100)+'s';
  } else { 
    return time+'ms';
  }   
}

function var_dump(arr,level) {
  var dumped_text   = "";
  var level_padding = "";
  var value;
  if (!level) level = 0;
  for(i=0;i<level+1;i++) level_padding += "    ";

  if (typeof(arr) == 'object') {
    for (var item in arr) {
      value = arr[item];
      if (typeof(value) == 'object') {
        if (level<3 && value && value.length>0) {
          dumped_text += level_padding+"'"+item+"' => Object ( \n";          
          dumped_text += var_dump(value,level+1);
          dumped_text += level_padding+")\n ";
        } else {
          if (value && value.length>0) {
            dumped_text += level_padding+"'"+item+"' => Object(truncated)\n";
          } else 
          if (value) {
            dumped_text += level_padding+"'"+item+"' => Object(empty)\n";
          } else {
            dumped_text += level_padding+"'"+item+"' => Object(null)\n";
          }
        }
      } else {
        if (typeof(value)!='function') {
          dumped_text += level_padding+"'"+item+"' => \""+value+"\"\n";
        }
      }
    }
  } else {
    dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
  }
  return dumped_text;  
}
