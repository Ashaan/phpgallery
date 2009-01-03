IF {count_album}>0 THEN BEGIN[0]
  <div id="gallery">
    IF '{count_album}'=='{count_allalbum}' THEN BEGIN[1]
      GETBLOCK[categoryofalbum]  
    [1]END ELSE BEGIN[1]
      GETBLOCK[categoryofcategory]  
    [1]END
  </div>
[0]END ELSE BEGIN[0]
  SETVAR[1][imageperpage,20,]
  SETVAR[1][page_count,ceil({count_image}/{imageperpage}),M]
  SETVAR[1][page_prev,{page_current}-1,M]
  SETVAR[1][page_next,{page_current}+1,M]
  SETVAR[1][image_first,({page_current}-1)*{imageperpage},M]
  SETVAR[1][image_last,({page_current}*{imageperpage}),M]
  <div id="album">
    GETBLOCK[albumInfo]
    GETBLOCK[albumList]
  </div>
  GETBLOCK[image]
[0]END

BLOCKBEGIN[categoryofcategory]  
  FOREACH {album} DO BEGIN[2]
    <div id="album{id}" class="category">
      <div class="categoryTitle">
        {title}
      </div>
      <div class="categoryStat">
        {count_allimage} images dans {count_allalbum} albums
      </div>
      <table class="categoryList">
      <tr>
        FOR {album}=0 TO 6 DO BEGIN[3]
          IF {i}!=0 && ({i}/3)==round({i}/3) THEN BEGIN[4]
            </tr><tr>
          [4]END
          <td id="album{id}" class="album">
            <a href="OPERATOR[getMyUrlEncode,?mode=album&id={id}]">
              <img src="OPERATOR[getImageLink,{image},214,60]" alt="{title}"/>
              <br/>
              <span class="title">
                {title}
              </span>
              <br/>
              <span class="count">
                {count_image} images
              </span>
            </a>
          </td>
        [3]END
      </tr>
      </table>
      <div class="categoryMore">
        IF {count_album}>6 THEN BEGIN[3]
          <input type="button" value="{lang_morealbum}" onclick="window.location.href = 'OPERATOR[getMyUrlEncode,?mode=album&id={id}&morealbum=1]'"/>
        [3]END
      </div>
    </div>
  [2]END
[categoryofcategory]BLOCKEND
BLOCKBEGIN[categoryofalbum]
  <div id="album{id}" class="category">
    <div class="categoryTitle">
      {title}
    </div>
    <div class="categoryStat">
      {count_allimage} images dans {count_allalbum} albums
    </div>
    <table class="categoryList">
    <tr>
      SETVAR[2][i,-1,M]
      FOREACH {album} DO BEGIN[2]
        SETVAR[3][i,{i}+1,M]
        IF {i}!=0 && ({i}/3)==round({i}/3) THEN BEGIN[3]
          </tr><tr>
        [3]END
        <td id="album{id}" class="album">
          <a href="OPERATOR[getMyUrlEncode,?mode=album&id={id}]">
            <img src="OPERATOR[getImageLink,{image},214,60]" alt="{title}"/>
            <br/>
            <span class="title">
              {title}
            </span>
            <br/>
            <span class="count">
              {count_image} images
            </span>
          </a>
        </td>      
      [2]END
    </tr>
    </table>
  </div>
[categoryofalbum]BLOCKEND
BLOCKBEGIN[albumInfo]
  <div class="info">
    <div class="albumTitle">
      {title}
    </div>
    IF '{desc}'!='' THEN BEGIN[1]
      <div class="albumComment">
        {lang_desc} :<br/>
        {desc}
      </div>
    [1]END
    <div class="albumStat">
      {lang_lastupdate} : OPERATOR[getMyDate,{lastdate}]<br/>
      {lang_createdate} : OPERATOR[getMyDate,{firstdate}]<br/>
    </div>
    IF '{archive}' THEN BEGIN[1]
      <div class="albumArchive">
        FOREACH {archive} DO BEGIN[2]
          <a href="OPERATOR[getArchiveLink,{id},{type},{lastdate}]">archive ({type}/OPERATOR[getMySize,{size}])</a>
        [2]END
      </div>
    [1]END
    IF {count_image}>{imageperpage} THEN BEGIN[1]
      <div id="albumNavigator" class="albumNavigator">
        IF {page_current}>2 THEN BEGIN[2]
          <a href="OPERATOR[getMyUrlEncode,?page=1]" class="first">&laquo;</a>
        [2]END
        IF {page_current}>1 THEN BEGIN[2]
          <a href="OPERATOR[getMyUrlEncode,?page={page_prev}]"  class="prev">&lsaquo;</a>
        [2]END
        <span>{page_current}/{page_count}</span>
        IF {page_current}<{page_count} THEN BEGIN[2]
          <a href="OPERATOR[getMyUrlEncode,?page={page_next}]"  class="next">&rsaquo;</a>
        [2]END
        IF {page_current}+1<{page_count} THEN BEGIN[2]
          <a href="OPERATOR[getMyUrlEncode,?page={page_count}]"  class="last">&raquo;</a>
        [2]END
      </div>
    [1]END
  </div>
