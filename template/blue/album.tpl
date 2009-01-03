<script language="javascript" type="text/javascript">
  function Ctpl() {
    this.idTreeContainer          = 'tree';
    this.idTreeTitle              = 'treeTitle';
    this.idTreeElement            = 'treeElement';
    this.idTreeExpand             = 'treeExpand';
    this.classTreeContainer       = 'container';
    this.classTreeCannotExpand    = 'expandCannot';
    this.classTreeNotExpand       = 'expandNot';
    this.classTreeExpand          = 'expand';
    this.classTreeTitle           = 'title';
    this.classTreeTitleSelected   = 'titleSelected';
    this.classTreeElement         = 'element';

    this.idListContainer          = 'list';
    this.idListElement            = 'listElement';
    this.classListElement         = 'listElement';

    this.idDataTitle              = 'dataTitle';

    this.idViewContainer          = 'view';
    this.idNavContainer           = 'navImage';
    this.idNavElement             = 'nav';

    this.langDateFirst            = '{lang_datefirst}';
    this.langDateLast             = '{lang_datelast}';
    this.langImage                = '{lang_media}';
    this.langAlbum                = '{lang_album}';
    this.langBy                   = '{lang_by}';

    this.playerURL = 'template/default/player/flvplayer.swf';
    this.playerOPT = '?flv={url}video.php?id=T_id_T';
    this.playerOP1 = '&showdigits=false&showicons=false&autostart=true&showfsbutton=false&repeat=false';
    this.playerOP2 = '&autostart=true';

    // media - vue mosaique
    this.tplImageList1 = 
      '<div id="T_idElement_T" class="T_classElement_T" onclick="T_functionElement_T">' +
        '<img src="{url}media.php?mode=image&id=T_id_T&amp;w=80&amp;h=60" alt="T_title_T"\/>' +
        '<br\/>' +
        'T_title_T' +
      '<\/div>';
    // media - vue list
    this.tplImageList2 = 
      '<div id="T_idElement_T" class="T_classElement_T" style="width:98%;text-align:left;" onclick="T_functionElement_T">' +
        'T_title_T' +
      '<\/div>';
    // media - vue
    this.tplImageList = this.tplImageList1;
    
    // album - tree
    this.tplAlbumList = 
      '<div class="T_classContainer_T" id="T_idContainer_T">' +
        '<div class="T_classTitle_T" id="T_idTitle_T" onclick="T_functionTitle_T">' +
          '<div class="T_classExpand_T" id="T_idExpand_T" onclick="T_functionExpand_T"><\/div>' +
          '<img src="{url}media.php?mode=image&id=T_image_T&amp;w=30&amp;h=20&m=004&t=1" alt="???"\/>' +
          'T_title_T' +
        '<\/div>' + 
        '<div class="T_classElement_T" id="T_idElement_T"><\/div>' + 
      '<\/div>';
    // album - tree move
    this.tplAlbumMove = 
      '<div class="moveAlbum" id="moveAlbum">' +
        '<img src="{url}media.php?mode=image&id=T_image_T&amp;w=30&amp;h=20&m=004&t=1" alt="???"\/>' +
        'T_title_T' +
      '<\/div>';
    //
    this.tplNavigatorImage =
      '<div id="T_idElement_T" class="image" onclick="T_functionElement_T">' +
        '<img src="{url}media.php?mode=image&id=T_id_T&amp;w=80&amp;h=60" alt="T_title_T"\/>' +
      '<\/div>';

    this.tplViewImage = '<img src="{url}media.php?mode=image&id=T_id_T&amp;w=600" alt=""\/>';
    this.tplViewVideo = 
      '<div id="T_idElement_T" class="image">' +
        '<object type="application/x-shockwave-flash" data="T_dataElement_T" height="300" width="400">'+
          '<param name="movie" value="T_dataElement_T"\/>'+
          '<param name="vmode" value="transparent"\/>'+
        '<\/object>'+
      '<\/div>'+
      '';
    this.tplAdminAlbumAction = 
      '<div>{lang_album_new}    <\/div>' +
      '<div>{lang_album_virtual}<\/div>' +
      '<div>{lang_album_import} <\/div>' +
      '<div>{lang_album_rename} <\/div>' +
      '<div>{lang_album_move}   <\/div>' +
      '<div>{lang_album_delete} <\/div>';
    this.tplAdminMediaAction =
      '<div>{lang_album_new}    <\/div>' +
      '<div>{lang_album_rename} <\/div>' +
      '<div>{lang_album_move}   <\/div>' +
      '<div>{lang_album_delete} <\/div>';
  }

  var zIndexMax = 500;
  var mouse= new Cmouse();
  var ajax = new Cajax();
  var tpl  = new Ctpl();
  var nav  = new Cnavigator();
  var cat  = new Ccategory();
</script>

<div id="tree">
  <div id="treeElement0" style="margin-left:-5px;">
  </div>
</div>



<div id="data">
  <div id="dataTitle" class="title">
  </div>
  <div class="info">
    <div class="infoBox">
      <div class="infoTitle"   id="infoTitle"></div>
      <div class="infoElement" id="infoDateFirst"></div> 
      <div class="infoElement" id="infoDateLast"></div> 
      <div class="infoElement">
        <span id="infoCountAlbum"></span>/<span id="infoCountImage"></span>
      </div>
    </div>
    <div class="infoBox">
      <div class="infoTitle">{lang_description}</div>
      <div class="infoElement" id="infoDesc"></div> 
    </div>
    <div class="infoBox">
      <div class="infoTitle">{lang_action}</div>
      <div class="infoElement" id="infoAction"></div> 
    </div>
  </div>
  <div class="zone">
    <div class="option">

    </div>
    <div id="list" class="list">
    </div>
  </div>
</div>


<div id="view">
  <div id="nav">
    <div id="navImage" class="scroll"></div>
    <div class="left"  onmouseover="nav.right()"  onmouseout="nav.stop()"></div>
    <div class="right" onmouseover="nav.left()" onmouseout="nav.stop()"></div>
  </div>
  <div id="viewZone" class="viewZone" onclick="document.getElementById('view').style.display = 'none'">

  </div>
</div>

<script language="javascript" type="text/javascript">
  function initialize()
  {
    cat.addAlbum(new Calbum(0,-1,'',-1,1,0));

    IF '{parentList}'=='' THEN BEGIN[0]
      cat.get(0).expand();
    [0]END ELSE BEGIN[0]
      var parent = [{parentList},{id}];  
      var parent2 = [];
      for(var i=1;i<parent.length;i++) {
        parent2[parent2.length] = parent[i];
      }
      cat.get(parent[0]).expand(parent2);
    [0]END
  }
  initialize();

</script>
