<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" dir="ltr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="keywords" content="php apache mysql mathieu chocat ashaan gallery galerie galeries image images video videos vidéo vidéos photo photos album albums" />
  {meta}
  <title>{fulltitle}</title>
  <script type="text/javascript" src="template/default/script/function.js"></script>
  FOREACH {themeList} DO BEGIN[0]
    IF '{name}' == '{themeCurrent}' THEN BEGIN[1]
      <link rel="stylesheet" type="text/css" href="{themePath}{name}/theme.css" title="{name}"/>
    [1]END ELSE BEGIN[1]
      <link rel="alternate stylesheet" type="text/css" href="{themePath}{name}/theme.css" title="{name}"/>
    [1]END
  [0]END
</head>
<body>

<!-- begin header -->
<div class="header">
  <table>
  <tr>
    <td>
    </td>
    <td style="text-align:center">
      <a href="{url}">{title}</a>
    </td>
    <td style="text-align:right">
<!-- begin login button -->
<div id="loginButton">
IF '{isLogged}' THEN BEGIN[0]
  [<a href="#" class="username" onclick="setVisibility('loginZone');switchClass('loginButton','','active');">{username}</a>]
[0]END ELSE BEGIN[0]
  <a href="#" class="connect" onclick="setVisibility('loginZone');switchClass('loginButton','','active');">{lang_connect}</a>
[0]END
</div>
<!-- end login button -->
    </td>
  </tr>
  </table>
</div>
<!-- end header -->

<!-- begin content -->
<div class="content">
  <div class="contentNavigatorTop">GETBLOCK[navigator]</div>
  {content}
  <div class="contentNavigatorBottom">GETBLOCK[navigator]</div>
</div>
<!-- end content -->

<!-- begin footer -->
<div class="footer">
  APP_NAME by APP_AUTHOR<br/>
  EXEC_TIME<br/>
  <a href="http://validator.w3.org/check?uri=referer">
    <img src="http://www.w3.org/Icons/valid-xhtml11-blue.png" alt="Valid XHTML 1.0 Strict" height="20" width="60" style="border:0px;"/>
  </a>
  <a href="#css" onclick="window.location.href = 'http://jigsaw.w3.org/css-validator/validator?uri='+window.location.href;">
    <img src="http://www.w3.org/Icons/valid-css2.png" alt="Valid XHTML 1.0 Strict" height="20" width="60" style="border:0px;"/>
  </a>
</div>
<!-- end footer -->


<!-- begin login zone -->
<div id="loginZone" style="display:none">
<form action="?" method="post">
IF '{isLogged}' THEN BEGIN[0]
  <input type="hidden" name="action" id="loginOption" value=""/>
  FOREACH {userMenu} DO BEGIN[1]
    <input class="menu" type="button" value="{lang_{name}}" onclick="document.getElementById('loginOption').value='{name}';submit();"/>
  [1]END
  <input class="menu" type="button" value="{lang_close}"    onclick="setVisibility('loginZone');switchClass('loginButton','','active');"/>
[0]END ELSE BEGIN[0]
  <table>
  <tr>
    <td colspan="2" class="title">
      {lang_connecting}
    </td>
  </tr><tr>
    <td class="name">
      {lang_login} :
    </td>
    <td class="value">
      <input type="text" name="username" value=""/>
    </td>
  </tr><tr>
    <td class="name">
      {lang_password} :
    </td>
    <td class="value">
      <input type="password" name="password" value=""/>
    </td>
  </tr><tr>
    <td colspan="2" class="option">
      <input type="checkbox" name="longlife" value="1"/> {lang_sessionlonglife}
    </td>
  </tr><tr>
    <td colspan="2" class="button">
      <input type="button" value="{lang_close}" onclick="setVisibility('loginZone');switchClass('loginButton','','active');"/>
      <input type="submit" value="{lang_valid}"/>
    </td>
  </tr>
  </table>
[0]END
</form>
</div>
<!-- end login zone -->

</body>
</html>

BLOCKBEGIN[navigator]
  SETVAR[0][first,1,]
  FOREACH {navigator} DO BEGIN[0]
    IF {first}==0 THEN BEGIN[1]
      &raquo;
    [1]END ELSE BEGIN[1]
      SETVAR[2][first,0,]
    [1]END
    IF {current}==0 THEN BEGIN[1]
      <a href="OPERATOR[getMyUrlEncode,?mode={mode}&id={id}]" class="contentNavigator">{title}</a>
    [1]END ELSE BEGIN[1]
      <a href="OPERATOR[getMyUrlEncode,?mode={mode}&id={id}]" class="contentNavigatorSelected">{title}</a>
    [1]END
  [0]END
[navigator]BLOCKEND
