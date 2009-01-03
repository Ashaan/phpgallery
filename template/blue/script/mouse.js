
if (!document.all) document.captureEvents(Event.MOUSEMOVE);
document.onmousemove = getMouse;
//alert(IE);

function getMouse(e) {
  if (document.all) {
    x = event.clientX + document.body.scrollLeft;
    y = event.clientY + document.body.scrollTop;
  } else {
    x = e.pageX;
    y = e.pageY;
  }

  if (x<0) {
    x = 0;
  }
  if (y<0) {
    y = 0;
  }

  mouse.x = x;
  mouse.y = y;
  mouse.update();
}

function Cmouse() {
  this.x = 0;
  this.y = 0;
  this.selectObject = null;
  this.selectLastParent = null;
  this.selectId     = 0;
  this.selectType   = '';
  this.moveObject = null;
  this.moveLastParent = null;
  this.moveId     = 0;
  this.moveType   = '';

  this.select = function(mode,object,id) 
  {
    if (this.selectLastParent == null) {
      this.selectLastParent = object;
    }
    if (this.selectObject != object && this.selectLastParent.parentNode.parentNode != object) {
      this.selectObject = object;
      this.selectId     = id;
      this.selectType   = mode;
    }
    this.selectLastParent = object;
  }
  this.unSelect = function() 
  {
    this.selectObject = null;
    this.selectId     = 0;
    this.selectType   = '';
  }
  this.move = function(mode,object,id) 
  {
    if (this.moveObject != object) {
      alert(object.id+' movable');
      this.moveObject = object;
      this.moveId     = id;
      this.moveType   = mode;
      this.moveObject.style.position = 'absolute';
    } else {
      alert(object.id+' unmovable');
      this.moveObject.style.position = '';
      this.moveObject = null;
      this.moveId     = 0;
      this.moveType   = '';
    }
  }
  this.moveObject = function(evt)
  {
    if (this.moveObject && this.moveObject.style) {
      this.moveObject.style.left = evt.pageX?evt.pageX:evt.clientX;
      this.moveObject.style.top  = evt.pageY?evt.pageY:evt.clientY;
    }
  }

  this.update = function()
  {
    document.getElementById('footerMousePos').innerHTML = mouse.x + ','+mouse.y;
  }
}
