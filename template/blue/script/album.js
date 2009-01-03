function Calbum(id,pid,title,image,count_album,count_image) 
{
  this.id           = id;
  this.pid          = pid;
  this.title        = title;
  this.image        = image;
  this.count_album  = count_album;
  this.count_image  = count_image;
  this.child        = [];
  this.parent       = null;
  this.media        = [];

  this.addChild = function(child) 
  {
    var x = this.child.length;
    for (var i=0;i<this.child.length;i++) {
      if (this.child[i].id == child.id) {
        x = i;
        break;
      }
    }
    child.parent = this;
    this.child[x] = child;
  }

  this.draw = function() 
  {
    var html = tpl.tplAlbumList;
    html = html.replace(/T_classContainer_T/ig,tpl.classTreeContainer);
    html = html.replace(/T_classTitle_T/ig    ,tpl.classTreeTitle);
    html = html.replace(/T_classElement_T/ig  ,tpl.classTreeElement);
    html = html.replace(/T_idContainer_T/ig   ,tpl.idTreeContainer+this.id);
    html = html.replace(/T_idTitle_T/ig       ,tpl.idTreeTitle+this.id);
    html = html.replace(/T_idExpand_T/ig      ,tpl.idTreeExpand+this.id);
    html = html.replace(/T_idElement_T/ig     ,tpl.idTreeElement+this.id);

    if (this.count_album<1 && this.count_image<1) {
      html = html.replace(/T_classExpand_T/ig   ,tpl.classTreeCannotExpand);
      html = html.replace(/T_functionExpand_T/ig,'');
      html = html.replace(/T_functionTitle_T/ig,'this.album.select()');
    } else if (this.count_album<1) {
      html = html.replace(/T_classExpand_T/ig ,tpl.classTreeCannotExpand);
      html = html.replace(/T_functionExpand_T/ig,'');
      html = html.replace(/T_functionTitle_T/ig,'this.album.select()');
    } else if (this.count_image<1) {
      html = html.replace(/T_classExpand_T/ig ,tpl.classTreeNotExpand);
      html = html.replace(/T_functionExpand_T/ig,'this.album.expand()');
      html = html.replace(/T_functionTitle_T/ig,'this.album.select()');
    } else {
      html = html.replace(/T_classExpand_T/ig ,tpl.classTreeNotExpand);
      html = html.replace(/T_functionExpand_T/ig,'this.album.expand()');
      html = html.replace(/T_functionTitle_T/ig,'this.album.select()');
    }

    html = html.replace(/T_id_T/ig     ,this.id);
    html = html.replace(/T_image_T/ig  ,this.image);
    html = html.replace(/T_title_T/ig  ,this.title);

    return html;
  }

  this.expand = function(autoexpand) 
  {
    var autoexpandOld = null;
    var autoexpandId  = null;
    var autoexpandNew = null;

    if (autoexpand && autoexpand!='') {
      var autoexpand2 = [];
      for(var i=1;i<autoexpand.length;i++) {
        autoexpand2[autoexpand2.length] = autoexpand[i];
      }
      if (autoexpand2 == []) {
        autoexpandOld = autoexpand;
        autoexpandId  = autoexpand[0];
      } else {
        autoexpandOld = autoexpand;
        autoexpandId  = autoexpand[0];
        autoexpandNew = autoexpand2;
      }
    }
 
    var element = document.getElementById(tpl.idTreeElement+this.id);
    if (this.id == 0) {
      element.innerHTML = '';
    }
    if (this.count_album < 1) {
      if (this.id != 0) {
        document.getElementById(tpl.idTreeExpand+this.id).className = tpl.classTreeCannotExpand;
      }
      if (autoexpandOld) {
        var album = cat.get(autoexpandId);
        album.setInfo();
        album.setList();
      }
    } else if (element.innerHTML != '') {
      if (this.id != 0) {
        document.getElementById(tpl.idTreeExpand+this.id).className = tpl.classTreeNotExpand;
      }
      element.innerHTML = '';
    } else {
       if (this.id != 0) {
        document.getElementById(tpl.idTreeExpand+this.id).className = tpl.classTreeExpand;
      }
      if (this.getAlbum(autoexpandOld)) {
        for (var i=0;i<this.child.length;i++) {
          element.innerHTML += this.child[i].draw();
        }
        for (var i=0;i<this.child.length;i++) {
          document.getElementById(tpl.idTreeExpand+this.child[i].id).album = this.child[i];
          document.getElementById(tpl.idTreeTitle+this.child[i].id).album  = this.child[i];
          data = document.getElementById(tpl.idTreeContainer+this.child[i].id);
          this.setMovable(data);
        }

        if (autoexpandNew != false || autoexpandId != false) {
          var album = cat.get(autoexpandId);
          if (album.count_album>0) {
            album.expand(autoexpandNew);
          }
          if (album.count_image>0) {
            album.setInfo();
            album.setList();
          }
        }
      }
    }
  }

  this.select = function() 
  {
    if (!document.getElementById(tpl.idTreeTitle+this.id)) {
      return false;
    }

    for(var i=0;i<cat.album.length;i++) {
      cat.album[i].unselect();
    }

    document.getElementById(tpl.idTreeTitle+this.id).className = tpl.classTreeTitleSelected;

    this.setInfo();
    this.setList();
  }

  this.unselect = function() 
  {
    if (document.getElementById(tpl.idTreeTitle+this.id)) {
      document.getElementById(tpl.idTreeTitle+this.id).className = tpl.classTreeTitle;
    }
  }

  this.setInfo = function() 
  {
    var title  = this.title;
    var parent = this.parent;
    while (parent.parent != null) {
      title = parent.title + ' - ' + title;
      parent = parent.parent;
    }
    document.getElementById(tpl.idDataTitle).innerHTML = title;

    document.getElementById('infoTitle').innerHTML      = this.title;

    document.getElementById('infoDateFirst').innerHTML  = tpl.langDateFirst+'<b>'+this.date_first+'</b><br/>'+tpl.langBy+'<b>'+this.user_name+'</b>';
    document.getElementById('infoDateLast').innerHTML   = tpl.langDateLast +'<b>'+this.date_last +'</b><br/>'+tpl.langBy+'<b>'+this.user_name+'</b>';
    document.getElementById('infoCountAlbum').innerHTML = '<b>'+this.count_album +'</b>'+tpl.langAlbum;
    document.getElementById('infoCountImage').innerHTML = '<b>'+this.count_image +'</b>'+tpl.langImage;

    if (this.description != 'undefined') {
      document.getElementById('infoDesc').innerHTML = '-';
    } else {
      document.getElementById('infoDesc').innerHTML = this.description;
    }
  }

  this.setList = function() 
  {
    var image = this.getImage();
    var alist = document.getElementById(tpl.idListContainer);
    alist.innerHTML = '';
    alist.album = this;

    if (image) {
      for (var i=0;i<this.media.length;i++) {
        alist.innerHTML += this.media[i].draw();
      }
      for (var i=0;i<image.length;i++) {
        document.getElementById(tpl.idListElement+image[i].id).image = image[i];
      }
    }
  }


  this.getImage = function() 
  {
    if (this.count_image>0 && this.media.length<1) {
      ajax.send('getImage',[['id',this.id]]);   
    } else if (this.media.length>0) {
      return this.media;
    }
    return false;
  }

  this.getAlbum = function(autoexpand) 
  {
    if (this.count_album>0 && this.child.length<1) {
      if (!autoexpand) {
        ajax.send('getAlbum',[['id',this.id]]);
      } else {
        var autoexpand2 = '';
        for (var i=0;i<autoexpand.length;i++) {
          if (autoexpand2!='') {
            autoexpand2 += ',';
          }
          autoexpand2 += autoexpand[i];
        }
        ajax.send('getAlbum',[['id',this.id],['expand',autoexpand2]]);   
      }
      return false;
    } else {
      return this.child;
    }
  }

  this.getOneImage = function(id) 
  {
    for (var i=0;i<this.media.length;i++) {
      if (this.media[i].id == id) {
        return this.media[i];
      }
    }
    return false;
  }

  this.setMovable = function(object)
  {
    object.move    = false;
    object.movable = true;
    object.album   = this;

    //object.onmousedown = albumMouseDown;
    object.onmouseup = function (e) {
      this.move = false;
      this.style.position = '';
      this.style.cursor   = '';
      this.style.left     = '';
      this.style.top      = '';
    }
  }
}

