String.prototype.trim = function()
{
  return this.replace(/^\s+/,'').replace(/\s+$/,'');
}

String.prototype.ucfirst = function()
{
  var aString = this;
  var aResult = aString.charAt(0).toUpperCase();
  for (var i=1;i<aString.length;i++) {
    aResult += aString.charAt(i);
  }

  return aResult;
}

