function Cnavigator() 
{
  this.imageWidth  = 80;
  this.imageHeight = 60;
  this.album       = null;
  this.image       = null;
  this.current     = null;
  this.width       = 0;
  this.pos         = 0;
  this.moveId      = 0;
  this.moveInc     = 0;

  this.draw = function() 
  {
    var anav = document.getElementById(tpl.idNavContainer);
    anav.innerHTML = '';
    this.image = this.album.media;
    for (var i=0;i<this.image.length;i++) {
      anav.innerHTML += this.image[i].drawNav();
    }
    this.width = this.image.length*this.imageWidth;

    anav.style.width = this.width+'px';
    anav.style.left  = '0px';
    this.pos = 0;
  }

  this.left = function() 
  {
    this.moveInc = -1;
    if (this.moveId==0) {
       this.moveId = setInterval("nav.move()",10);
    }
  }

  this.right = function() 
  {
    this.moveInc = 1;
    if (this.moveId==0) {
       this.moveId = setInterval("nav.move()",10);
    }
  }

  this.stop = function() 
  {
    this.moveInc = 0;
    clearInterval(this.moveId);
    this.moveId = 0
  }

  this.move = function() 
  {
    var max = document.body.clientWidth;
    var maxLeft  = Math.floor((document.body.clientWidth/2)-(this.imageWidth/2));
    var maxRight = Math.floor(- this.width + ((document.body.clientWidth/2)-(this.imageWidth/2)));
    var mi = 2;
    var anav = document.getElementById(tpl.idNavContainer);

    if (this.pos+this.moveInc*mi >= maxLeft) {
      this.pos       = maxLeft;
      anav.style.left = maxLeft+'px';
      this.moveInc = 0;
    }
    if (this.pos+this.moveInc*mi <= maxRight) {
      this.pos       = maxRight;
      anav.style.left = maxRight+'px';
      this.moveInc = 0;
    }

    if (document.getElementById(tpl.idViewContainer).style.display == 'none') {
      this.move = 0;
    }

    if (this.moveInc == 1) {
      this.pos = this.pos+mi;
      anav.style.left = (this.pos)+'px';
    } else if (this.moveInc == -1) {
      this.pos = this.pos-mi;
      anav.style.left = (this.pos)+'px';
    } else {
      clearInterval(this.moveId);
      this.moveId = 0
    }
  }

  this.select = function(id) 
  {
    this.current = null;
    var index   = -1;
    for (var i=0;i<this.image.length;i++) {
      if (id==this.image[i].id) {
        index   = i;
        this.current = this.image[i];
      } else {
        document.getElementById(tpl.idNavElement+this.image[i].id).className = "image";
      }
    }
    if (index == -1) {
      return false;
    }
    var maxLeft  = Math.floor((document.body.clientWidth/2)-(this.imageWidth/2));

    this.pos = maxLeft - this.imageWidth*index;
    anav = document.getElementById(tpl.idNavContainer);
    anav.style.left = (this.pos)+'px';
    document.getElementById(tpl.idNavElement+this.current.id).className = "imageSelect";
    this.show();
  }

  this.show = function() 
  {
    document.getElementById('viewZone').innerHTML = '';
    if (this.current.mime_group == 'image') {
      document.getElementById('viewZone').innerHTML = this.current.playImage();
    }
    if (this.current.mime_group == 'video') {
      document.getElementById('viewZone').innerHTML = this.current.playVideo();
    }
  }
}