Cajax.prototype.comGetImage = function(info,data)
{
  var info = this.autobuild(info,new Object());

  var imageList = [];
  for (var i=0;i<data.childNodes.length;i++) {
    var element = data.childNodes[i];
    var image = this.autobuild(element,new Cimage());
    image.pid = image.album;
    imageList[imageList.length] = image;
  }
  cat.get(info.id).media = imageList;
  cat.get(info.id).setList();
}

function albumMouseDown(e)
{
  if (!e) e = window.event;
  var temp = (typeof e.target != "undefined") ? e.target : e.srcElement;
  if (temp.tagName != "HTML"|"BODY" && typeof(this.movable) == "undefined") {
    temp = (typeof temp.parentNode != "undefined") ? temp.parentNode : temp.parentElement;
  }
  if (this.movable) {
    var html = tpl.tplAlbumMove;
    html = html.replace(/T_image_T/ig   ,this.album.image);
    html = html.replace(/T_title_T/ig   ,this.album.title);
    this.shadow = document.getElementById('move');
    this.shadow.album     = this.album;
    this.shadow.innerHTML = html;

    move = document.getElementById('moveAlbum');
    move.style.left = e.clientX-30 + "px";
    move.style.top  = e.clientY-10 + "px";

    document.onmousemove = function (e) {
      if (!e) e = window.event;
      move = document.getElementById('moveAlbum');
      if (move) {
        move.style.left = e.clientX-30 + "px";
        move.style.top  = e.clientY-10 + "px";
      }
    }

    move.onmouseup = function (e) {
      this.shadow = document.getElementById('move');
      this.shadow.album     = null;
      this.shadow.innerHTML = '';
    }
    return false;
  }
}
