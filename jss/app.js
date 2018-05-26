var app={mode:'',par:'',tab:''};

//// Debug features ////

log=function(d)
{
 console.log(d);
}

//app.agent='?';
app.agent=
 (function x(){})[-5]=='x'?'FF3':
 (function x(){})[-6]=='x'?'FF2':
 /a/[-1]=='a'?'FF':
 '\v'=='v'?'IE':
 //a/.__proto__=='//'?'Saf':
 /s/.test(/a/.toString)?'Chr':
 /^function \(/.test([].sort)?'Op':
 '?';

app.nvl=function(val,def)
{
 return (typeof val=='undefined')?def:val;
}

app.str=function(val)
{
 return val?(''+val):'';
}

app.json=function(value)
{
 var result="";
 var t=typeof(value);
 if(value==null)
  result="null";
 else if(t=="string")
 {
  var tmp=String(value).split("\"");
  for(var i=0;i<tmp.length;i++)
   result+=(i?"\\\"":"")+tmp[i];
  result="\""+result+"\"";
 }
 else if(value instanceof Array)
 {
  for(i=0;i<value.length;i++)
   result=result+(i?", ":"")+app.json(value[i]);
  result="[ "+result+" ]";
 }
 else if(t!="object")
  result=""+value;
 else if("nodeName" in value)
  result="<"+value.nodeName+">";
 else if("json_mark" in value)
  result=""+value;
 else
 {
  value.json_mark=true;
  for(var p in value)
  {
   if(p=="json_mark")
    continue;
   var v=value[p];
   if(typeof(v)=="function")
    continue;
   if(p!="sender")
    v=app.json(v);
   result=result+(result==""?"":", ")+p+" : "+v;
  }
  delete value.json_mark;
  result="{ "+result+" }";
 }
 return result;
}

//// Utilities ////

String.prototype.replaceAll=function(search,replace)
{
 return this.split(search).join(replace);
}

String.prototype.ucfirst=function()
{
 return this.charAt(0).toUpperCase()+this.slice(1);
}

String.prototype.safeHTML=function()
{
 return this.replaceAll('\n','<br>')
 .replaceAll('<script','<s\u0441ript').replaceAll('</script','</s\u0441ript')
 .replaceAll('<object','<\u043ebject').replaceAll('</object','</\u043ebject');
}

String.prototype.toDate=function()
{
 var v=this.split('-');
 var result=new Date();
 if(v.length>0)
  result.setDate(parseInt(v[0]));
 if(v.length>1)
  result.setMonth(parseInt(v[1])-1);
 if(v.length>2)
  result.setFullYear(parseInt(v[2]));
 return result;
}

Date.prototype.toString=function()
{
 return (''+(this.getDate()+100)).substr(1,2)+'-'+(''+(this.getMonth() + 101)).substr(1,2)+'-'+this.getFullYear();
}

Date.prototype.addDays=function(days)
{
 this.setDate(this.getDate()+days);
 return this;
}

app.setTextHTML=function(pane,text)
{
 if(pane.hasClass('html'))
  pane.html((''+text).safeHTML());
 else
  pane.text(text);
}

app.setAttr=function(node,name,value)
{
 if((typeof value=='string')&&value.length)
  node.attr(name,value);
 else
  node.removeAttr(name);
}

app.setProp=function(object,name,value)
{
 if((typeof value=='string')&&value.length)
  object[name]=value;
 else if(name in object)
  delete object[name];
}

app.min2str=function(min)
{
 var m=min%60;
 var h=((min-m)/60)%24;
 return (''+(100+h)).substr(1)+':'+(''+(100+m)).substr(1);
}

app.strInt=function(value) // http://stackoverflow.com/questions/149055/
{
 var t = app.numCharMil || ' ';
 var i = parseInt(value = Math.abs(+value || 0)) + '';
 var j = (j = i.length) > 3 ? j % 3 : 0;
 return (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t);
}

app.addCurr=function(text,curr,code,nbsp)
{
 if(!curr)
  curr=app.curr;
 if(!curr||!curr.length)
  return text;
 curr=(!!code&&(curr.length>1))?curr[1]:curr[0];
 if(!curr||!curr.id)
  return text;
 if(!curr.pos)
  text=curr.id+' '+text;
 else if(curr.pos==1)
  text=curr.id+''+text;
 else if(curr.pos==2)
  text+=''+curr.id;
 else
  text+=' '+curr.id;
 return nbsp?text.replaceAll(' ','&nbsp;'):text;
}

app.parseUri=function(uri)
{
 var result={pro:'',host:'',path:'',paramStr:'',params:{},hash:''};
 uri=uri?uri:location.href;
 var p=uri.indexOf('#');
 if(p>=0)
 {
  result.hash=uri.substr(p);
  uri=uri.substr(0,p);
 }
 p=uri.indexOf('?');
 if(p>=0)
 {
  result.paramStr=uri.substr(p);
  uri=uri.substr(0,p);
  result.params=app.urlParams(result.paramStr);
 }
 p=uri.indexOf('://');
 if(p>=0)
 {
  result.pro=uri.substr(0,p+3);
  uri=uri.substr(p+3);
 }
 p=uri.indexOf('/');
 if(p>=0)
 {
  result.host=uri.substr(0,p);
  result.path=uri.substr(p);
 }
 else if(uri.indexOf('.')>0)
  result.host=uri;
 else
  result.path=uri;
 result.param=function(name){return(name in this.params)?this.params[name]:'';}
 result.href=function()
 {
  var p=[];
  for(var i in this.params)
   p.push(encodeURIComponent(i)+'='+encodeURIComponent(this.params[i]));
  this.paramStr=p.length?('?'+p.join('&')):'';
  return this.pro+this.host+this.path+this.paramStr+this.hash;
 }
 result.toString=function(){return this.href();}
 return result;
}

app.addParams=function(uri,params)
{
 if(typeof uri=='string')
  uri=app.parseUri(uri);
 if(typeof params=='string')
  params=app.urlParams(params);
 if(params instanceof Array)
 {
  for(var i in params)
  {
   var p=params[i].split('=');
   if(p.length==2)
    uri.params[p[0]]=p[1];
   else if(p.length==1)
    uri.params[p[0]]='';
  }
 }
 else if(params instanceof Object)
 {
  for(var i in params)
   uri.params[i]=params[i];
 }
 return uri.href();
}

app.makeURI=function(params,uri)
{
 var u=app.parseUri();
 if(uri)
  u.path+=(u.path.slice(-1)=='/')?uri:('/'+uri);
 return app.addParams(u,params);
}

app.urlParam=function(name,uri)
{
 var results=new RegExp('[\?&amp;]'+name+'=([^&amp;#]*)').exec(app.nvl(uri,location.href));
 return results[1]||0;
}

app.urlParams=function(uri)
{
 var paramString=app.nvl(uri,location.href);
 var p=paramString.indexOf('?');
 if(p>=0)
  paramString=paramString.substr(p+1);
 p=paramString.indexOf('#');
 if(p>=0)
  paramString=paramString.substr(0,p);
 var paramPairs=paramString.split('&');
 var params={};
 if(paramPairs)
  for(var i in paramPairs)
  {
   var pair=paramPairs[i].split('=');
   if(pair.length==1)
    params[pair[0]]='';
   else if(pair.length==2)
    params[pair[0]]=pair[1];
  }
  return params;
}

app.checkEmail=function(email)
{
 return /^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i.test(email);
}

app.msg=function(message,title,focus)
{//http://www.linkexchanger.su/2009/95.html
 //http://api.jqueryui.com/dialog/
 var id='dlg-msg';
 var form=$('#'+id);
 if(!form.length)
 {
  form=$('<div id="'+id+'">');
  $('body').append(form);
 }
 if(title)
  form.attr('title',title);
 if(message)
  form.html(message);
 form.dialog({modal:true,resizable:false
 ,buttons:[{text:'OK',click:function(){form.dialog("close");}}]
 ,close:function()
 {
  setTimeout(function()
  {
   form.dialog("destroy");
   form.attr('title','').html('');
   if(focus)
    $(focus).focus().select();
  },1);
 }
 });
}

app.txtUserError=function()
{
 return (app.txt&&app.txt.user_error)?app.txt.user_error:'User error';
}

app.txtServerError=function()
{
 return (app.txt&&app.txt.server_error)?app.txt.server_error:'Server error';
}

//http://stackoverflow.com/questions/2107556/how-to-inherit-from-a-class-in-javascript
//http://javascript.ru/tutorial/object/inheritance#nasledovanie-na-klassah-funkciya-extend
/*app.ServerError=function(message)
{
 var error=new Error(message);
 error.name=app.txtServerError();
 return error;
}*/

app.throwServerError=function(message)
{
 var error=new Error(message);
 error.name=app.txtServerError();
 throw error;
}

app.sync=function(url,data)
{
 var settings={async:false};
 if(data)
  $.extend(settings,{type:'POST',data:data});
 return $.ajax(url,settings);
}

app.ajax=function(url,focus,data)
{
 var xhr=app.sync(url,data);
 if(!xhr)
  return console.log('No XHR');
 if(!('status' in xhr))
  return console.log('No XHR.status: '+app.json(xhr));
 if(xhr.status!=200)
 {
  console.log(xhr.status+': '+xhr.statusText);
  return app.msg(xhr.status+': '+xhr.statusText,app.txtServerError(),focus);
 }
 try
 {
  var res=eval('('+xhr.responseText+')');
  if('warning' in res)
   console.log(res.warning);
  if('error' in res)
   return app.msg(res.error,app.txtUserError());
  if('failure' in res)
   app.throwServerError(res.failure);
  if(!('result' in res))
   app.throwServerError('No result value');
  if(res.result!='OK')
   app.throwServerError('Result value is not OK');
  return res;
 }
 catch(e)
 {
  console.log(xhr.responseText);
  console.log(e.name+': '+e.message);
  return alert(e.message,e.name,focus)
 }
 return null;
}

app.declareACHighlight=function()
{
 $.widget("custom.achighlight",$.ui.autocomplete,
 {
  /*_renderMenu:function(t,n)
  {
   var r=this;
   e.each(n,function(e,n)
   {
    r._renderItemData(t,n)
   })
  },*/
  //_renderItemData:function(e,t){return this._renderItem(e,t).data("ui-autocomplete-item",t)},
  //_renderItem:function(t,n){return e("<li>").append(e("<a>").text(n.label)).appendTo(t)}
  _renderMenu:function(ul,items)
  {
   var self=this;
   $.each(items,function(index,item)
   {
    self._renderItemData(ul,item);
   });
  },
  _renderItem:function(ul,item)
  {
   var li=$('<li>');
   var cat=(this.options.isProc&&(''+item.id).substring(0,1)=='c')||(this.options.isTerr&&!('id' in item));
   if(cat)
    li.addClass('cat');
   var label=item.value;
   if(cat&&this.options.isTerr)
    li.append($('<span class="title">').text(label));
   else
   {
    var term=this.term;
    var len=term.length;
    if(len)
    {
     var pos=0;
     var test=function(ch)
     {
      var re=new RegExp(ch+term,'gi');
      if(!re.exec(label))
       return false;
      pos=re.lastIndex;
      return true;
     }
     if(new RegExp('^'+term,'i').test(label))
      pos=len;
     else
      test(' ')||test('-');
     if(pos>0)
      label=label.substr(0,pos-len)+'<b>'+label.substr(pos-len,len)+'</b>'+label.substr(pos);
    }
    var anchor=$('<a>').append($('<span class="title">').html(label));
    if(cat&&this.options.isProc)
     anchor.append($('<i> ('+app.txt.all_offers+')</i>'))
    li.append(anchor);
   }
   li.appendTo(ul);
   return li;
  }
 });
}

//// Root initialization ////

app.onWindowLoad=function()
{
 //console.log('app: onWindowLoad()');
 if(addEventListener&&history&&history.pushState)
 {
  setTimeout(function()
  {
   addEventListener('popstate',function()
   {
    //console.log('popstate event');
    app.go(''+document.location,false);
   },false);
   app.onWindowResize();
  },1);
 }
}

app.onDocClick=function(e)
{
 if(e.which>1)//IE:0
  return true;
 var node=e.target;
 var node$=$(node);
 /*if((app.page=='book')&&(!node$.hasClass('topw'))&&(node$.parents('.topw').length==0))
 {
  var topw=$('nav .topw:visible');
  if(topw.length)
   topw.hide();
 }*/
 while(node&&(node.tagName!='A')&&(node.tagName!='BODY'))
  node=node.parentNode;
 node$=$(node);
 if(!node||(node.tagName!='A'))
  return true;
 if(node$.hasClass('js'))
  return false;
 if(!node$.hasClass('ax'))
  return true;
 try
 {
  return !app.go(node.href,true);
 }
 catch(e)
 {
  console.log(e.name+': '+e.message);
  app.msg(node.href+'\n'+e.message,'Navigation error: '+e.name);
 }
 return false;
}

$(function()
{
 //console.log('app initialization...');
 $(window)
 .load(app.onWindowLoad)
 .resize(app.onWindowResize)
 .scroll(app.onWindowScroll);

 //Handle taps here? Looks like without jq mobile taps don't work: https://api.jquerymobile.com/tap/
 //$(document).on('tap',function(e){alert("Tap "+event.target.nodeName);e.target.click();e.preventDefault();}).on('click',app.onDocClick);

 $(document).on('click',app.onDocClick);
 //console.log('app initialized');
});

