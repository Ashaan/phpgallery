function Ccategory() 
{
  this.current;
  this.album = [];

  this.addAlbum = function (album) 
  {
    album.child = [];

    var x = this.album.length;
    for (var i=0;i<this.album.length;i++) {
      if (this.album[i].id == album.id) {
        x = i;
        break;
      }
    }
    this.album[x] = album;

    if (album.pid>=0) {
      var parent = this.get(album.pid);
      parent.addChild(album);
    }

    return true;
  }

  this.get = function (id) 
  {
    for (var i=0;i<this.album.length;i++) {
      if (this.album[i].id == id) {
        return this.album[i];
      }
    }
    return false;
  }

  this.setCurrent = function(id) 
  {
    this.current = this.get(id);
  }
}

Cajax.prototype.comGetAlbum = function(info,data)
{
  var info = this.autobuild(info,new Object());
  if (typeof(info.expand_id)=='string') {
    info.expand_id = [info.expand_id];
  }

  for (var i=0;i<data.childNodes.length;i++) {
    var element = data.childNodes[i];
    var album = this.autobuild(element,new Calbum());
    cat.addAlbum(album);
  }
  cat.get(info.id).expand(info.expand_id);
}