[albumInfo]BLOCKEND
BLOCKBEGIN[albumList]
  <div class="list">
    <table class="list">
    <tr>
      FOR {media}={image_first} TO {image_last} DO BEGIN[1]
        IF {i}!=0 && ({i}/5)==round({i}/5) THEN BEGIN[2]
          </tr><tr>
        [2]END
        <td>
          <a href="javascript:showImage({id})">
            <img src="OPERATOR[getImageLink,{id},100,80,001,1]" alt="{title}" onmouseover="this.src='OPERATOR[getImageLink,{id},100,80,002,1]'" onmouseout="this.src='OPERATOR[getImageLink,{id},100,80,001,1]'"/>
            <img src="OPERATOR[getImageLink,{id},100,80,002,1]" alt="{title}" style="display:none;"/>
          </a>
        </td>
      [1]END
    </tr>
    </table>
  </div>
[albumList]BLOCKEND
BLOCKBEGIN[image]
  SETVAR[1][imageNavCount,3,]
  <div id="view">
    <script language="javascript" type="text/javascript">
      var imageList = [{mediaId}];
      var albumId   = {id};

      function showImage(id) 
      {
        for(var i=0;i<imageList.length;i++) {
          if (imageList[i][0] == id) {
            index = i;
            break;
          }
        }
        for(var i=0;i<{imageNavCount};i++) {
          //prev
          prev  = document.getElementById('viewPrev'+({imageNavCount}-i-1));
          if (index-i-1>=0) {
            prev.style.display = '';
            prev.src = 'image.php?id='+imageList[index-i-1][0]+'&w=100&h=80&m=001&t=1';
            prev.imageId = imageList[index-i-1][0];
          } else {
            prev.style.display = 'none';
          }

          next  = document.getElementById('viewNext'+(i));
          if (index+i+1<imageList.length) {
            next.style.display = '';
            next.src = 'image.php?id='+imageList[index+i+1][0]+'&w=100&h=80&m=001&t=1';
            next.imageId = imageList[index+i+1][0];
          } else {
            next.style.display = 'none';
          }
        }
        displayImage(imageList[index][0],imageList[index][1]);
      }
      function displayImage(id,type) {
        view  = document.getElementById('view');
        title = document.getElementById('viewTitle');
        image = document.getElementById('viewImage'); 
        video = document.getElementById('viewVideo'); 
        param = document.getElementById('viewVideoMovie');
        image.style.display = 'none';
        video.style.display = 'none';

        if (type == 'I') {
          view.style.display = 'block';
          image.style.display = '';
          image.src = 'image.php?id='+id+'&w=600';
          title.innerHTML = '<b>'+imageList[index][5]+'</b><br/>'+imageList[index][2]+'x'+imageList[index][3];
        }
        if (type == 'V') {
          view.style.display = 'block';
          video.style.display = '';
  //        image.src   = 'video.php?id='+id;
  //        video.data  = "template/default/player/flvplayer.swf?file={url}media.php?mode=video&amp;id='+id+'&amp;type=flv&amp;ext=.flv&showdigits=false&showicons=false&autostart=true&showfsbutton=false&repeat=false';
  //        video.data  = 'template/default/player/flvplayer.swf?flv=\"{url}media.php?mode=video&id='+id+'&type=flv&ext=.flv\"';
          param = document.getElementById('viewVideoVars');
          param.value = 'flv={url}video.php?id='+id+'&showstop=1&showvolume=1';

          title.innerHTML = '<b>'+imageList[index][5]+'</b><br/>'+imageList[index][2]+'x'+imageList[index][3]+' - '+imageList[index][4]+'<br/><b>[Telecharger]</b>';
        }
      }
      function getImageUrl(id,mask)
      {
        return 'image.php?id='+id+'&w=100&h=80&m='+mask+'&t=1';
      }
    </script>

    <div class="viewContent">
      <div class="viewNavigator">
        <div class="prev">
          FOR =0 TO {imageNavCount} DO BEGIN[1]
            <img id="viewPrev{i}" src="test{i}" alt="{lang_prev} (-{i})" 
                 onmouseover="this.src=getImageUrl(this.imageId,'002')"
                 onmouseout ="this.src=getImageUrl(this.imageId,'001')"
                 onclick    ="showImage(this.imageId)"/>
          [1]END
        </div>
        <div class="next">
          FOR =0 TO {imageNavCount} DO BEGIN[1]
            <img id="viewNext{i}"  src="test{i}" alt="{lang_next} (+{i})"
                 onmouseover="this.src=getImageUrl(this.imageId,'002')"
                 onmouseout ="this.src=getImageUrl(this.imageId,'001')"
                 onclick    ="showImage(this.imageId)"/>
          [1]END
        </div>
      </div>
      <div id="viewZone" class="viewImage"  onclick="document.getElementById('view').style.display = 'none'">
        <img id="viewImage" src="image" alt="" style="display:none"/>
        <object id="viewVideo" type="application/x-shockwave-flash"
                data="template/default/player/flvplayer.swf" width="600" height="450"
                style="display:none">
          <param id="viewVideoMovie" name="movie" value="template/default/player/flvplayer.swf"/>
          <param id="viewVideoVars" name="FlashVars" value="" />
          <param name="vmode" value="transparent"/>
        </object>
      </div>
      <div id="viewTitle"></div>
    </div>
  </div>
[image]BLOCKEND
