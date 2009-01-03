function Cajax()
{
  this.queue = [];
  this.data  = [];
  this.mode  = 'GET';
  this.stat_upload   = 0;
  this.stat_download = 0;
  this.stat_count    = 0;
  this.stat_time     = 0;

  this.getEngine = function() 
  {
    var engine = null;
	  var msxmlhttp = new Array("Msxml2.XMLHTTP.5.0","Msxml2.XMLHTTP.4.0","Msxml2.XMLHTTP.3.0","Msxml2.XMLHTTP","Microsoft.XMLHTTP");
  	for (var i = 0; i < msxmlhttp.length; i++) {
  		try {
  		  engine = new ActiveXObject(msxmlhttp[i]);
  		} catch (e) {
  		  engine = null;
  		}
      if (engine != null) {
        break;
      }
  	}
 			
  	if(!engine && typeof XMLHttpRequest != "undefined") engine = new XMLHttpRequest();
    if (!IE) {
      engine.startTime = getSecTime();
    } else {
      this.startTime = getSecTime();
    }
    this.stat_count++;

    return engine;
  }

  this.send = function(command,param)
  {
    var i = this.queue.length;
    var engine = this.getEngine();

    this.queue[i] = engine;
    this.data[i]  = new Object();
    this.data[i].startTime = getSecTime();

    if (this.queue[i]) {
  		this.queue[i].onreadystatechange = function() 
      {
    		if (engine.readyState != 4) {
	  			return false;
        }
        if (engine.status != 200) {
          return false;
        }
        ajax.receive(engine.responseXML,engine.responseText);
        if (!IE) {
          ajax.stat_time += getSecTime() - engine.startTime;
        } else {
          ajax.stat_time += getSecTime() - ajax.startTime;
        }
        ajax.updateStat();
        delete engine;
      }
    
      var uri = '';
      for (var j = 0;j < param.length;j++) {
        if (uri != '') {
          uri += '&';
        }      
        uri += param[j][0] + '=' + param[j][1];
      }
      
      this.stat_upload += uri.length + command.length;
      if (this.mode == 'POST') {
        this.sendPOST(i,command,uri);
      } else {
        this.sendGET(i,command,uri);
      }
    }
  }

  this.sendGET = function(i,command,param) 
  {
    this.queue[i].open('GET', 'ajax.php?command='+command+'&'+param, true);
    this.queue[i].send(null);
  }

  this.sendPOST = function(i,command,param) 
  {
    this.queue[i].open('POST', 'ajax.php?command='+command, true);
		this.queue[i].setRequestHeader("Method", "POST ajax.php HTTP/1.1");
		this.queue[i].setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    this.queue[i].send(param);
  }

  this.updateStat = function()
  {
    element = document.getElementById('ajaxStatDownload');
    if (element) {
      element.innerHTML = getMySize(this.stat_download);
    }
    element = document.getElementById('ajaxStatUpload');
    if (element) {
      element.innerHTML = getMySize(this.stat_upload);
    }
    element = document.getElementById('ajaxStatTime');
    if (element) {
      element.innerHTML = getMyChrono(this.stat_time/this.stat_count)+'/'+getMyChrono(this.stat_time);
    }
  }

  this.receive = function(XML, TEXT)
  {
    this.stat_download += TEXT.length;
    if (!XML) {
      alert(TEXT);
      return false;
    }

    var xmlDoc  = XML.documentElement;
    if (xmlDoc.tagName != 'RESPONSE') {
      return false;
    }

    var info = null;
    var data = null;
    for (var i=0;i<xmlDoc.childNodes.length;i++) {
      var child = xmlDoc.childNodes[i];
      if (child.tagName == 'INFO') {
        info = child;
      } else
      if (child.tagName == 'DATA') {
        data = child;
      }
    }
    var command = info.getElementsByTagName("COMMAND");
    if (command.length<=0) {
      return false;
    }    

    command = command[0].firstChild.nodeValue.trim().ucfirst();

    eval('this.com'+command+'(info,data);');
    return true;
  }
}

Cajax.prototype.autobuild = function(node,object,childarray)
{
  for (var i=0;i<node.childNodes.length;i++) {
    var element = node.childNodes[i];
    if(!element.firstChild) {
      continue;
    }
    if(!element.firstChild.nodeValue) {
      for (var j=0;j<element.childNodes.length;j++) {
        var child = element.childNodes[j];
        var myvar = element.tagName.toLowerCase()+'_'+child.tagName.toLowerCase();
        if (eval('object.'+myvar)) {
          if (eval('typeof(object.'+myvar+') != "object"')) {
            eval('var tmp = object.'+myvar+';object.'+myvar+' = new Array();object.'+myvar+'[0] = tmp;');
          }
          var id = eval('object.'+myvar+'.length');
          myvar += '['+id+']';
        }
        if (child.firstChild) {
          eval('object.'+myvar+' = child.firstChild.nodeValue;');
        }
      }
    } else {
      var myvar = element.tagName.toLowerCase();
      eval('object.'+myvar+' = element.firstChild.nodeValue;');
    }
  }

  return object;
}
