//// Jump initialization ////

app.go=function(uri,fwd)
{
 //log('app.go('+uri+')');
 if((typeof uri=='object')&&('pro' in uri))
 {
  app.uri=uri;
  uri=uri.href();
 }
 else
 {
  uri=''+(((typeof uri=='string')&&(uri>''))?uri:location.href);
  if(uri.substr(0,4)!='http')
   uri=$('head base').attr('href')+uri;
  app.uri=app.parseUri(uri);
 }
 if(!history||!('pushState' in history))
 {
  if(fwd!==true)
   document.location=uri;
  return false;
 }
 app.uri.params.a='';
 var res=app.ajax(app.uri.href());
 app.setProp(app.uri.params,'a');
 if(!res)
  return false;
 if(fwd!==false)
  history.pushState(document.title,document.title,uri);
 var path=uri.split('/');
 while(path.length&&(path[0]!='com'))
  path.shift();
 path=path.join('/');
 $('header a.lang').each(function(i,item){$(this).attr('href',app.home+$(this).attr('path')+path);});
 $('article').hide();
 app.dataSetup();
 for(key in res.app)
  app[key]=res.app[key];
 app.menuSetup(res.menu,res.menuCtr);
 if('deny' in res)
 {
  app.titleSetup({'title':res.deny,'pretitle':'','subtitle':res.deny});
  return !app.msg(res.deny);
 }
 app.titleSetup(res.app);
 app.tabsSetup(res.tabs);
 app.dataSetup(res.data);
 $('#art-'+app.par).show();
 return true;
}

app.titleSetup=function(data)
{
 document.title=('title' in data)?data.title:'';
 //var ctrT=(data.ctr&&data.ctrs)?data.ctrs[data.ctr]:(app.mode=='all'?app.txt.title_business_profile:'');
 $('header .ctrt').toggle(data.pretitle.length>0);
 $('header .ctrt .name').text(data.pretitle);
 var ul=$('header .ctrt ul');
 ul.text('');
 var uselist=false;
 if(data.ctrs)
 {
  for(var id in data.ctrs)
   ul.append($('<li>').attr('ctr',id)
   .append($('<a>').attr('href','ctr-'+id+'/').addClass('ax ui-state-default').text(data.ctrs[id])));
  if($('li',ul).length<2)
   ul.text('');
  else
   uselist=true;
 }
 $('header .ctrt .name').css('cursor',uselist?'pointer':'default');
 $('header .ctrt .select').toggle(uselist);
 $('header .title').text(('subtitle' in data)?data.subtitle:'');
}

app.titleInit=function()
{
 var ul=$('header .ctrt ul');
 ul.on('click','a',function(){ul.hide();});
 ul.on('mouseenter','a',function(){$(this).addClass('ui-state-active');});
 ul.on('mouseleave','a',function(){$(this).removeClass('ui-state-active');});
 var sel=$('header .ctrt .select');
 $('header .ctrt .name,header .ctrt .select').click(function()
 {
  if(sel.css('display')=='none')
   return;
  ul.toggle();
 });
 var timer=null;
 ul.mouseleave(function()
 {
  if(ul.css('display')=='none')
   return;
  timer=setTimeout(function()
  {
   if(!timer)
    return;
   timer=null;
   if(ul.css('display')=='none')
    return;
   ul.toggle(false);
  },300);
 })
 .mousemove(function()
 {
  if(!timer)
   return;
  clearTimeout(timer);
  timer=null;
 });
}

//// Menu re-initialization ////

app.menuSetup=function(menu,menuCtr)
{
 $('header .menu a.ctr').each(function(i,item)
 {
  item=$(item);
  var id=item.attr('id').substr(5);
  if(id in menu)
   item.attr('href',menu[id]).show();
  else
   item.attr('href','').hide();
 });
 $('#menu-brief .text').text(menuCtr);
 $('header .menu a')
 .removeClass('active')
 .filter('#menu-'+app.par)
 .addClass('active');
 $('header .title').text(app.subtitle);
}

//// Menu initialization ////

