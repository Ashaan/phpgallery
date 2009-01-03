function Cimage(id,pid,title,type,width,height,duration) 
{
  this.id     = id;
  this.pid    = pid;
  this.title  = title;
  this.type   = type;
  this.width  = width;
  this.height = height;
  this.size   = '';
  this.duration   = duration;
  this.mime_group = '';
  this.mime_type  = '';
  this.tested = false;

  this.draw = function() 
  {
    var html = tpl.tplImageList;
    html = html.replace(/T_classElement_T/ig  ,tpl.classListElement);
    html = html.replace(/T_idElement_T/ig     ,tpl.idListElement+this.id);
    html = html.replace(/T_functionElement_T/ig,'this.image.show()');
    html = html.replace(/T_id_T/gi,this.id);
    html = html.replace(/T_title_T/ig,this.title);
    return html;
  }

  this.drawNav = function() 
  {
    var html = tpl.tplNavigatorImage;
    html = html.replace(/T_idElement_T/ig     ,tpl.idNavElement+this.id);
    html = html.replace(/T_functionElement_T/ig,'nav.select('+this.id+')');
    html = html.replace(/T_id_T/gi,this.id);
    html = html.replace(/T_title_T/ig,this.title);

    return html;
  }

  this.show = function() 
  {
    this.showNavigator();
  }

  this.showNavigator = function() 
  {
    document.getElementById(tpl.idViewContainer).style.display = 'block';

    nav.album = cat.get(this.pid);
    nav.draw();
    nav.select(this.id);
  }

  this.playImage = function() 
  {
    var html = tpl.tplViewImage;
    html = html.replace(/T_id_T/gi,this.id);
    html = html.replace(/T_title_T/ig,this.title);
    return html;


    //title = document.getElementById('viewTitle');
    //title.innerHTML = '<b>'+this.title+'</b><br/>'+this.width+'x'+this.height;
  }

  this.playVideo = function()
  {
    if (!this.tested) {
      this.getVideoStatus();
      return '';
    }
    tpl.playerOP1 = '&title='+this.title+'&autoplay=1&autoload=1&showstop=1&showvolume=1&showtime=1';
    html = tpl.tplViewVideo;
    html = html.replace(/T_dataElement_T/gi,tpl.playerURL+tpl.playerOPT+tpl.playerOP1);
    html = html.replace(/T_id_T/gi,this.id);
    return html;

//    title = document.getElementById('viewTitle');
 //   title.innerHTML = '<b>'+this.title+'</b><br/>'+this.width+'x'+this.height+' - '+this.duration+'<br/><b>[Telecharger]</b>';
  }

  this.getVideoStatus = function() 
  {
    ajax.send('getVideoStatus',[['id',this.id],['pid',this.pid]]);
  }
}

Cajax.prototype.comGetVideoStatus = function(info,data) 
{
  var info = this.autobuild(info,new Object());
  var image = cat.get(info.pid).getOneImage(info.id);
  if (info.flv == '1') {
    image.tested = true;
    image.show();
  } else {
    image.tested = false;
    alert('La video est en cours de traitement\nCette operation peut prendre plusieur minute et bloquera partiellement la navigation sur le site\nUn message vous sera envoyé a la fin de l\'operation');
    ajax.send('generateVideo',[['id',info.id],['pid',info.pid]]);
  }
}

Cajax.prototype.comGenerateVideo = function(info,video)
{
  var info = this.autobuild(info,new Object());
  var image = cat.get(info.pid).getOneImage(info.id);
  image.tested = true;
  alert('La video a été correctement generer a un format lisible sur le site');
}