app.menuInit=function()
{
 $('header .menu a,header a.lang')
 .mouseenter(function(){$(this).addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')})
 .css('border-right','1px solid '+app.style.colorBgWidgetHeader)
 .filter(':first')
 .css('border-left','1px solid '+app.style.colorBgWidgetHeader);
 //$('header .mode').addClass('ui-widget-header');
}

//// Current article tabs re-initialization ////

app.tabsSetup=function(tabs)
{
 var holder=$('#art-'+app.par+' .tabholder');
 if(!holder.length)
  return;
 if(tabs)
  for(var id in tabs)
   if('href' in tabs[id])
    $('.tags .tab-'+id+',.taps .tab-'+id,holder).attr('href',tabs[id].href);
 $('.tab',holder)
 .removeClass('active')
 .filter('.tab-'+app.tab)
 .addClass('active');
 $('.tags .tab,.taps .tab',holder)
 .removeClass('js ui-state-active')
 .filter('.tab-'+app.tab)
 .addClass('js ui-state-active');
 $('.tabs .tab',holder)
 .hide()
 .filter('.tab-'+app.tab)
 .show();
}

//// Tabs initialization ////

app.tabsInit=function()
{
 $('article .tabholder .tags').css('border-top-width','0');
 $('article .tabholder .tags .tab')
 .mouseenter(function(){$(this).not('.active').addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')});
 $('article .side').addClass('ui-widget-header ui-corner-left');
 $('article .side .pane').addClass('ui-widget-content ui-corner-left');
 $('article .action').addClass('ui-widget-content');
 $('article .frame').addClass('ui-widget-header');
 $('article .frame>*').addClass('ui-widget-content');
 $('#art-mtr .tabs .tab-privs .priv input').click(app.actionEditMasterPrivs);
}

//// Block initialization ////

app.blockInit=function(tag)
{
 $('.block',tag)
 .css('color',app.style.colorFgWidgetContent)
 .css('border-color',app.style.colorBgWidgetContent)
 .css('background-color',app.style.colorBgWidgetContent)
 .mouseenter(function(){$(this).css('background-color',app.style.colorBgWidgetHeader)})
 .mouseleave(function(){$(this).css('background-color',app.style.colorBgWidgetContent)});
 $('.block a[href]:not(.button)',tag)
 .mouseenter(function(){$(this).css('color',app.style.colorFgStateError)})
 .mouseleave(function(){$(this).css('color',app.style.colorFgWidgetContent)});
}

app.blockActionBtnClick=function(node,idattr)
{
 var block=node.parents('.block');
 var title=$('.head .title',block).text();
 var prompt=node.text();
 if (title)
  prompt+=' "'+title+'"';
 prompt+='?';
 app.dlgConfirmShow(app.subtitle,prompt,function()
 {
  var uri=app.makeURI('action='+encodeURIComponent(node.attr('action')));
  if(idattr)
   uri+='&'+idattr+'='+encodeURIComponent(block.attr(idattr));
  var res=app.ajax(uri);
  if(!res)
   return false;
  app.go(res.uri);
  return true;
 });
}

//// Table initialization ////

app.tableSetupCellBtns=function(btns)
{
 btns.addClass('ui-state-default ui-corner-all')
 .mouseenter(function(){$(this).addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')});
 //btns.filter('.img-time:empty').text('(o)');
 //btns.filter('.img.money:empty').text('($)');
 btns.filter(':empty').not('.img').text('...');
}

app.tableSetupDataCells=function(cells)
{
 $('a',cells)
 .css('border','1px').css('border-color',app.style.colorBgWidgetContent)
 .mouseenter(function(){$(this).filter('[href]').addClass('ui-state-highlight')})
 .mouseleave(function(){$(this).filter('[href]').removeClass('ui-state-highlight')});
 app.tableSetupCellBtns($('.btn',cells));
}

app.tableSetupCells=function(rows)
{
 $('th,td',rows).addClass('ui-widget-content').css('border-width','1px')
 app.tableSetupDataCells($('.data',rows));
}

app.tableSetup=function(art)
{
 app.tableSetupCells($('.list tr:has(td)',art));
}

app.tableInit=function()
{
 var tables=$('.list,.form');
 app.tableSetupCells(tables);
 tables//.addClass('ui-widget-header')
 .click(function(e)
 {
  var node=$(e.target);
  if(!node.hasClass('btn'))
   return true;
  if(node.filter('[action]').length)
   return app.tableActionBtnClick(node);
  if(node.parent().hasClass('edit'))
   return app.tableEditBtnClick(node);
  return true;
 });
 //$('#art-brief .tabs .tab-def .list').addClass('small');
}

app.tableActionBtnClick=function(btn)
{
 var action=btn.attr('action');
 var table=btn.parents('.list,.form');
 var row=table.hasClass('list')?btn.parents('[rowid]'):null;
 if(row&&!row.length)
  row=null;
 var rowid=row?row.attr('rowid'):'';
 var prompt=btn.text();
 if(rowid)
 {
  var object=app.str(table.find('.prompt[action='+action+']').attr('object'));
  if(object.length)
   prompt+=' '+object;
  var name=row.find('.data[field=name] a').text();
  if(!name.length)
   name=row.find('.data a').filter(':first').text();
  if(name.length)
   prompt+=' "'+name+'"';
 }
 prompt+='?';
 app.dlgConfirmShow(app.subtitle,prompt,function()
 {
  var uri=app.makeURI('action='+encodeURIComponent(action));
  if(rowid)
   uri+='&rowid='+encodeURIComponent(rowid);
  var res=app.ajax(uri);
  if(!res)
   return false;
  app.go(res.uri);
  return true;
 });
}

app.dataCellSetValue=function(hth,cell,a,value)
{
 if(!(''+value).length)
  a.text('').filter('[href]').removeAttr('href').removeAttr('target');
 else if(hth.hasClass('select'))
  app.dataSetupSelect(cell,value,true);
 else if(cell.hasClass('bool'))
  a.attr('checked',!!value).text(value?app.txt.value_true:app.txt.value_false)
 else
  a.text(value);
 if(cell.hasClass('uri'))
  a.attr('href',value.uri?value.uri:value.text?value.text:value);
}

app.dataEditBtnClick=function(hth,cell,a,rowid,field,prompt)
{
 var values={'name':a.text().trim()};
 if(cell.hasClass('optional'))
  values.optional=true;
 if(cell.filter('[name]').length)
 {
  values.name=cell.attr('name');
  var titles={};
  for(lang in app.langs)
   app.setProp(titles,lang,cell.attr('title-'+lang));
  values.titles=titles;
 }
 else if(hth.hasClass('abc'))
 {
  values={'titles':{}};
  for(lang in app.langs)
   app.setProp(values.titles,lang,cell.attr('title-'+lang));
 }
 else if(hth.hasClass('bnd'))
  values={'bnd':cell.attr('objid')};
 else if(hth.hasClass('level'))
  values={'level':cell.attr('objid')};
 else if(hth.hasClass('role'))
  values={'role':cell.attr('objid')};
 else if(hth.hasClass('prc'))
  values={'prc':{'id':cell.attr('objid'),'title':a.text()}};
 else if(hth.hasClass('curr'))
  values={'curr':{'id':cell.attr('objid'),'title':a.text()}};
 else if(cell.hasClass('bool'))
  values.name=!!a.attr('checked');
 app.dlgInputShow(app.subtitle,prompt,values,function(newvalues)
 {
  if((field=='name')&&('name' in values)&&(!('name' in newvalues)||!newvalues.name.length))
   return false;
  var uri=app.makeURI('action=modify');
  if(rowid)
   uri+='&rowid='+encodeURIComponent(rowid);
  uri+='&field='+encodeURIComponent(field);
  if('bnd' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.bnd);
  else if('level' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.level);
  else if('role' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.role);
  else if('prc' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.prc.id);
  else if('curr' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.curr.id.substr(0,3));
  else if('name' in newvalues)
   uri+='&value='+encodeURIComponent(newvalues.name);
  if('titles' in newvalues)
   for(lang in newvalues.titles)
    uri+='&title-'+lang+'='+encodeURIComponent(newvalues.titles[lang]);
  var res=app.ajax(uri);
  if(!res)
   return false;
  if('uri' in res)
   return app.go(res.uri);
  if('title' in res)
   app.titleSetup(res);
  if(('titles' in newvalues))
   app.dataSetupLangName(cell,res);
  else
   app.dataCellSetValue(hth,cell,a,('value' in res)?res.value:'');
  if(!rowid&&('values' in res))
   for(n in res.values)
   {
    var cell2=cell.parents('.form').first().find('.data[field="'+n+'"]');
    app.dataCellSetValue(cell2,cell2,$('a',cell2),res.values[n]);
   }
  return true;
 });
 return false;
}

app.tableEditBtnClick=function(btn)
{
 var cell=btn.parent();
 var a=$('a',cell);
 var table=btn.parents('.list,.form');
 var field=cell.attr('field');
 var block=null,row=null,rowid=null;
 if(table.hasClass('list'))
 {
  row=btn.parents('[rowid]').first();
  rowid=row.length?row.attr('rowid'):'';
 }
 else
 {
  block=btn.parents('.block[rowid]').first()
  rowid=block.length?block.attr('rowid'):'';
 }
 var hth=table.find('.prompt[field="'+field+'"]');
 var prompt=hth.text();
 if(row&&row.length)
 {
  if(field!='name')
  {
   var name=row.find('.data[field="name"] a').text();
   if(!name.length)
    name=row.find('.data a').filter(':first').text();
   if(name.length)
    prompt='"'+name+'": '+prompt;
  }
  prompt+=':';
 }
 else
  hth=cell;
 return app.dataEditBtnClick(hth,cell,a,rowid,field,prompt)
}

app.actionCreateWithName=function(button,extvalues)
{
 var action=button.attr('action');
 if(!action)
  action=button.parent().attr('action');
 if(!action)
  action='create';
 values={'name':'','titles':{}};
 if(extvalues)
  for(n in extvalues)
   values[n]=extvalues[n];
 app.dlgInputShow(app.subtitle,button.text(),values,function(newvalues)
 {
  if(!newvalues.name.length)
   return false;
  var uri=app.makeURI('action='+action);
  uri+='&name='+encodeURIComponent(newvalues.name);
  for(lang in newvalues.titles)
   uri+='&title-'+lang+'='+encodeURIComponent(newvalues.titles[lang]);
  if(extvalues)
   for(n in extvalues)
    uri+='&'+n+'='+encodeURIComponent(newvalues[n]);
  var res=app.ajax(uri);
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.actionDeleteCurrent=function(prompt)
{
 app.dlgConfirmShow(app.subtitle,prompt+'?',function()
 {
  var res=app.ajax(app.makeURI('action=delete'));
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.onSendMessage=function(subject,message)
{
 if(!subject.length||!message.length)
  return false;
 var params='action=send';
 params+='&subject='+encodeURIComponent(Base64.encode(subject));
 params+='&message='+encodeURIComponent(Base64.encode(message));
 var res=app.ajax(app.makeURI(params));
 if(!res||!res.msgid)
  return false;
 setTimeout(function(){app.go('msg-'+res.msgid+'/');},1);
 return true;
}

app.actionCreateMessage=function(button)
{
 app.dlgMessageShow(button.text(),'',app.onSendMessage);
}

app.actionAnswerMessage=function(button)
{
 var subject=$('.subject',button.parents('article')).text();
 subject=(subject.substr(0,4)=='Re: ')?subject:('Re: '+subject);
 app.dlgMessageShow(button.text(),subject,app.onSendMessage);
}

app.actionDeleteMessage=function(e)
{
 var button=$(e.target);
 var msg=button.parents('.msg');
 var title=$('.title',msg).text()+' ('+$('.sent',msg).text()+')'
 app.dlgConfirmShow(title,button.text()+'?',function()
 {
  var params='action=delete';
  params+='&msgid='+encodeURIComponent(msg.attr('rowid'));
  var res=app.ajax(app.makeURI(params));
  if(!res)
   return false;
  setTimeout(function(){app.go();},1);
  return true;
 });
}

app.actionAddPhoneNumber=function(prompt)
{
 app.dlgInputShow(prompt,'',{'name':''},function(newvalues)
 {
  if(!('name' in newvalues)||!newvalues.name.length)
   return false;
  var res=app.ajax(app.makeURI('action=addtel&phone='+encodeURIComponent(newvalues.name)));
  if(res)
   app.go();
  return !!res;
 });
}

app.actionCreateService=function(button)
{
 var values={'prc':{},'title':'','titles':{},'level':'','tip':{}};
 app.dlgInputShow(button.text(),'',values,function(newvalues)
 {
  if(!newvalues.prc)
   return false;
  if(!newvalues.title||!newvalues.title.length)
   newvalues.title=newvalues.prc.title;
  var uri=app.makeURI('action=addsrv');
  uri+='&grp='+encodeURIComponent(button.parents('.grp[grpid]').attr('grpid'));
  uri+='&prc='+encodeURIComponent(newvalues.prc.id);
  if(newvalues.level)
   uri+='&level='+encodeURIComponent(newvalues.level);
  uri+='&name='+encodeURIComponent(newvalues.title);
  for(lang in newvalues.titles)
   uri+='&title-'+lang+'='+encodeURIComponent(newvalues.titles[lang]);
  for(key in newvalues.tip)
   uri+='&'+key+'='+encodeURIComponent(newvalues.tip[key]);
  var res=app.ajax(uri);
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.actionCreatePackage=function(button)
{
 var values={'prcs':[],'name':'','titles':{},'level':'','tip':{}};
 app.dlgInputShow(button.text(),app.txt.prompt_package_name,values,function(newvalues)
 {
  if(!newvalues.name.length||!newvalues.prcs.length)
   return false;
  var uri=app.makeURI('action=addpkg');
  uri+='&grp='+encodeURIComponent(button.parents('.grp[grpid]').attr('grpid'));
  uri+='&name='+encodeURIComponent(newvalues.name);
  uri+='&prcs='+encodeURIComponent(newvalues.prcs.join());
  if(newvalues.level)
   uri+='&level='+encodeURIComponent(newvalues.level);
  for(lang in newvalues.titles)
   uri+='&title-'+lang+'='+encodeURIComponent(newvalues.titles[lang]);
  for(key in newvalues.tip)
   uri+='&'+key+'='+encodeURIComponent(newvalues.tip[key]);
  var res=app.ajax(uri);
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.actionCreatePriceOption=function(prompt)
{
 app.dlgInputShow(prompt,'',{/*'level':'',*/'title':'','titles':{},'tip':{}},function(newvalues)
 {
  if(!('tip' in newvalues)||!('duration' in newvalues.tip)||!('price' in newvalues.tip))
   return false;
  var uri=app.makeURI('action=create');
  if(newvalues.level)
   uri+='&level='+encodeURIComponent(newvalues.level);
  if(newvalues.title)
   uri+='&title='+encodeURIComponent(newvalues.title);
  for(lang in newvalues.titles)
   uri+='&title-'+lang+'='+encodeURIComponent(newvalues.titles[lang]);
  for(key in newvalues.tip)
   uri+='&'+key+'='+encodeURIComponent(newvalues.tip[key]);
  var res=app.ajax(uri);
  if(res)
   app.go();
  return !!res;
 });
}

app.actionAddProcedure=function(prompt)
{
 app.dlgInputShow(prompt,'',{'prc':{}},function(newvalues)
 {
  console.log(newvalues);
  if(!(('prc')in newvalues)||!newvalues.prc.id)
   return false;
  var res=app.ajax(app.makeURI('action=addprc&prc='+encodeURIComponent(newvalues.prc.id)));
  if(res)
   app.go();
  return !!res;
 });
}

app.actionAddSrvMaster=function(prompt)
{
 app.dlgInputShow(prompt,'',{'mtr':''},function(newvalues)
 {
  if(!(('mtr')in newvalues)||!newvalues.mtr)
   return false;
  var res=app.ajax(app.makeURI('action=addmtr&mtr='+encodeURIComponent(newvalues.mtr)));
  if(res)
   app.go();
  return !!res;
 });
}

app.actionCreateMaster=function(prompt)
{
 app.dlgInputShow(prompt,'',{'email':''},function(newvalues)
 {
  if(!(('email')in newvalues)||!newvalues.email.length||!app.checkEmail(newvalues.email))
   return false;
  var res=app.ajax(app.makeURI('action=add&email='+encodeURIComponent(newvalues.email)));
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.actionCreateMasterNew=function(prompt)
{
 app.dlgInputShow(prompt,null,{'firstname':'','lastname':'','email':''},function(newvalues)
 {
  if(!(('firstname')in newvalues)||!newvalues.firstname.length)
   return false;
  if(!(('lastname')in newvalues)||!newvalues.lastname.length)
   return false;
  if(!(('email')in newvalues)||!newvalues.email.length||!app.checkEmail(newvalues.email))
   return false;
  var res=app.ajax(app.makeURI('action=create&firstname='+encodeURIComponent(newvalues.firstname)+'&lastname='+encodeURIComponent(newvalues.lastname)+'&email='+encodeURIComponent(newvalues.email)));
  if(res)
   app.go(res.uri);
  return !!res;
 });
}

app.actionEditMasterPrivs=function()
{
 var privs=[];
 var inputs=$('#art-mtr .tabs .tab-privs .priv input');
 inputs.each(function(i,item)
 {
  if(item.checked)
   privs.push(parseInt($(item).attr('priv')));
 })
 app.dlgMasterPrivs(app.subtitle,privs,function(newprivs)
 {
  var res=app.ajax(app.makeURI('action=modify&field=privs&value='+newprivs.join()));
  if(res)
   app.go();
  return !!res;
 });
 return false;
}

app.buttonsTextAreaSetup=function(viewer)
{
 var empty=!$('.text.active',viewer).html().length;
 $('.action .button',viewer).each(function(i,item){$(item).toggle(empty==$(this).hasClass('add'));});
}

app.actionTextAreaLang=function(item)
{
 var editing=app.editing;
 app.actionEditorClose();
 var tab=item.parents('.frame');
 $('.viewer .text.active',tab).removeClass('active').hide();
 $('.viewer .text[lang="'+item.attr('lang')+'"]',tab).addClass('active').show();
 $('.item',item.parent()).removeClass('active ui-state-active');
 item.addClass('active ui-state-active');
 if(editing)
  app.actionEditorOpen(editing.viewer,editing.editor);
 else
  app.buttonsTextAreaSetup($('.viewer',tab));
}

app.actionTextAreaEditorOpen=function(button)
{
 var tab=button.parents('.frame');
 app.actionEditorOpen($('.viewer',tab),$('.editor',tab));
}

app.actionEditorOpen=function(viewer,editor)
{
 app.editing={'viewer':viewer,'editor':editor};
 $('.text',editor).val($('.text:visible',viewer).html().replaceAll('<br>','\n'));
 viewer.hide();
 editor.show().find('.auto-focus').focus();
}

app.actionEditorClose=function()
{
 if(!app.editing)
  return;
 app.editing.viewer.show();
 app.buttonsTextAreaSetup(app.editing.viewer);
 app.editing.editor.hide().find('.text').val('');
 app.editing=null;
}

app.actionEditorSave=function()
{
 if(!app.editing)
  return;
 var lang=$('.text.active',app.editing.viewer).attr('lang');
 var text=$('.text',app.editing.editor).val().safeHTML();
 var res=app.ajax(app.makeURI({action:'save',lang:lang,text:text}));
 if(!res)
  return;
 app.setTextHTML($('.text.active',app.editing.viewer),(res.text&&res.text.length)?
  Base64.decode(res.text):'');
 app.actionEditorClose();
}

app.actionLogoUpload=function(pane)
{
 var div=pane.parents('.image');
 if(div.length!=1)
  return;// console.log('app.actionImageUpload(): div.length='+div.length);
 app.dlgFileShow(app.txt.title_upload_image,app.txt.prompt_select_image_file,function(file)
 {
  var data=new FormData();
  data.append('image',file);
  var res=$.ajax(
  {
   url: app.makeURI('action=upload'),
   type: 'post',
   data: data,
   async: false,
   cache: false,
   dataType: 'json',
   processData: false, // Don't process the files
   contentType: false  // Set content type to false as jQuery will tell the server its a query string request
  });
  if(res.readyState!=4)
   return console.log('Invalid res.readyState: '+res.readyState);
  if(!res.responseJSON)
   return console.log('Invalid res.responseText: '+res.responseText);
  if(res.responseJSON.result!='OK')
   return console.log('Invalid res.responseJSON.result: '+res.responseJSON.result);
  if(!res.responseJSON.data)
   return console.log('Invalid res.responseJSON: no data in response');
  if(!res.responseJSON.data.logo)
   return console.log('Invalid res.responseJSON: no logo in response.data');
  app.dataSetupAnyLogo(div.parent(),res.responseJSON.data.logo)
  return true;
 });
}

app.actionLogoClear=function(pane)
{
 var div=pane.parents('.image');
 if(div.length!=1)
  return console.log('app.actionImageClear(): div.length='+div.length);
 prompt='Delete logo';
 app.dlgConfirmShow(app.subtitle,prompt+'?',function()
 {
  var res=app.ajax(app.makeURI('action=clear'));
  if(res)
   app.dataSetupAnyLogo(div.parent())
  return !!res;
 });
}

app.actionImageUpload=function(pane)
{
 var div=pane.parents('.image[rowid]');
 if(div.length!=1)
  return;// console.log('app.actionImageUpload(): div.length='+div.length);
 var rowid=div.attr('rowid');
 app.dlgFileShow(app.txt.title_upload_image,app.txt.prompt_select_image_file, function(file)
 {
  var data=new FormData();
  data.append('image',file);
  var res=$.ajax(
  {
   url: app.makeURI('action=upload&rowid='+rowid),
   type: 'post',
   data: data,
   async: false,
   cache: false,
   dataType: 'json',
   processData: false, // Don't process the files
   contentType: false  // Set content type to false as jQuery will tell the server its a query string request
  });
  if(res.readyState!=4)
   return console.log('Invalid res.readyState: '+res.readyState);
  if(!res.responseJSON)
   return console.log('Invalid res.responseText: '+res.responseText);
  if(res.responseJSON.result!='OK')
   return console.log('Invalid res.responseJSON.result: '+res.responseJSON.result);
  if(!res.responseJSON.data)
   return console.log('Invalid res.responseJSON: no data in response');
  if(!res.responseJSON.data.img)
   return console.log('Invalid res.responseJSON: no img in response.data');
  app.dataSetupAnyImg(div.parent(),rowid,res.responseJSON.data.img)
  return true;
 })
}

app.actionImageClear=function(pane)
{
 var div=pane.parents('.image[rowid]');
 if(div.length!=1)
  return console.log('app.actionImageClear(): div.length='+div.length);
 var rowid=div.attr('rowid');
 prompt='Delete image';
 app.dlgConfirmShow(app.subtitle,prompt+'?',function()
 {
  var res=app.ajax(app.makeURI('action=clear&rowid='+rowid));
  if(res)
   app.dataSetupAnyImg(div.parent(),rowid)
  return !!res;
 });
}

app.descrSetup=function(pane)
{
 $('.viewer .text.active',pane).removeClass('active').hide();
 $('.viewer .text.def',pane).addClass('active').show();
}

app.buttonInitStyle=function(pane)
{
 $('.button',pane).addClass('ui-state-default ui-corner-all')
 .mouseenter(function(){$(this).addClass('ui-state-hover').filter('.active').removeClass('ui-state-active')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover').filter('.active').addClass('ui-state-active')})
}

app.buttonSetup=function(pane)
{
 $('.button',pane).removeClass('active ui-state-active')
 .filter('.def').addClass('active ui-state-active');
}

app.buttonInit=function()
{
 app.buttonInitStyle($('article'));
 $('#art-home .tab-msgs .button[action="newmsg"]').click(function(){app.actionCreateMessage($(this));});
 $('#art-msg .button[action="answer"]').click(function(){app.actionAnswerMessage($(this));});
 $('#art-msg .button[action="delete"]').click(function(){app.actionDeleteCurrent($(this).text());});
 $('#art-ctrs .button.create').click(function(){app.actionCreateWithName($(this));});
 $('#art-brief .tab-def .button.delctr').click(function(){app.actionDeleteCurrent($(this).text());});
 $('#art-brief .tab-def .button[action="addtel"]').click(function(){app.actionAddPhoneNumber($(this).text());});
 $('#art-brief .tab-lvls .button.create').click(function(){app.actionCreateWithName($(this));});
 $('#art-srvs .button[action="addgrp"]').click(function(){app.actionCreateWithName($(this));});
 $('#art-srv .tab-def .button.delete').click(function(){app.actionDeleteCurrent($(this).text());});
 $('#art-srv .tab-def .button.addprc').click(function(){app.actionAddProcedure($(this).text());});
 $('#art-srv .tab-tips .button.create').click(function(){app.actionCreatePriceOption($(this).text());});
 $('#art-srv .tab-mtrs .button.addmtr').click(function(){app.actionAddSrvMaster($(this).text());});
 $('#art-mtrs .button.add').click(function(){app.actionCreateMaster($(this).text());});
 $('#art-mtrs .button.create').click(function(){app.actionCreateMasterNew($(this).text());});
 $('#art-mtr .tab-def .button.delete').click(function(){app.actionDeleteCurrent($(this).text());});
 $('#art-bnds .button.create').click(function(){app.actionCreateWithName($(this),{'uri':''});});
 $('#art-bnd .tab-def .button.delete').click(function(){app.actionDeleteCurrent($(this).text());});
 $('#art-bnd .tab-lvls .button.create').click(function(){app.actionCreateWithName($(this));});
 $('article .frame.text .menu .item.button').click(function(){app.actionTextAreaLang($(this));});
 $('article .frame.text .viewer .button').click(function(){app.actionTextAreaEditorOpen($(this));});
 $('article .frame.text .button.cancel').click(function(){app.actionEditorClose();});
 $('article .frame.text .button.save').click(function(){app.actionEditorSave();});
 $('article .tab-def .button.upload').click(function(){app.actionLogoUpload($(this));});
 $('article .tab-def .button.clear').click(function(){app.actionLogoClear($(this));});
 $('article .tab-imgs .button.upload').click(function(){app.actionImageUpload($(this));});
 $('article .tab-imgs .button.clear').click(function(){app.actionImageClear($(this));});
}

app.styleInit=function()
{
 var Style=$('nav .style');
 app.style={};
 app.style.colorBgWidgetHeader=$('.ui-widget-header',Style).css('background-color');
 app.style.colorFgWidgetHeader=$('.ui-widget-header',Style).css('color');
 app.style.colorBgWidgetContent=$('.ui-widget-content',Style).css('background-color');
 app.style.colorFgWidgetContent=$('.ui-widget-content',Style).css('color');
 app.style.colorFgStateError=$('.ui-state-error',Style).css('color');
}

//// Data re-initialization ////

app.dataSetup=function(data)
{
 if(!app.par.length)
  return;
 var func='dataSetup'+app.par.ucfirst();
 var slct='#art-'+app.par;
 if(app.tab.length)
 {
  func+=app.tab.ucfirst();
  slct+=' div.tab.tab-'+app.tab;
 }
 var pane=$(slct);
 if(func in app)
  app[func](pane,data);
 if(data)
 {
  app.dataSetupEdit(pane,data.edit===true);
 }
}

app.dataSetupEdit=function(tag,edit)
{
 $('.action.edit',tag).toggle(edit);
 $('.data.edit',tag).toggleClass('able',edit);
 $('.list th.prompt.btn[action]',tag).toggle(edit);
 $('.list td:has(.btn[action])',tag).toggle(edit);
 $('.form tr.edit:not(.hidden)',tag).toggle(edit);
}

app.dataSetupSelect=function(cell,data,edit)
{
 if(typeof(data)=='object')
  cell.removeClass('right').attr('objid',data.id).find('a').text(data.text).prev().toggle(edit);
 else
  cell.addClass('right').removeAttr('objid').find('a').text(data).prev().hide();
}

app.dataSetupBool=function(a,data,edit)
{
 if(data=='1')
  a.attr('checked',true).text(app.txt.value_true).prev().toggle(edit);
 else if((data===null)||(data===false)||(data===''))
  a.attr('checked',false).text(app.txt.value_false).prev().toggle(edit);
 else
  a.removeAttr('checked').text(data).prev().hide();
}

app.dataSetupLangName=function(cell,data)
{
 var at,usename=('name' in data);
 if(usename)
  cell.attr('name',data.name);
 for(lang in app.langs)
 {
  at='title-'+lang;
  if(at in data)
   cell.attr(at,data[at]);
  else
   cell.removeAttr(at);
 }
 at='title-'+app.lang;
 var text=$('a',cell);
 if(!text.length)
  text=cell;
 text.text((at in data)?data[at]:usename?data.name:'');
}

app.dataSetupAnyList=function(data,key,table,nodata)
{
 var tbody=$('tbody',table);
 tbody.children().remove('tr:has(td)');
 if(!data)
  return;
 var rows=data[key];
 var empty=!rows||!rows.length;
 if(nodata)
  nodata.toggle(empty);
 table.parent().filter('.block').toggle(!empty);
 table.toggle(!empty);
 if(empty)
  return;
 var hths=$('tr:first th',tbody);
 $.each(rows,function(i,row)
 {
  var tr=$('<tr>');
  if(row.id)
   tr.attr('rowid',row.id);
  $.each(hths,function(j,hth)
  {
   hth=$(hth);
   var field=hth.attr('field');
   var td=$('<td>').attr('field',field).addClass('data');
   if(field)
   {
    if(hth.hasClass('edit'))
     td.addClass('edit').append($('<div class="btn">'));
    if(hth.hasClass('img'))
    {
     $('.btn',td).addClass('img');
     if(hth.hasClass('time'))
      $('.btn',td).addClass('time').append($('<span class="ui-icon ui-icon-clock">'));
     if(hth.hasClass('money'))
      $('.btn',td).addClass('money').append($('<span class="ui-icon ui-icon-calculator">'));
     $('.img span.ui-icon',td).click(function(){$(this).parent().click();});
    }
    if(hth.hasClass('nw'))
     td.addClass('nw');
    if(hth.hasClass('right'))
     td.addClass('right');
    else if(hth.hasClass('center'))
     td.addClass('center');
    td.append($('<a>'));
    var a=$('a',td);
    if(hth.hasClass('bg'))
    {
     var bg=app.str(hth.attr('bg'));
     if(row[bg])
      a.css('background-color','#'+row[bg]);
    }
    var text=row[field];
    if(text&&(typeof(text)=='object')&&('name' in text))
     app.dataSetupLangName(td,text);
    else if(hth.hasClass('select'))
     app.dataSetupSelect(td,text,true);
    else
    {
     var text=app.str(text);
     a.text(text);
     if(text.length&&hth.hasClass('uri'))
     {
      var uri=app.str(hth.attr('uri'));
      uri=uri.length?app.str(row[uri]):text;
      if(uri&&uri.length)
      {
       a.attr('href',uri);
       if(hth.hasClass('ax'))
        a.addClass('ax');
       else if(hth.hasClass('ext'))
        a.attr('target','_blank');
      }
     }
     if(hth.hasClass('databtn')&&(hth.attr('databtn-text')in row))
     {
      text=app.str(row[hth.attr('databtn-text')]);
      $('<div class="databtn">').attr('style',hth.attr('databtn-style')).text(text).insertBefore(a);
     }
    }
   }
   else if(hth.hasClass('btn'))
   {
    var action=hth.attr('action');
    if(action)
     td.append($('<div class="btn">').attr('action',action).text(app.txt['button_'+action]));
   }
   tr.append(td);
  });
  tbody.append(tr);
 });
}

app.dataSetupAnyForm=function(form,data)
{
 $('.data a',form).text('')
 .filter('[href]').removeAttr('href').removeAttr('target').removeClass('ui-state-highlight');
 if(!data||!data.values)
  return;
 for(var name in data.values)
 {
  var text=data.values[name];
  var a=$('.data[field='+name+'] a',form);
  if(!a.length)
   continue;
  var cell=a.parent();
  if(text&&(typeof(text)=='object')&&('name' in text))
   app.dataSetupLangName(cell,text);
  else
  {
   if(cell.hasClass('bool'))
    app.dataSetupBool(a,text,!!data.edit);
   else if(cell.hasClass('select'))
    app.dataSetupSelect(cell,text,!!data.edit);
   else
    a.text(app.nvl(text.text,text));
   if(cell.hasClass('uri'))
   {
    var uri=app.nvl(text.uri,text);
    if(uri.length)
    {
     a.attr('href',uri);
     if(cell.hasClass('ax'))
      a.addClass('ax')
     else if(cell.hasClass('ext'))
      a.attr('target','_blank');
    }
   }
  }
 }
}

app.dataSetupAnyTextArea=function(tab,data)
{
 if(data)
 {
  app.buttonSetup(tab);
  app.descrSetup(tab);
 }
 else
  app.actionEditorClose(tab);
 $('.viewer .text',tab).each(function(i,view)
 {
  view=$(view);
  var lang=view.attr('lang');
  app.setTextHTML(view,(data&&data.text&&(lang in data.text))?Base64.decode(data.text[lang]):'');
 });
 app.buttonsTextAreaSetup($('.viewer',tab));
}

app.dataSetupAnyPic=function(div,data)
{
 if(div.length!=1)
 {
  console.log('Invalid div.length: '+div.length);
  return;
 }
 app.dataSetupAnyForm($('.form',div),{'values':data});
 if(data&&('uri' in data)&&data.uri.length)
 {
  $('.thumb img',div).attr('src',data['uri']);
  $('.form .hide',div).removeClass('hidden').show();
 }
 else
 {
  $('.thumb img',div).removeAttr('src');
  $('.form .hide',div).addClass('hidden').hide();
 }
}

app.dataSetupAnyLogo=function(tab,data)
{
 app.dataSetupAnyPic($('.image',tab),data);
}

app.dataSetupAnyImg=function(tab,rowid,data)
{
 app.dataSetupAnyPic($('.image[rowid="'+rowid+'"]',tab),data);
}

app.dataSetupHomeMsgs=function(tab,data)
{
 var noData=!data||!data.msgs||!data.msgs.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.msgs',tab);
 blocks.html('');
 if(noData)
  return;
 for(i in data.msgs)
 {
  var msg=data.msgs[i];
  var block=$('<div>').attr('rowid',msg.id)
  .addClass('block msg '+((msg.dir=='o')?'out':'in')+' ui-widget-content')
  .append($('<div>').addClass('title')
   .append($('<a>').addClass('ax').attr('href','msg-'+msg.id+'/').text(msg.subject))
  )
  .append($('<div>').addClass('action')
   .append($('<div>').addClass('sent').text(msg.sent))
   .append($('<div>').addClass('button right').attr('action','delete').text(app.txt.button_delmsg))
  );
  blocks.append(block);
 }
 app.buttonInitStyle(blocks);
 app.blockInit(blocks);
 $('.button[action="delete"]',blocks).click(app.actionDeleteMessage);
}

app.dataSetupMsg=function(tab,data)
{
 $('.button[action="answer"]',tab).toggle(data&&data.incoming);
 $('.subject',tab).html((data&&data.subject&&data.subject.length)?Base64.decode(data.subject):'');
 $('.viewer .text',tab).html((data&&data.message&&data.message.length)?Base64.decode(data.message):'');
}

app.dataSetupHomePmts=function(tab,data)
{
 app.dataSetupAnyList(data,'pmts',$('.list',tab),$('.no-data',tab));
 app.tableSetup(tab);
}

app.dataSetupHomeOfrs=function(tab,data)
{
 var noData=!data||!data.ofrs||!data.ofrs.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.ofrs',tab);
 blocks.html('');
 if(noData)
  return;
 app.createBlocksOfrs(blocks,data.ofrs);
}

// Calendar

app.cdrFilterInit=function()
{
 // Date
 var wDate=$('#art-cdr .side .date .widget');
 var dp=$('.dp',wDate);
 dp.datepicker(
 {
  firstDay:app.cdr.fday
 ,onSelect:function()
 {
  app.uri.params.date=$(this).datepicker('getDate').toString();
  var ani=wDate.attr('ani');
  wDate.attr('ani','0');
  app.go(app.uri);
  wDate.attr('ani',ani);
 }});
 var ctrl=$('#art-cdr .side .date .ctrl');
 $('.empty',wDate).click(function()
 {
  app.uri.params.date=app.cdr.filter.date||new Date();
  app.go(app.uri);
  ctrl.slideDown();
 });
 $('.clear',wDate).click(function()
 {
  app.setProp(app.uri.params,'date');
  app.go(app.uri);
 });
 $('.using .value',wDate).click(function(){if(wDate.attr('ani')=='1')ctrl.stop().slideToggle();});
 $('#art-cdr .tags').mouseleave(function(){if(wDate.attr('ani')=='1')ctrl.stop().slideUp();});
 $('.pin-on',ctrl).click(function(){wDate.attr('ani','1');});
 $('.pin-off',ctrl).click(function(){wDate.attr('ani','0');});
 $('.prev',ctrl).click(function()
 {
  var date=dp.datepicker('getDate');
  date.setDate(date.getDate()-((app.tab=='def')?7:1));
  app.uri.params.date=date.toString();
  app.go(app.uri);
 });
 $('.next',ctrl).click(function()
 {
  var date=dp.datepicker('getDate');
  date.setDate(date.getDate()+((app.tab=='def')?7:1));
  app.uri.params.date=date.toString();
  app.go(app.uri);
 });
 $('.today',ctrl).click(function()
 {
  var date=new Date();
  app.uri.params.date=date.toString();
  app.go(app.uri);
 });
 // Time
 //app.cdrFilterShowTime('','');
 var seltmin=$('#dlg-booking-time .tmin select');
 var seltmax=$('#dlg-booking-time .tmax select');
 app.cdr.times['']=app.txt.title_booking_any_time;
 seltmin.append($('<option>').attr('value','').text(app.txt.title_booking_any_time));
 seltmax.append($('<option>').attr('value','').text(app.txt.title_booking_any_time));
 for(var i=480;i<1920;i+=30)
 {
  var t=i%1440;
  var text=app.min2str(t);
  app.cdr.times[''+t]=text;
  seltmin.append($('<option>').attr('value',t).text(text));
  seltmax.append($('<option>').attr('value',t).text(text));
 }
 var wTime=$('#art-cdr .side .time .widget');
 $('.empty,.value',wTime).click(function()
 {
  seltmin.val(app.cdr.filter.tmin);
  seltmax.val(app.cdr.filter.tmax);
  $('#dlg-booking-time').dialog({modal:true,width:300,title:app.txt.title_booking_select_time
  ,buttons:[{text:app.txt.button_ok,click:function()
  {
   app.setProp(app.uri.params,'tmin',seltmin.val());
   app.setProp(app.uri.params,'tmax',seltmax.val());
   if(app.go(app.uri))
    $(this).dialog('close');
  }},{text:app.txt.button_cancel,click:function()
  {
   $(this).dialog('close');
  }}]});
 });
 $('.clear',wTime).click(function()
 {
  app.setProp(app.uri.params,'tmin');
  app.setProp(app.uri.params,'tmax');
  app.go(app.uri);
 });
 // Mtr
 var wMtr=$('#art-cdr .side .mtr .widget');
 $('select',wMtr).change(function()
 {
  app.setProp(app.uri.params,'mtr',$(this).val());
  app.go(app.uri);
 });
 $('.clear-select',wMtr).click(function()
 {
  app.setProp(app.uri.params,'mtr');
  app.go(app.uri);
 });
 // Srv
 //app.cdrFilterShowSrv('','');
 var selgrp=$('#dlg-booking-srv .grp select');
 selgrp.append($('<option>').attr('value','').text(app.txt.title_booking_all_groups));
 for(var i in app.cdr.grps)
  selgrp.append($('<option>').attr('value',i).text(app.cdr.grps[i]));
 var selsrv=$('#dlg-booking-srv .srv select');
 selsrv.append($('<option>').attr('value','').text(app.txt.title_booking_all_services));
 for(var i in app.cdr.srvs)
  selsrv.append($('<option>').attr('value',i).attr('grp',app.cdr.srvs[i].grp).text(app.cdr.srvs[i].name));
 selgrp.click(function()
 {
  selsrv.val('');
  $('option[grp]',selsrv).toggle($(this).val()=='').filter('[grp="'+$(this).val()+'"]').show();
 });
 var wSrv=$('#art-cdr .side .srv .widget');
 $('.empty,.value',wSrv).click(function()
 {
  selgrp.val(app.cdr.filter.grp).click();
  selsrv.val(app.cdr.filter.srv);
  $('#dlg-booking-srv').dialog({modal:true,width:400,title:app.txt.title_booking_select_srv
  ,buttons:[{text:app.txt.button_ok,click:function()
  {
   app.setProp(app.uri.params,'grp',selgrp.val());
   app.setProp(app.uri.params,'srv',selsrv.val());
   if(app.go(app.uri))
    $(this).dialog('close');
  }},{text:app.txt.button_cancel,click:function()
  {
   $(this).dialog('close');
  }}]});
 });
 $('.clear',wSrv).click(function()
 {
  app.setProp(app.uri.params,'grp');
  app.setProp(app.uri.params,'srv');
  app.go(app.uri);
 });
 // Clt
 //app.cdrFilterShowClt('','','','','');
 var wClt=$('#art-cdr .side .clt .widget');
 $('.empty,.value',wClt).click(function()
 {
  var dlg=$('#dlg-booking-clt');
  var sel=$('select',dlg);
  var fname=$('.fname input',dlg);
  var sname=$('.sname input',dlg);
  var phone=$('.phone input',dlg);
  var email=$('.email input',dlg);
  fname.val(app.cdr.filter.fname);
  sname.val(app.cdr.filter.sname);
  phone.val(app.cdr.filter.phone);
  email.val(app.cdr.filter.email);
  sel.text('').val('');
  sel.append($('<option>').attr('value','').text(app.txt.title_booking_all_clients));
  dlg.dialog({modal:true,width:600,title:app.txt.title_booking_select_clt
  ,buttons:[{text:app.txt.button_search,click:function()
  {
   sel.text('').val('');
   sel.append($('<option>').attr('value','').text(app.txt.title_booking_all_clients));
   var params={cmd:'clts'};
   app.setProp(params,'fname',fname.val());
   app.setProp(params,'sname',sname.val());
   app.setProp(params,'phone',phone.val());
   app.setProp(params,'email',email.val());
   var res=app.ajax(app.addParams('',params));
   if(!res||!res.data||!res.data.length)
    return;
   for(var i in res.data)
   {
    var clt=res.data[i];
    var text=(clt.firstname+' '+clt.lastname).trim();
    if(clt.phone.length)
     text+=' ('+clt.phone+')';
    text+=' <'+clt.email+'>';
    sel.append($('<option>').attr('value',clt.id).text(text));
   }
  }},{text:app.txt.button_cancel,click:function()
  {
   $(this).dialog('close');
  }},{text:app.txt.button_ok,click:function()
  {
   app.setProp(app.uri.params,'clt',sel.val());
   if(app.go(app.uri))
    $(this).dialog('close');
  }},{text:app.txt.button_cancel,click:function()
  {
   $(this).dialog('close');
  }}]});
 });
 $('#dlg-booking-clt select').dblclick(function()
 {
  app.setProp(app.uri.params,'clt',$(this).val());
  if(app.go(app.uri))
   $('#dlg-booking-clt').dialog('close');
 });
 $('.clear',wClt).click(function()
 {
  app.setProp(app.uri.params,'clt');
  app.go(app.uri);
 });
}

app.cdrFilterShowDate=function(on,date,date0,date1)
{
 app.cdr.filter.date=date;
 $('#art-cdr .side .date .value').text((date0&&date1)?(date0+' - '+date1):date);
 if(date)
  $('#art-cdr .side .date .dp').datepicker('setDate',date.toDate());
 app.setAttr($('#art-cdr .side .date .widget'),'set',on?'1':'');
}

app.cdrFilterShowTime=function(tmin,tmax)
{
 app.cdr.filter.tmin=tmin;
 app.cdr.filter.tmax=tmax;
 if(tmin||tmax)
  $('#art-cdr .side .time .value').text(app.cdr.times[''+tmin]+((tmin==tmax)?'':(' - '+app.cdr.times[''+tmax])));
 app.setAttr($('#art-cdr .side .time .widget'),'set',(tmin||tmax)?'1':'');
}

app.cdrFilterShowMtr=function(mtrs,mtr)
{
 app.cdr.mtrs=mtrs;
 app.cdr.filter.mtr=mtr;
 if(mtrs)
 {
  var sel=$('#art-cdr .side .mtr .select select');
  sel.html('');
  sel.append($('<option>').attr('value','').text(app.txt.title_booking_all_masters));
  for(var i in app.cdr.mtrs)
   sel.append($('<option>').attr('value',i).text(app.cdr.mtrs[i]));
  sel.val(mtr);
 }
 app.setAttr($('#art-cdr .side .mtr .widget'),'set',mtr?'1':'');
}

app.cdrFilterShowSrv=function(grp,srv)
{
 app.cdr.filter.grp=grp;
 app.cdr.filter.srv=srv;
 if(grp||srv)
  $('#art-cdr .side .srv .value').text(srv?app.cdr.srvs[srv].name:app.cdr.grps[grp]);
 app.setAttr($('#art-cdr .side .srv .widget'),'set',(grp||srv)?'1':'');
}

app.cdrFilterShowClt=function(clt,title,fname,sname,email)
{
 app.cdr.filter.fname=fname;
 app.cdr.filter.sname=sname;
 app.cdr.filter.email=email;
 if(clt)
  $('#art-cdr .side .clt .value').text(title);
 app.setAttr($('#art-cdr .side .clt .widget'),'set',clt?'1':'');
}

app.cdrFilterSetup=function(data)
{
 app.cdrFilterShowDate(app.uri.param('date'),data.date,data.date0,data.date1);
 app.cdrFilterShowTime(app.uri.param('tmin'),app.uri.param('tmax'));
 app.cdrFilterShowMtr(data.masters,app.uri.param('mtr'));
 app.cdrFilterShowSrv(app.uri.param('grp'),app.uri.param('srv'));
 app.cdrFilterShowClt(app.uri.param('clt'),data?data.cltT:'','','','');
}

app.cdrTablesInit=function()
{
 var dlgnew=$('#dlg-booking-new');
 var isrv=$('.srv select',dlgnew);
 var itip=$('.tip select',dlgnew);
 //var imtr=$('.mtr select',dlgnew);
 isrv.change(function()
 {
  app.cdr.dnew.tips=null;
  var tip=itip.val();
  itip.html('');
  app.cdr.dnew.params.srv=isrv.val();
  if(!app.cdr.dnew.params.srv)
   return;
  var res=app.ajax(app.addParams('',$.extend({action:'tips'},app.cdr.dnew.params)));
  if(!res||!res.data||!res.data.tips)
   return;
  app.cdr.dnew.tips=res.data.tips;
  for(t in res.data.tips)
   itip.append($('<option>').attr('value',t).text(res.data.tips[t].name));
  itip.val((tip in res.data.tips)?tip:itip.children().attr('value'));
 });
 $('#art-cdr table.book-sheet').on('click','tr[rowid] td.data .databtn',function(e)
 {
  var btn=$(this);
  var row=btn.parents('[rowid]');
  var field=btn.parents('[field]').attr('field');
  var date=row.parent().children().eq(1).find('[field="'+field+'"]').text();
  app.cdrDialogBookingAdd(date,row.attr('rowid'));
 });
}

app.cdrDialogBookingAdd=function(date,time)
{
 app.cdr.dnew.params.date=date;
 app.cdr.dnew.params.time=time;
 var dlgnew=$('#dlg-booking-new');
 var iname=$('.name input',dlgnew);
 var iphone=$('.phone input',dlgnew);
 var isrv=$('.srv select',dlgnew);
 var itip=$('.tip select',dlgnew);
 //var imtr=$('.mtr select',dlgnew);
 iname.val('');
 iphone.val('');
 isrv.html('').append($('<option>').attr('value','').text(app.txt.title_booking_select_srv));
 for(g in app.cdr.grps)
 {
  isrv.append($('<optgroup>').attr('label',app.cdr.grps[g]));
  for(s in app.cdr.srvs)
   if(app.cdr.srvs[s].grp==g)
    isrv.append($('<option>').attr('value',s).text(app.cdr.srvs[s].name));
 }
 itip.html('');
 //for(m in app.cdr.mtrs)
 // imtr.append($('<option>').attr('value',m).text(app.cdr.mtrs[m]));
 if(app.cdr.filter.srv)
 {
  isrv.val(app.cdr.filter.srv);
  isrv.change();
 }
 var title=app.txt.title_booking_add_new+': '+date+' '+app.min2str(time);
 dlgnew.dialog({modal:true,width:600,title:title,buttons:[
 {
  text:app.txt.button_ok,
  click:function()
  {
   var name=iname.val().trim();if(!name.length){iname.focus();return;}
   var phone=iphone.val().trim();if(!phone.length){iphone.focus();return;}
   var srv=isrv.val();if(!srv.length){isrv.focus();return;}
   var tip=itip.val();if(!tip.length){itip.focus();return;}
   var date=app.cdr.dnew.params.date;
   var time=app.cdr.dnew.params.time;
   var params={action:'book',time:time,name:name,phone:phone,tip:tip,date:date};
   var res=app.ajax(app.addParams('',params));
   if(!res)
    return;
   dlgnew.dialog('close');
   app.go();
  }
 },{text:app.txt.button_cancel,click:function(){dlgnew.dialog('close');}}]});
}

app.dataSetupCdrDef=function(tab,data)
{
 if(data)
  app.cdrFilterSetup(data);
 //var art=$('#art-'+app.par);
 var noData=!data||!data.bookings||!data.bookings.length;
 $('.no-data',tab).toggle(noData);
 //$('.side .dp .widget',art).datepicker();
 app.dataSetupAnyList(data,'bookings',$('.list',tab),$('.no-data',tab));
 if(noData)
  return;
 app.tableSetup(tab);
 var t=$('.list',tab);
 for(var i in data.bookings)
 {
  var b=data.bookings[i];
  $('tr[rowid='+b.id+']',t).attr('price',b.price).attr('disc',b.disc).attr('fact',b.fact).attr('total',b.total).attr('curr',b.curr);
 }
 $('.list tr[rowid]',tab).click(function(e)
 {
  if($(e.target).hasClass('btn'))
   return;
  var row=$(this);
  var dlg=$('#dlg-booking-view');
  $('.client .value',dlg).text($('td[field=client]',row).text());
  $('.service .value',dlg).text($('td[field=service]',row).text());
  $('.date .value',dlg).text($('td[field=date]',row).text());
  $('.time .value',dlg).text($('td[field=time]',row).text());
  $('.dura .value',dlg).text($('td[field=dura]',row).text());
  $('.qty .value',dlg).text($('td[field=qty]',row).text());
  $('.price .value',dlg).text(row.attr('price'));
  $('.fact .value',dlg).text(row.attr('fact'));
  $('.disc .value',dlg).text(row.attr('disc'));
  $('.total .value',dlg).text(row.attr('total'));
  $('.curr',dlg).text(row.attr('curr'));
  dlg.dialog({modal:true,resizable:true,width:400,title:app.txt.title_booking_details
 ,buttons:[{text:app.txt.button_close,click:function()
 {
  dlg.dialog('close');
 }}]});
 });
}

app.dataSetupCdrWeek=function(tab,data)
{//http://tikalk.com/incubator/week-picker-using-jquery-ui-datepicker
 if(data)
  app.cdrFilterSetup(data);
 //var art=$('#art-'+app.par);
 //$('.side .dp .widget',art).datepicker();
 var noData=!data||!data.bookings||!data.bookings.length;
 $('.no-data',tab).toggle(noData);
 //$('.side .dp .widget',art).datepicker();
 app.dataSetupAnyList(data,'bookings',$('.list',tab),$('.no-data',tab));
 if(noData)
  return;
 app.tableSetup(tab);
}

app.dataSetupCdrDay=function(tab,data)
{
 if(data)
  app.cdrFilterSetup(data);
 //var art=$('#art-'+app.par);
 var noData=!data||!data.bookings||!data.bookings.length;
 $('.no-data',tab).toggle(noData);
 //$('.side .dp .widget',art).datepicker();
 app.dataSetupAnyList(data,'bookings',$('.list',tab),$('.no-data',tab));
 if(noData)
  return;
 app.tableSetup(tab);
}

app.dataSetupClts=function(art,data)
{
 var noData=!data||!data.clts||!data.clts.length;
 $('.no-data',art).toggle(noData);
 var blocks=$('.ctrs',art);
 blocks.html('');
 if(noData)
  return;
 for(i in data.clts)
 {
  var clt=data.clts[i];
  var uri=((app.mode=='ctr')?('ctr-'+app.ctr+'/'):'')+'clt-'+clt.id+'/';
  var title=$('<div>').addClass('title').append($('<a>').addClass('ax').attr('href',uri).text(clt.name));
  var info=$('<div>')
  .append($('<div>').addClass('left').text(app.txt.title_last_visited+': '+clt.visited));
  //.append($('<a>').attr('target','_blank').attr('href','../ctr-'+text.centre_id+'/#'+text.mark).html(text.text))
  blocks.append($('<div>').addClass('block ui-widget-content').append(title).append(info));
 }
 app.blockInit(blocks);
 //app.dataSetupAnyList(data,'clts',$('.list',art),$('.no-data',art));
 //app.tableSetup(art);
}

app.dataSetupCltDef=function(tab,data)
{
}

app.dataSetupCltBook=function(tab,data)
{
 $('.view-all',tab).toggle(app.mode=='ctr');
 var noData=!data||!data.bookings||!data.bookings.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.bookings',tab);
 blocks.html('');
 if(noData)
  return;
}

app.dataSetupCltCmnt=function(tab,data)
{
 $('.view-all',tab).toggle(app.mode=='ctr');
 var noData=!data||!data.texts||!data.texts.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.comments',tab);
 blocks.html('');
 if(noData)
  return;
 for(i in data.texts)
 {
  var text=data.texts[i];
  var title=$('<div>').addClass('title');
  if(app.mode=='ctr')
   title.text(text.written);
  else
   title.append($('<a>').addClass('ax').attr('href','ctr-'+text.centre_id+'/'+$('#art-clt .tabholder .tab-cmnt').attr('href')).text(text.written+' '+text.centre_name));
  var info=$('<div>')
  .append($('<a>').attr('target','_blank').attr('href','../ctr-'+text.centre_id+'/#'+text.mark).html(text.text))
  var block=$('<div>')
  .addClass('block kind'+text.kind+' ui-widget-content')
  .append(title).append(info);
  blocks.append(block);
 }
 app.blockInit(blocks);
}

app.dataSetupCtrs=function(art,data)
{
 var noData=!data||!data.ctrs||!data.ctrs.length;
 $('.no-data',art).toggle(noData);
 var blocks=$('.ctrs',art);
 blocks.html('');
 if(noData)
  return;
 for(i in data.ctrs)
 {
  var ctr=data.ctrs[i];
  var info=$('<div>');
  if(('addr' in ctr)&&ctr.addr&&ctr.addr.length)
   info.append($('<div>').addClass('left').addClass('addr').text(ctr.addr));
  if(('bndName' in ctr)&&ctr.bndName&&ctr.bndName.length)
  {
   var bnd=$('<div>').addClass('right bnd');
   if(ctr.bndId)
    bnd.append($('<a>').addClass('ax').attr('href','bnd-'+ctr.bndId+'/').text(ctr.bndName));
   else
    bnd.text(ctr.bndName);
   info.append(bnd);
  }
  var title=$('<div>').addClass('title')
   .append($('<a>').addClass('ax'+(ctr.owner?'':' left')).attr('href','ctr-'+ctr.id+'/').text(ctr.name));
  if(!ctr.owner)
   title.append($('<a>').addClass('ax right').attr('href','clt-'+ctr.ownerId+'/').text('('+ctr.ownerName+')'));
  var block=$('<div>').attr('rowid',ctr.id)
  .addClass('block ctr'+(ctr.owner?'':' alien')+' ui-widget-content')
  .append(title).append(info);
  blocks.append(block);
 }
 app.blockInit(blocks);
}

app.dataSetupBriefDef=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.dataSetupAnyList(data,'tels',$('.list',tab),$('.no-data',tab));
 app.tableSetup(tab);
 app.dataSetupAnyLogo(tab,data?data.logo:null);
 $('.action:has(.delctr)',tab).toggleClass('edit',!!(data&&data['is-owner'])).toggle(!!(data&&data.values&&data.values['is-owner']));
}

app.dataSetupBriefHours=function(tab,data)
{
 app.dataSetupAnyList(data,'hours',$('.list',tab),$('.no-data',tab));
 $('#row-1 .btn[action="copy"]',tab).hide();
 app.tableSetup(tab);
}

app.dataSetupBriefOfrs=function(tab,data)
{
 var noData=!data||!data.ofrs||!data.ofrs.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.ofrs',tab);
 blocks.html('');
 if(noData)
  return;
 app.createBlocksOfrs(blocks,data.ofrs);
}

app.dataSetupBriefLvls=function(tab,data)
{
 var noData=!data||!data.lvls||!data.lvls.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.lvls',tab);
 blocks.html('');
 if(noData)
  return;
 for(i in data.lvls)
 {
  var lvl=data.lvls[i];
  var name=$('<div>').addClass('data edit').append($('<div>').addClass('btn')).append($('<a>'))
  app.dataSetupLangName(name,lvl.name);
  var block=$('<div>').attr('rowid',lvl.id)
  .addClass('block lvl ui-widget-content')
  .append(name)
  .append($('<div>').addClass('action')
   .append($('<div>').addClass('button right').attr('action','delete').text(app.txt.button_delete))
  );
  blocks.append(block);
 }
 app.buttonInitStyle(blocks);
 app.blockInit(blocks);
 app.tableSetupCellBtns($('.btn',blocks));
 $('.btn',blocks).click(function()
 {
  var cell=$(this.parentNode);
  var lvl=cell.parents('.block.lvl');
  var object=lvl.parent().attr('object');
  var rowid=lvl.attr('rowid');
  var a=$('a',cell);
  var prompt=object+' '+a.text();
  return app.dataEditBtnClick(cell,cell,a,rowid,'name',prompt)
 });
 $('.button[action="delete"]',blocks).click(function()
 {
  var button=$(this);
  var lvl=button.parents('.block.lvl');
  var object=lvl.parent().attr('object');
  var rowid=lvl.attr('rowid');
  var prompt=app.txt.button_delete+' '+object+' '+$('.data a',lvl).text()+'?';
  app.dlgConfirmShow(app.subtitle,prompt,function()
  {
   var uri=app.makeURI('action=delete');
   uri+='&rowid='+rowid;
   var res=app.ajax(uri);
   if(!res)
    return false;
   app.go();
   return true;
  });
 });
}

app.dataSetupBriefDescr=function(tab,data)
{
 app.dataSetupAnyTextArea(tab,data);
}

app.dataSetupBriefImgs=function(tab,data)
{
 for(var i=1;i<=5;i++)
  app.dataSetupAnyImg(tab,i,(data&&data.imgs)?data.imgs[i]:null);
}

app.dataSetupSrvsMakeSrvTable=function(srv)
{
 var curr=app.curr?(' '+app.curr):'';
 var tbody=$('<tbody>');
 if(srv.tips&&(srv.tips.length==1))
  tbody.append($('<tr>')
  .append($('<td class="label line">'))
  .append($('<th class="line">').text(srv.tips[0].duration))
  .append($('<th class="line">').text(srv.tips[0].price+curr))
  );
 else
  tbody.append($('<tr>')
  .append($('<td class="label" colspan="3">')));
 if(srv.tips&&(srv.tips.length>1))
  for(var i in srv.tips)
  {
   var tip=srv.tips[i];
   tbody.append($('<tr>')
   .append($('<td class="tip line">').text(tip.title))
   .append($('<th class="line">').text(tip.duration))
   .append($('<th class="line">').text(tip.price+curr))
   );
  }
 app.dataSetupLangName($('.label',tbody),srv.name);
 return $('<table>').append(tbody);
}

app.dataSetupSrvs=function(art,data)
{
 var Grps=$('.grps',art);
 Grps.html('');
 if(!data||!data.grps||(Grps.length!=1))
  return;
 for(var i in data.grps)
 {
  var grp=data.grps[i];
  var Grp=$('<div class="block grp" grpid="'+grp.id+'">');
  var Head=$('<div class="head">');
  Head.append($('<div class="title">').append($('<a class="ax" href="ctr-'+app.ctr+'/sgr-'+grp.id+'/">').text(grp.title)));
  Head.append($('<table>').append($('<tbody>').append($('<tr>')
  .append($('<th class="action">').append($('<div class="button right">').attr('action','addsrv').text(app.txt.button_addsrv)))
  .append($('<th class="action">').append($('<div class="button right">').attr('action','addpkg').text(app.txt.button_addpkg)))
  .append($('<th width="1000">'))
  )));
  var Body=$('<div class="body">');
  for(var j in grp.srvs)
   Body.append($('<div class="srv ui-corner-all" srvid="'+grp.srvs[j].id+'">')
   .append($('<a class="ax static" href="ctr-'+app.ctr+'/srv-'+grp.srvs[j].id+'/">')
   .append(app.dataSetupSrvsMakeSrvTable(grp.srvs[j]))));
  var Foot=$('<div class="foot">');
  Foot.append($('<table>').append($('<tbody>').append($('<tr>')
  .append($('<th class="action">').append($('<div class="button right delgrp" action="delgrp">').text(app.txt.button_delgrp)))
  )));
  Grps.append(Grp.append(Head).append(Body).append(Foot));
 }
 $('.grp',Grps).addClass('ui-widget-content');
 app.tableSetupDataCells($('.data',Grps));
 app.buttonInitStyle(Grps);
 app.blockInit(Grps);
 Grps.click(function(e)
 {
  var node=$(e.target);
  if(node.hasClass('button'))
  {
   if(node.attr('action')=='addsrv')
    return app.actionCreateService(node);
   if(node.attr('action')=='addpkg')
    return app.actionCreatePackage(node);
   if(node.attr('action')=='delgrp')
    return app.blockActionBtnClick(node,'grpid');
  }
  return true;
 });
}

app.dataSetupSgrDef=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.tableSetup(tab);
}

app.dataSetupSrvDef=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.dataSetupAnyList(data,'prcs',$('.list',tab),$('.no-data',tab));
 var btns=$('.list .data .btn[action="delprc"]',tab);
 if(btns.length==1)
  $(btns.get(0)).replaceWith();
 app.tableSetup(tab);
}

app.dataSetupSrvTips=function(tab,data)
{
 app.dataSetupAnyList(data,'tips',$('.list',tab),$('.no-data',tab));
 app.tableSetup(tab);
}

app.dataSetupSrvMtrs=function(tab,data)
{
 app.dataSetupAnyList(data,'mtrs',$('.list',tab),$('.no-data',tab));
 app.tableSetup(tab);
 if(data&&data.mtrs)
  for(n in data.mtrs)
   if(data.mtrs[n]['all-srv'])
    $('#row-'+data.mtrs[n].id+' .btn[action="delete"]',tab)
    .replaceWith($('<div class="center">').text(app.txt.prompt_all_services));
}

app.dataSetupSrvDescr=function(tab,data)
{
 app.dataSetupAnyTextArea(tab,data);
}

app.dataSetupSrvRestr=function(tab,data)
{
 app.dataSetupAnyTextArea(tab,data);
}

app.dataSetupSrvNotes=function(tab,data)
{
 app.dataSetupAnyTextArea(tab,data);
}

app.dataSetupSrvImgs=function(tab,data)
{
 for(var i=1;i<=5;i++)
  app.dataSetupAnyImg(tab,i,(data&&data.imgs)?data.imgs[i]:null);
}

app.dataSetupMtrs=function(art,data)
{
 var Mtrs=$('.mtrs',art);
 Mtrs.html('');
 if(!data||!data.mtrs||(Mtrs.length!=1))
  return;
 for(var i in data.mtrs)
 {
  var mtr=data.mtrs[i];
  var Mtr=$('<div class="block mtr" mtrid="'+mtr.id+'">');
  var Title=$('<div class="title">').append($('<a>').text(mtr.name));
  if('mtr-uri' in mtr)
   $('a',Title).attr('class','ax').attr('href',mtr['mtr-uri']);
  var Body=$('<a class="static">').append($('<table width="100%">').append($('<tr>')
  .append($('<td>').html(mtr.job?$('<b>').text(mtr.job):''))
  .append($('<td>').html(mtr.lvl?$('<i>').text(mtr.lvl):''))
  .append($('<td>').html(mtr.email?$('<u>').text(mtr.email):''))));
  Mtrs.append(Mtr.append(Title).append(Body));
 }
 app.blockInit(Mtrs);
}

app.dataSetupMtrDef=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.tableSetup(tab);
 $('.block',tab).last().toggle(!data||!data.cant_fire);
}

app.dataSetupMtrPrivs=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.tableSetup(tab);
 var disabled=data&&!data.edit;
 $('.priv input',tab).each(function(i,item)
 {
  var checked=(data&&(data.values.privs.indexOf(parseInt($(item).attr('priv')))>=0));
  if(item.checked!=checked)
   item.checked=checked;
  if(data&&(item.disabled!=disabled))
   item.disabled=disabled;
 });
}

app.dataSetupBnds=function(art,data)
{
 var noData=!data||!data.bnds||!data.bnds.length;
 $('.no-data',art).toggle(noData);
 var blocks=$('.bnds',art);
 blocks.html('');
 if(noData)
  return;
 for(i in data.bnds)
 {
  var bnd=data.bnds[i];
  var info=$('<div>');
  if(('email' in bnd)&&bnd.email&&bnd.email.length)
   info.append($('<div>').addClass('left').addClass('email').text(bnd.email));
  if(('uri' in bnd)&&bnd.uri&&bnd.uri.length)
  {
   info.append($('<div>').addClass('right uri')
   .append($('<a>').attr('href',bnd.uri).text(bnd.uri)));
  }
  var block=$('<div>').attr('rowid',bnd.id)
  .addClass('block bnd ui-widget-content')
  .append($('<div>').addClass('title')
   .append($('<a>').addClass('ax').attr('href','bnd-'+bnd.id+'/').text(bnd.name))
  )
  .append(info);
  blocks.append(block);
 }
 app.blockInit(blocks);
 //app.dataSetupAnyList(data,'bnds',$('.list',art),$('.no-data',art));
 //app.tableSetup(art);
}

app.dataSetupBndDef=function(tab,data)
{
 app.dataSetupAnyForm($('.form',tab),data);
 app.tableSetup(tab);
 app.dataSetupAnyLogo(tab,data?data.logo:null);
 $('.action:has(.delete)',tab).toggleClass('edit',!!(data&&data['is-owner'])).toggle(!!(data&&data.values&&data.values['is-owner']));
}

app.dataSetupBndOfrs=function(tab,data)
{
 var noData=!data||!data.ofrs||!data.ofrs.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.ofrs',tab);
 blocks.html('');
 if(noData)
  return;
 app.createBlocksOfrs(blocks,data.ofrs);
}

app.dataSetupBndLvls=function(tab,data)
{
 var noData=!data||!data.lvls||!data.lvls.length;
 $('.no-data',tab).toggle(noData);
 var blocks=$('.lvls',tab);
 blocks.html('');
 if(noData)
  return;
 for(i in data.lvls)
 {
  var lvl=data.lvls[i];
  var name=$('<div>').addClass('data edit').append($('<div>').addClass('btn')).append($('<a>'))
  app.dataSetupLangName(name,lvl.name);
  var block=$('<div>').attr('rowid',lvl.id)
  .addClass('block lvl ui-widget-content')
  .append(name)
  .append($('<div>').addClass('action')
   .append($('<div>').addClass('button right').attr('action','delete').text(app.txt.button_delete))
  );
  blocks.append(block);
 }
 app.buttonInitStyle(blocks);
 app.blockInit(blocks);
 app.tableSetupCellBtns($('.btn',blocks));
 $('.btn',blocks).click(function()
 {
  var cell=$(this.parentNode);
  var lvl=cell.parents('.block.lvl');
  var object=lvl.parent().attr('object');
  var rowid=lvl.attr('rowid');
  var a=$('a',cell);
  var prompt=object+' '+a.text();
  return app.dataEditBtnClick(cell,cell,a,rowid,'name',prompt)
 });
 $('.button[action="delete"]',blocks).click(function()
 {
  var button=$(this);
  var lvl=button.parents('.block.lvl');
  var object=lvl.parent().attr('object');
  var rowid=lvl.attr('rowid');
  var prompt=app.txt.button_delete+' '+object+' '+$('.data a',lvl).text()+'?';
  app.dlgConfirmShow(app.subtitle,prompt,function()
  {
   var uri=app.makeURI('action=delete');
   uri+='&rowid='+rowid;
   var res=app.ajax(uri);
   if(!res)
    return false;
   app.go();
   return true;
  });
 });
}

app.dataSetupBndDescr=function(tab,data)
{
 app.dataSetupAnyTextArea(tab,data);
}

app.dataSetupBndImgs=function(tab,data)
{
 for(var i=1;i<=5;i++)
  app.dataSetupAnyImg(tab,i,(data&&data.imgs)?data.imgs[i]:null);
}

//// Autocomplete ////

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
   if(this.options.isTerr&&!('id' in item))
    return li.append($('<span>').addClass('title').text(item.label)).appendTo(ul);
   if(this.options.isProc&&(''+item.id).substring(0,1)=='c')
   {
    var span=$('<span>').addClass('cat');
    span.append($('<span>').addClass('title').text(item.label));
    return li.append(span).appendTo(ul);
   }
   var term=this.term;
   var len=term.length;
   var label=item.label;
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
     label=label.substr(0,pos-len)+'<b class="ui-state-highlight">'+label.substr(pos-len,len)+'</b>'+label.substr(pos);
   }
   return li.append($('<a>').append($('<span>').html(label))).appendTo(ul);
  }
 });
}

app.initAutocomplete=function()
{
 app.declareACHighlight();
 // Procedure autocomplete
 var tagDlgPrcInput=$('#dlg-input .prc .edit input');
 tagDlgPrcInput.achighlight({minLength:0,isProc:true
 ,source0:app.acMenuProc
 ,source:function(request,response)
 {
  if(request.term.length>=2)
   $.getJSON(document.location,'ac=prc&term='+request.term,response);
  else if(!request.term.length)
   response(app.acMenuProc);
 }
 ,select:function(e,ui)
 {
  var id=parseInt(ui.item.id.substr(1));
  if($('#dlg-input .prcs:visible').length)
  {
   $('#dlg-input .prcs .item[acid="'+id+'"]').replaceWith();
   app.dialogInputAddPrc(id);
   setTimeout(function(){$('#dlg-input .prc .edit input').val('').focus();},1);
  }
  else
   $(this).data('acid',id).data('actext',ui.item.label);
 }})
 $('#dlg-input .prc .edit .btn').click(function()
 {
  tagDlgPrcInput.data('acid','').data('actext','').val('').achighlight('search','')
 })
 .mouseenter(function(){$(this).addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')})
 // Currency autocomplete
 var tagDlgCurrInput=$('#dlg-input .curr .edit input');
 tagDlgCurrInput.achighlight({minLength:0,isProc:true
 ,source:function(request,response)
 {
  $.getJSON(document.location,'ac=curr&term='+request.term,response);
 }
 ,select:function(e,ui)
 {
  $(this).data('acid',ui.item.id).data('actext',ui.item.label);
 }})
 $('#dlg-input .curr .edit .btn').click(function()
 {
  tagDlgCurrInput.data('acid','').data('actext','').val('').achighlight('search','')
 })
 .mouseenter(function(){$(this).addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')});
 var tagDlgDate=$('#dlg-date .edit input');
 tagDlgDate.datepicker(
 {
  hideIfNoPrevNext:true
 //,showButtonPanel:true
 //,numberOfMonths:2
 ,minDate:0
 //,maxDate:"+1M -1D"
 ,onSelect:function(date)
 {
  //$(this).parent().addClass('has-text');
  $(this).data('acid',$(this).datepicker('getDate'));
  //app.onListFilterChanged();
  //search_date=$.datepicker.formatDate($.datepicker.W3C,$(this).datepicker('getDate'));
 }
 })
 .click(function()
 {
  $(this).datepicker('show');
 });
}

app.dialogInputAddPrc=function(id)
{
 $('#dlg-input .prcs')
 .append($('<div class="item ui-widget-header ui-corner-all">').attr('acid',id).text(app.prcs[id])
 .append($('<div class="btn ui-corner-all">').append($('<span class="ui-icon ui-icon-circle-close">'))
 .mouseenter(function(){$(this).addClass('ui-state-hover')})
 .mouseleave(function(){$(this).removeClass('ui-state-hover')})
 .click(function(){$(this).parent().replaceWith()})
 ));
}

//// Dialogs ////

app.dialogInit=function()
{
}

app.dlgConfirmShow=function(title,prompt,onOK,onCancel)
{
 var dlg=$('#dlg-confirm');
 $('.prompt',dlg).text(prompt);
 dlg.dialog({modal:true,resizable:true,width:400,title:title
 ,buttons:[{text:app.txt.button_ok,click:function()
 {
  if(onOK())
   dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
  if(onCancel)
   onCancel();
 }}]});
}

app.dlgInputInit=function()
{
 var dlg=$('#dlg-input');
 dlg.find('.edit')
 .keydown(function(event)
 {
  if(event.target.tagName!='INPUT')
   return true;
  if(event.keyCode==$.ui.keyCode.ENTER)
  {
   var inputs=$('input:visible',dlg);
   for(var i=0;i<inputs.length;i++)
   {
    if((inputs[i]!=event.target))
     continue;
    if($(inputs[i]).hasClass('required')&&!$(inputs[i]).val().trim().length)
     break;
    if($(inputs[i]).hasClass('email')&&!app.checkEmail($(inputs[i]).val().trim()))
     break;
    if(i<inputs.length-1)
     $(inputs[i+1]).select().focus();
    else if(!$('.ui-autocomplete:visible').length)
     dlg.dialog('option','buttons')[0].click();
    break;
   }
  }
  return true;
 });
}

// values={'name':'?','email':'?','titles':{'ru':'?','en':'?'},'prc':'?','curr:'?'}
app.dlgInputShow=function(title,prompt,values,onOK,onCancel)
{
 //console.log(values);
 var res,dlg=$('#dlg-input');
 var bool=(typeof(values.name)=='boolean');
 if('name' in values)
 {
  var tagName=$('.name',dlg);
  $('.edit',tagName).toggle(!bool);
  $('.bool',tagName).toggle(bool);
  if(bool)
   $('.bool input',tagName).attr('checked',values.name);
  else
   $('.edit input',tagName).val(values.name).attr('placeholder',prompt).toggleClass('required',!values.optional);
  tagName.show();//.find('.prompt .text').text(prompt);
 }
 else
  $('.name',dlg).hide();//.find('.prompt .text').text('');

 $(['uri','firstname','lastname','email','title']).each(function(i,item)
 {
  $('.'+item,dlg).toggle(item in values);
  $('.'+item+' input',dlg).val((item in values)?values[item]:'');
 });

 $('.bnd',dlg).hide().find('select').empty();
 if('bnd' in values)
 {
  res=app.ajax(app.makeURI('action=bnds'),$('.bnd select',dlg));
  if(res&&('data' in res))
   for(i in res.data)
    $('.bnd select',dlg).append($('<option>').attr('value',res.data[i].id).text(res.data[i].title));
  if(values.bnd)
   $('.bnd select',dlg).val(values.bnd);
  $('.bnd',dlg).show();
 }

 $('.level',dlg).hide().find('select').empty();
 if('level' in values)
 {
  res=app.ajax(app.makeURI('action=levels'),$('.level select',dlg));
  if(res&&('data' in res))
   for(i in res.data)
    $('.level select',dlg).append($('<option>').attr('value',res.data[i].id).text(res.data[i].title));
  if(values.level)
   $('.level select',dlg).val(values.level);
  $('.level',dlg).show();
 }

 $('.role',dlg).hide().find('select').empty();
 if('role' in values)
 {
  res=app.ajax(app.makeURI('action=roles'),$('.role select',dlg));
  if(res&&('data' in res))
   for(i in res.data)
    $('.role select',dlg).append($('<option>').attr('value',res.data[i].id).text(res.data[i].title));
  if(values.role)
   $('.role select',dlg).val(values.role);
  $('.role',dlg).show();
 }

 $('.mtr',dlg).hide().find('select').empty();
 if('mtr' in values)
 {
  res=app.ajax(app.makeURI('action=mtrs'),$('.mtrs select',dlg));
  if(!res||!('data' in res)||!res.data.length)
  {
   app.msg(app.txt.msg_no_more_masters,title);
   return;
  }
  for(i in res.data)
   $('.mtr select',dlg).append($('<option>').attr('value',res.data[i].id).text(res.data[i].title));
  if(values.mtr)
   $('.mtr select',dlg).val(values.mtr);
  $('.mtr',dlg).show();
 }

 if('tip' in values)
  $('.tip',dlg).show().find('input').each(function(i,item){$(item).val(values.tip[$(item).attr('field')]);});
 else
  $('.tip',dlg).hide().find('input').val('');

 $('.titles',dlg).toggle('titles' in values).find('input').val('');
 if('titles' in values)
  for(lang in values.titles)
   $('.titles .edit[lang="'+lang+'"]',dlg).find('input').val(values.titles[lang]);

 var tagPrcs=$('.prcs',dlg);
 tagPrcs.toggle('prcs' in values).empty();
 if('prcs' in values)
  for(n in values.prcs)
   app.dialogInputAddPrc(values.prcs[n]);

 var tagPrc=$('.prc',dlg);
 var tagPrcInput=tagPrc.find('input');
 tagPrc.toggle(('prc' in values)||('prcs' in values));
 if(('prc' in values)&&values.prc.id)
  tagPrcInput.data('acid',values.prc.id).data('actext',values.prc.title).val(values.prc.title);
 else
  tagPrcInput.data('acid','').data('actext','').val('');
 //console.log('Show: '+tagPrcInput.data('acid'));

 var tagCurr=$('.curr',dlg);
 var tagCurrInput=tagCurr.find('input');
 tagCurr.toggle('curr' in values);
 if(('curr' in values)&&values.curr.id)
  tagCurrInput.data('acid',values.curr.id).data('actext',values.curr.title).val(values.curr.title);
 else
  tagCurrInput.data('acid','').data('actext','').val('');

 dlg.dialog({modal:true,resizable:true,width:400,title:title
 ,open:function(event,ui)
 {
  var inputs=$('input:visible',dlg);
  if(inputs.length)
   $(inputs[0]).select().focus();
 }
 ,close:function()
 {
  tagPrcInput.achighlight('close');
  tagCurrInput.achighlight('close');
 }
 ,buttons:[{text:app.txt.button_ok,click:function()
 {
  var inputs=$('input:visible',dlg);
  var focus=null;
  for(var i=0;i<inputs.length;i++)
  {
   focus=$(inputs[i]);
   var text=focus.val().trim();
   if(focus.hasClass('required')&&!text.length)
    break;
   focus=null;
  }
  if(focus)
  {
   focus.select().focus();
   return;
  }
  var newvalues={};
  if('name' in values)
   if(bool)
    newvalues.name=$('.name .bool input',dlg).filter(':checked').length;
   else
    newvalues.name=$('.name .edit input',dlg).val().trim();
  if('uri' in values)
   newvalues.uri=$('.uri input',dlg).val().trim();
  if('firstname' in values)
   newvalues.firstname=$('.firstname input',dlg).val().trim();
  if('lastname' in values)
   newvalues.lastname=$('.lastname input',dlg).val().trim();
  if('email' in values)
  {
   newvalues.email=$('.email input',dlg).val().trim();
   if(!app.checkEmail(newvalues.email))
    return;
  }
  if('prc' in values)
  {
   var prcId=tagPrcInput.data('acid');
   if(!prcId)
   {
    tagPrcInput.data('acid','').val('').achighlight('search','')
    return;
   }
   newvalues.prc={'id':prcId,'title':tagPrcInput.data('actext')};
  }
  if('prcs' in values)
  {
   if(!tagPrcs.children().length)
   {
    tagPrcInput.val('').achighlight('search','')
    return;
   }
   newvalues.prcs=[];
   tagPrcs.children().each(function(i,item){newvalues.prcs.push($(item).attr('acid'))})
  }
  if('curr' in values)
  {
   var currId=tagCurrInput.data('acid');
   if(!currId)
   {
    tagCurrInput.data('acid','').val('').achighlight('search','')
    return;
   }
   newvalues.curr={'id':currId,'title':tagCurrInput.data('actext')};
  }
  if('bnd' in values)
   newvalues.bnd=$('.bnd .edit select',dlg).val();
  if('level' in values)
   newvalues.level=$('.level .edit select',dlg).val();
  if('role' in values)
   newvalues.role=$('.role .edit select',dlg).val();
  if('mtr' in values)
   newvalues.mtr=$('.mtr .edit select',dlg).val();
  if('tip' in values)
  {
   var tip={};
   $('.tip input',dlg).each(function(i,item){app.setProp(tip,$(item).attr('field'),$(item).val());});
   newvalues.tip=tip;
  }
  if('title' in values)
   newvalues.title=$('.title .edit input',dlg).val();
  if('titles' in values)
  {
   var titles={};
   $('.titles input',dlg).each(function(i,item)
   {
    app.setProp(titles,$(this).parent().attr('lang').trim(),$(this).val());
   });
   newvalues.titles=titles;
  }
  if(onOK(newvalues))
   dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
  if(onCancel)
   onCancel();
 }}]});
}

app.dlgMasterPrivs=function(title,privs,onOK)
{
 var dlg=$('#dlg-privs');
 var inputs=$('.priv input',dlg);
 inputs.each(function(i,item)
 {
  item.checked=privs.indexOf(parseInt($(item).attr('priv')))>=0;
 })
 dlg.dialog({modal:true,resizable:true,width:400,title:title
 ,buttons:[{text:app.txt.button_ok,click:function()
 {
  var newprivs=[];
  inputs.each(function(i,item)
  {
   if(item.checked)
    newprivs.push(parseInt($(item).attr('priv')));
  })
  if(onOK(newprivs))
   dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
 }}]});
}

app.upload={'file':null};

app.dlgFileInit=function()
{//http://abandon.ie/notebook/simple-file-uploads-using-jquery-ajax
 var dlg=$('#dlg-file');
 dlg.find('.edit input')
 .change(function(event)
 {
  var file=event.target.files.length?event.target.files[0]:null;
  //console.log('File: '+file.name);
  if(file&&file.size>30000000)
  {
   app.msg(app.txt.error_file_too_large+': '+file.size);
   $(this).val('');
   file=null;
  }
  app.upload.file=file;
  if(file)
   dlg.dialog('option','buttons')[0].click();
  else
   dlg.dialog('close');
 });
}

app.dlgFileShow=function(title,prompt,onOK)
{
 app.upload.file=null;
 var dlg=$('#dlg-file');
 var edit=$('input',dlg);
 edit.val('');
 $('.prompt',dlg).text(prompt);
 $('.thumb img',dlg).removeAttr('src');
 dlg.dialog({modal:true,resizable:true,width:400,title:title
 ,open:function(event,ui){edit.click();}
 ,close:function(event,ui){app.upload.file=null;}
 ,buttons:[{text:app.txt.button_ok,click:function()
 {
  if(!app.upload.file)
   return;
  if(onOK(app.upload.file))
   dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
 }}]});
}

app.dlgDateShow=function(title,prompt,onOK)
{
 var dlg=$('#dlg-date');
 var edit=$('input',dlg);
 edit.val('').data('acid','');
 $('.prompt',dlg).text(prompt);
 dlg.dialog({modal:true,resizable:true,width:400,title:title
 //,open:function(event,ui){edit.click();}
 //,close:function(event,ui){app.upload.file=null;}
 ,buttons:[{text:app.txt.button_order,click:function()
 {
  if(onOK(edit.data('acid')))
   dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
 }}]});
}

app.dlgMessageShow=function(title,subject,onSend)
{
 var dlg=$('#dlg-message');
 var Subject=$('.subject input',dlg);
 var Message=$('.message textarea',dlg);
 Subject.val(subject);
 Message.val('');
 dlg.dialog({modal:true,resizable:true,width:600,title:title
 ,open:function(event,ui){(subject?Message:Subject).focus();}
 ,buttons:[{text:app.txt.button_send,click:function()
 {
  var subject=Subject.val().trim();
  if(!subject.length)
   return Subject.focus()&&0;
  var message=Message.val().trim();
  if(!message.length)
   return Message.focus()&&0;
  return onSend(subject,message)&&!!dlg.dialog('close');
 }},{text:app.txt.button_cancel,click:function()
 {
  dlg.dialog('close');
 }}]});
}

app.createBlocksOfrs=function(blocks,ofrs)
{
 for(i in ofrs)
 {
  var ofr=ofrs[i];
  var block=$('<div>').attr('rowid',ofr.id)
  .addClass('block ofr ui-widget-content')
  .append($('<div>').addClass('title').text(ofr.name))
  .append($('<div>').addClass('action')
   .append($('<div>').addClass('left').text(''+ofr.price+' '+ofr.curr))
   .append($('<div>').addClass('button right').attr('action','order').text(app.txt.button_order))
  );
  if(ofr.ask_date)
   block.attr('ask_date',true);
  blocks.append(block);
 }
 app.buttonInitStyle(blocks);
 app.blockInit(blocks);
 $('.button[action="order"]',blocks).click(function()
 {
  app.offerOrderClick($(this).parents('.block.ofr'));
 });
}

app.offerOrderClick=function(ofr)
{
 var rowid=ofr.attr('rowid');
 var object=ofr.parent().attr('object');
 var prompt=app.txt.button_order+' '+object+' "'+$('.title',ofr).text()+'"?';
 app.dlgConfirmShow(app.subtitle,prompt,function()
 {
  setTimeout(function()
  {
   if(ofr.attr('ask_date'))
   {
    prompt=app.txt.prompt_start_date;
    app.dlgDateShow(app.subtitle,prompt,function(date)
    {
     setTimeout(function()
     {
      app.offerOrder(rowid,date);
     },1);
     return true;
    });
   }
   else
   {
    app.offerOrder(rowid);
   }
  },1);
  return true;
 });
}

app.offerOrder=function(ofrId,startDate)
{
 var ctrId=app.ctr?app.ctr.id:0;
 var bndId=app.bnd?app.bnd.id:0;
 var uri=app.makeURI('action=offer&id='+ofrId);
 if(ctrId)
  uri+='&ctr='+ctrId;
 if(bndId)
  uri+='&bnd='+bndId;
 if(startDate)
  uri+='&date='+startDate;
 alert('Order offer: uri='+uri);
 /*
 var res=app.ajax(uri);
 if(!res)
  return false;
 app.go();
 */
 return true;
}

//// Root initialization ////

app.onWindowResize=function()
{
 var wnd=$(window);
 //console.log('com: onWindowResize('+wnd.width()+','+wnd.height()+')');
 var body=$('body').first();
 var frame=$('header table.frame',body);
 var flt0=body.css('float')=='left'; // Existing state
 var flt1=frame.width()>wnd.width(); // Required state
 if (flt0!=flt1)
  body.css('float',flt1?'left':'none');
}

app.onWindowScroll=function()
{
 //var wnd=$(window);
 //console.log('com: onWindowScroll('+wnd.scrollLeft()+','+wnd.scrollTop()+')');
}

$(function()
{
 //console.log('com initialization...');
 app.styleInit();
 app.titleInit();
 app.menuInit();
 app.tabsInit();
 app.tableInit();
 app.blockInit();
 app.buttonInit();
 app.dialogInit();
 app.dlgInputInit();
 app.dlgFileInit();
 app.cdrFilterInit();
 app.cdrTablesInit();
 app.initAutocomplete();
 app.go(location.href,false);
 //console.log('com initialized');
});
