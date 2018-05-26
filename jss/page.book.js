//// Seance control ////

app.seance.execLogin=function(href,email,pass,err)
{
 err('');
 var xhr=app.sync('login',{email:email,pass:pass});
 if(xhr.status!=200)
  return err(xhr.status+': '+xhr.statusText);
 var res=eval('('+xhr.responseText+')');
 if('error' in res)
  return err(res.error);
 if(res.result!='OK')
  return err(app.txt.protocol_error);
 var URI=app.parseUri(app.nvl(href,location.href));
 URI.pro='https://';
 URI.hash='';
 location.href=URI.href();
 return true;
}

app.seance.execLogout=function(err)
{
 var xhr=app.sync('logout');
 if(xhr.status!=200)
  err(xhr.status+': '+xhr.statusText);
 else
 {
  var req=eval('('+xhr.responseText+')');
  if('error' in req)
   err(req.error);
  else
  {
   document.location.reload(true);
   return true;
  }
 }
 return false;
}

app.seance.execSignup=function(href,firstname,lastname,email,pass,err,ok)
{
 err('');
 var xhr=app.sync('signup',{href:href,firstname:firstname,lastname:lastname,email:email,pass:pass});
 if(xhr.status!=200)
  return err(xhr.status+': '+xhr.statusText);
 var res=eval('('+xhr.responseText+')');
 if('error' in res)
  return err(res.error);
 if(res.result!='OK')
  return err(app.txt.protocol_error);
 if((res.result!='OK')||!('message' in res))
  return err(app.txt.protocol_error);
 ok(res.message);
 return true;
}

app.seance.execRestore=function(href,email,err,ok)
{
 err('');
 var xhr=app.sync('restore',{href:href,email:email});
 if(xhr.status!=200)
  return err(xhr.status+': '+xhr.statusText);
 var res=eval('('+xhr.responseText+')');
 if('error' in res)
  return err(res.error);
 if((res.result!='OK')||!('message' in res))
  return err(app.txt.protocol_error);
 ok(res.message);
 return true;
}

app.seance.execPasswd=function(old,pass,err,ok)
{
 err('');
 var xhr;
 if(old)
  xhr=app.sync('passwd',{old:old,pass:pass});
 else
  xhr=app.sync(document.location.href,{pass:pass});
 if(xhr.status!=200)
  return err(xhr.status+': '+xhr.statusText);
 var res=eval('('+xhr.responseText+')');
 if('error' in res)
  return err(res.error);
 if((res.result!='OK')||!('message' in res))
  return err(app.txt.protocol_error);
 ok(res.message,function()
 {
  if('uri' in res)
  {
   if(!old&&res['result']=='OK')
    document.location.href=res.uri;
   else
    app.go(res.uri);
  }
 });
 return true;
}

app.seance.execListed=function(href,centre,addr,phone)
{
 var res=app.ajax('listed',null,{href:href,centre:centre,addr:addr,phone:phone});
 if(!res)
  return false;
 if(res.uri)
  location.href=res.uri;
 return true;
}

//// Login forms ////

app.topw.showForm=function(cls)
{
 scrollTo(0,0);
 var nav=$('nav');
 $('.topw.'+cls+':not(:visible),.topw:visible:not(.'+cls+')',nav).toggle();
}

app.topw.hideAll=function()
{
 var topw=$('nav .topw:visible');
 if(topw.length)
  topw.hide();
}

app.topw.showMessageForm=function(title,ok)
{
 var nav=$('nav');
 var topw=$('.topw.message',nav);
 $('.title',topw).html(title);
 app.topw.showForm('message');
 $('.button.main',topw).click(ok).focus();
}

app.ipadScreenWidth = 1000;

app.topw.showLoginForm=function(href,title)
{
 app.topw.href=href||document.location.href;
 var nav=$('nav');
 var topw=$('.topw.login',nav);
 $('.title',topw).text(app.nvl(title,app.txt.topw_title_topw_login));
 $('.error',topw).text('');
 $('input',topw).val('');
 app.topw.showForm('login');
 if($(document).width()>=app.ipadScreenWidth)
  $('.email input',topw).focus();
 return false;
}

app.topw.toggleLoginForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.login',nav);
 if(topw.filter(':visible').length)
  app.topw.hideAll();
 else
  app.topw.showLoginForm();
 return false;
}

app.topw.switchToRestoreForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.restore',nav);
 $('.error',topw).text('');
 $('.pass input',topw).val('');
 var email=$('.email input',topw);
 var text=$('.topw.login .email input',nav).val();
 email.val(text);
 app.topw.showForm('restore');
 if(app.checkEmail(text))
  $('.restore-now a',topw).focus();
 else if($(document).width()>=app.ipadScreenWidth)
   email.select().focus();
}

app.topw.switchToSignupForm=function()
{
 app.hideTopMenu();
 app.topw.href=document.location.href;
 var nav=$('nav');
 var topw=$('.topw.signup',nav);
 $('.error',topw).text('');
 $('input',topw).val('');
 app.topw.showForm('signup');
 if ($(document).width()>=app.ipadScreenWidth+100)
  $('.firstname input',topw).focus();
 return false;
}

app.topw.showListedForm=function()
{
 if(!app.seance.online)
  return app.topw.showLoginForm(null,app.txt.topw_title_topw_login_listed);
 var nav=$('nav');
 var topw=$('.topw.listed',nav);
 $('.error',topw).text('');
 $('input',topw).val('');
 app.topw.showForm('listed');
 if($(document).width()>=app.ipadScreenWidth)
  $('.centre input',topw).focus();
 return false;
}

app.topw.submitLoginForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.login',nav);
 var email=$('.email input',topw);
 var pass=$('.pass input',topw);
 var error=$('.error',topw);
 var textEmail=email.val();
 var textPass=pass.val();
 if(!textEmail.length)
 {
  error.text(app.txt.error_no_email);
  email.focus();
 }
 else if(!app.checkEmail(textEmail))
 {
  error.text(app.txt.error_invalid_email);
  email.focus();
 }
 else if(!textPass.length)
 {
  error.text(app.txt.error_no_password);
  pass.focus();
 }
 else
  app.seance.execLogin(app.topw.href,textEmail,textPass,function(e){error.text(e);});
}

app.topw.submitSignupForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.signup',nav);
 var fname=$('.firstname input',topw);
 var textFname=fname.val();
 var lname=$('.lastname input',topw);
 var textLname=lname.val();
 var email=$('.email input',topw);
 var textEmail=email.val();
 var pass=$('.pass input',topw);
 var textPass1=pass.eq(0).val();
 var textPass2=pass.eq(1).val();
 if(!textFname.length)
 {
  $('.error',topw).text(app.txt.error_no_fname);
  fname.focus();
 }
 else if(!textLname.length)
 {
  $('.error',topw).text(app.txt.error_no_lname);
  lname.focus();
 }
 else if(!textEmail.length)
 {
  $('.error',topw).text(app.txt.error_no_email);
  email.focus();
 }
 else if(!app.checkEmail(textEmail))
 {
  $('.error',topw).text(app.txt.error_invalid_email);
  email.focus();
 }
 else if(!textPass1.length)
 {
  $('.error',topw).text(app.txt.error_no_password);
  pass.eq(0).focus();
 }
 else if(!textPass2.length)
 {
  $('.error',topw).text(app.txt.error_no_password);
  pass.eq(1).focus();
 }
 else if(textPass1!=textPass2)
 {
  $('.error',topw).text(app.txt.error_password_differ);
  pass.eq(1).select().focus();
 }
 else
 {
  app.seance.execSignup(app.topw.href,textFname,textLname,textEmail,textPass1,function(e)
  {
   $('.error',topw).text(e);
  },app.topw.showMessageForm);
 }
}

app.topw.submitRestoreForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.restore',nav);
 var email=$('.email input',topw);
 //var pass=$('.pass input',topw);
 var textEmail=email.val();
 //var textPass1=pass.eq(0).val();
 //var textPass2=pass.eq(1).val();
 if(!textEmail.length)
 {
  $('.error',topw).text(app.txt.error_no_email);
  email.focus();
 }
 else if(!app.checkEmail(textEmail))
 {
  $('.error',topw).text(app.txt.error_invalid_email);
  email.focus();
 }
// else if(!textPass1.length)
// {
//  $('.error',topw).text(app.txt.error_no_password);
//  pass.eq(0).focus();
// }
// else if(!textPass2.length)
// {
//  $('.error',topw).text(app.txt.error_no_password);
//  pass.eq(1).focus();
// }
// else if(textPass1!=textPass2)
// {
//  $('.error',topw).text(app.txt.error_password_differ);
//  pass.eq(1).select().focus();
// }
 else
 {
  app.seance.execRestore(app.topw.href,textEmail,function(e)
  {
   $('.error',topw).text(e);
  },app.topw.showMessageForm);
 }
}

app.topw.showPasswdForm=function(useold)
{
 scrollTo(0,0);
 var nav=$('nav');
 var topw=$('.topw.passwd',nav);
 $('.oldpass',topw).toggle(!!useold);
 $('.error',topw).text('');
 $('input',topw).val('');
 app.topw.showForm('passwd');
 if ($(document).width()>=app.ipadScreenWidth)
  $((!!useold)?'.oldpass input':'.pass:first input',topw).focus();
}

app.topw.submitPasswdForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.passwd',nav);
 var oldpass=$('.oldpass:visible input',topw);
 var pass=$('.pass input',topw);
 var textOld=oldpass.val();
 var textPass1=pass.eq(0).val();
 var textPass2=pass.eq(1).val();
 if(oldpass.length&&!textOld.length)
 {
  $('.error',topw).text(app.txt.error_no_password);
  oldpass.focus();
 }
 else if(!textPass1.length)
 {
  $('.error',topw).text(app.txt.error_no_password);
  pass.eq(0).focus();
 }
 else if(!textPass2.length)
 {
  $('.error',topw).text(app.txt.error_no_password);
  pass.eq(1).focus();
 }
 else if(textPass1!=textPass2)
 {
  $('.error',topw).text(app.txt.error_password_differ);
  pass.eq(1).select().focus();
 }
 else
 {
  app.seance.execPasswd(textOld,textPass1,function(e)
  {
   $('.error',topw).text(e);
  },app.topw.showMessageForm);
 }
}

app.topw.submitListedForm=function()
{
 var nav=$('nav');
 var topw=$('.topw.listed',nav);
 var centre=$('.centre input',topw);
 var textCentre=centre.val();
 var addr=$('.addr input',topw);
 var textAddr=addr.val();
 var phone=$('.phone input',topw);
 var textPhone=phone.val();
 if(!textCentre.length)
 {
  $('.error',topw).text(app.txt.error_no_centre);
  centre.focus();
 }
 else if(!textAddr.length)
 {
  $('.error',topw).text(app.txt.error_no_addr);
  addr.focus();
 }
 else if(!textPhone.length)
 {
  $('.error',topw).text(app.txt.error_no_phone);
  phone.focus();
 }
 else
 {
  app.seance.execListed(location.href,textCentre,textAddr,textPhone);
 }
}

app.topw.initLoginForms=function()
{
 var nav=$('nav');
 app.msg=app.topw.showMessageForm;
 $('.topw.message .button.main',nav).click(app.topw.hideAll);
 $('#button-login').click(app.topw.toggleLoginForm);
 $('#button-listed').click(app.topw.showListedForm);
 //$('#button-login').click(app.topw.showMessageForm('Hello <a href="#">World</a>!'));
 $('.topw.login .link-forgot-password',nav).click(app.topw.switchToRestoreForm);
 $('.link-signup').click(app.topw.switchToSignupForm);
 $('.topw.login .button.main',nav).click(app.topw.submitLoginForm);
 $('.topw.signup .button.main',nav).click(app.topw.submitSignupForm);
 $('.topw.restore .button.main',nav).click(app.topw.submitRestoreForm);
 $('.topw.passwd .button.main',nav).click(app.topw.submitPasswdForm);
 $('.topw.listed .button.main',nav).click(app.topw.submitListedForm);
 $('.topw',nav).keydown(function(e)
 {
  if(e.which==13)
   $('.button.main',$(this)).click();
  else if(e.which==27)
   $('.topw:visible',nav).hide();
 });
 $('.topw .ui-icon-circle-close',nav).click(function()
 {
  $(this).parents('.topw').hide();
 });
 $('.topw .button.auth',nav).click(app.topw.onSocialClick);
 if(app.chgpwd)
  app.topw.showPasswdForm(false);
}

/*app.seance.signup=function()
{
 var form=$('#dlg-signup');
 if(!app.checkDlgFields(form))
  return false;
 var centre=encodeURIComponent($('#signup-centre').val().trim());
 var brand=encodeURIComponent($('#signup-brand',form).val().trim());
 var addr=encodeURIComponent($('#signup-addr',form).val().trim());
 var phone=encodeURIComponent($('#signup-phone',form).val().trim());
 var uri='signup?centre='+centre+'&brand='+brand+'&addr='+addr+'&phone='+phone;
 if(!app.seance.online)
 {
  uri+='&firstname='+encodeURIComponent($('#signup-firstname',form).val().trim());
  uri+='&lastname='+encodeURIComponent($('#signup-lastname',form).val().trim());
  uri+='&email='+encodeURIComponent($('#signup-email',form).val().trim());
 }
 var res=app.ajax(uri);
 if('uri' in res)
 {
  document.location=res.uri;
  return true;
 }
 if('error' in res)
 {
  app.msg(res.error);
  return false;
 }
 if(!('title' in res))
  return app.msg(app.txt.no_title_returned,app.txt.protocol_error,'#signup-email');
 if(!('message' in res))
  return app.msg(app.txt.no_message_returned,app.txt.protocol_error,'#signup-email');
 app.msg(res.message,res.title);
 form.dialog("close").dialog("destroy");
 return true;
}

app.seance.signin=function()
{
 var form=$('#dlg-signin');
 if(!app.checkDlgFields(form))
  return false;
 var firstname=$('#signin-firstname',form).val().trim();
 var lastname=$('#signin-lastname',form).val().trim();
 var email=$('#signin-email',form).val().trim();
 var pass=$('#signin-pass',form).val();
 var href=document.location.href;
 var params={firstname:firstname,lastname:lastname,email:email,pass:pass,href:href};
 var xhr=$.ajax(app.addParams('signin',params),{async:false});
 if(xhr.status!=200)
  return app.msg(xhr.status+': '+xhr.statusText,app.txt.server_error,'#signin-email');
 var res=eval('('+xhr.responseText+')');
 console.log(res);
 if('error' in res)
  return app.msg(res.error,app.txt.server_error,'#signin-email');
 if(!('title' in res))
  return app.msg(app.txt.no_title_returned,app.txt.protocol_error,'#signin-email');
 if(!('message' in res))
  return app.msg(app.txt.no_message_returned,app.txt.protocol_error,'#signin-email');
 app.msg(res.message,res.title);
 form.dialog("close").dialog("destroy");
 return true;
}

app.seance.restore=function()
{
 var form=$('#dlg-login');
 var email=$('#login-email',form).val().trim();
 if(!app.checkEmail(email))
  return $('#login-email',form).focus().select();
 var xhr=$.ajax('restore?email='+email,{async:false});
 if(xhr.status!=200)
  return app.msg(xhr.status+': '+xhr.statusText,app.txt.server_error,'#login-email');
 var req=eval('('+xhr.responseText+')');
 if('error' in req)
  return app.msg(req.error,app.txt.server_error,'#login-pass');
 if(!('title' in req))
  return app.msg(app.txt.no_title_returned,app.txt.protocol_error,'#login-email');
 if(!('message' in req))
  return app.msg(app.txt.no_message_returned,app.txt.protocol_error,'#login-email');
 app.msg(req.message,req.title);
 form.dialog("close").dialog("destroy");
 document.location=document.location;
 return true;
}

app.seance.login=function()
{
 var form=$('#dlg-login');
 if(!app.checkDlgFields(form))
  return false;
 var email=$('#login-email',form).val().trim();
 var pass=$('#login-pass',form).focus().select().val().trim();
 return app.seance.execLogin(email,pass,function(text,title){app.msg(text,title,'#login-pass')});
}

app.seance.logout=function()
{
 var xhr=$.ajax('logout',{async:false});
 if(xhr.status!=200)
  app.msg(xhr.status+': '+xhr.statusText,app.txt.server_error);
 else
 {
  var req=eval('('+xhr.responseText+')');
  if('error' in req)
   app.msg(req.error,app.txt.server_error);
  else
  {
   document.location.reload(true);
   return true;
  }
 }
 return false;
}*/

app.ifLogin=function(action,p1)
{
 if(typeof action=='string')
 {
  if(app.seance.online)
   app.go(action);
  else
   app.topw.showLoginForm(action);
 }
 else if(app.seance.online&&action)
  action(p1);
 else
  app.topw.showLoginForm();
 return false;
}

app.topw.onSocialClick=function()
{
 var form=$(this).parents('.topw').first();
 if(!form)
  return false;
 var values=app.checkDlgFields(form,true);
 if(!values)
  return false;
 document.cookie='auth_uri='+document.location.href+'; path='+app.home;
 for(var v in values)
 {
  document.cookie='auth_params='+JSON.stringify(values)+'; path='+app.home;
  break;
 }
 return true;
}

app.checkDlgFields=function(form,required)
{
 var fields=$('input.text',form);
 var values={};
 for(var i=0;i<fields.length;i++)
 {
  var field=$(fields[i]);
  if(required&&!field.hasClass('required'))
   continue;
  var val=field.val().trim();
  if(!val.length||field.hasClass('email')&&!app.checkEmail(val))
  {
   field.focus().select();
   return false;
  }
  if(!required)
   continue;
  var id=field.attr('id');
  var pos=id.lastIndexOf('-');
  if(pos>=0)
    id=id.substr(pos+1);
  values[id]=val;
 }
 return values;
}

app.clearDlgFields=function(form)
{
 $('input.text',form).each(function(i,item)
 {
  $(item).val('').removeClass('ui-state-error ui-state-error-text');
 });
}

app.onSocialClick=function()
{
 var form=$(this).parents('.dialog.form').first();
 if(!form)
  return false;
 var values=app.checkDlgFields(form,true);
 if(!values)
  return false;
 document.cookie='auth_uri='+document.location.href+'; path='+app.home;
 for(var v in values)
 {
  document.cookie='auth_params='+JSON.stringify(values)+'; path='+app.home;
  break;
 }
 return true;
}

app.initLoginForms=function()
{
 app.topw.initLoginForms();
 var login=$('#dlg-login');
 var signin=$('#dlg-signin');
 var signup=$('#dlg-signup');
 var enter=$('#button-login').text();
 $('.dialog.form input.text.email').change(function()
 {
  if(app.checkEmail($(this).val()))
   $(this).removeClass('ui-state-error').removeClass('ui-state-error-text');
  else
   $(this).addClass('ui-state-error').addClass('ui-state-error-text');
 })
 $('div.dialog.form .button.auth').click(app.onSocialClick);
 /*$('#button-login').click(function()
 {
  app.clearDlgFields(login);
  login.dialog({modal:true,resizable:false
  ,buttons:[{text:enter,click:app.seance.login}]
  ,open:function(){$('#login-email',login).focus();}
  ,close:function(){app.clearDlgFields(login);}});
 });*/
 $('#login-restore').click(app.seance.restore);
 $('#login-signin').click(function()
 {
  var email=$('#login-email').val();
  login.dialog("close").dialog("destroy");
  app.clearDlgFields(signin);
  $('#signin-email').val(email);
  signin.dialog({modal:true,resizable:false,buttons:[{text:enter,click:app.seance.signin}]
  ,open:function(){$('#signin-name',signin).focus();}
  ,close:function(){app.clearDlgFields(signin);}});
 });
 login.keydown(function(event){if(event.keyCode==$.ui.keyCode.ENTER)app.seance.login();});
 signin.keydown(function(event){if(event.keyCode==$.ui.keyCode.ENTER)app.seance.signin();});
 signup.keydown(function(event){if(event.keyCode==$.ui.keyCode.ENTER)app.seance.signup();});
}

//// Autocomplete engine ////

app.acKeyDown=function(e)
{
 var _this=$(this);
 if(e.which==27)
 {
  _this.parent().removeClass('has-text');
  _this.val('');
 }
 _this.data('acid','');
 if(_this.parents('#list-filter').length)
  app.onListFilterChanged();
 if((e.which==13)&&_this.parent().hasClass('brand'))
 {
  var value=_this.val();
  if(value.length>=3)
   app.acSelect.call(this,e,{item:{value:value}});
 }
}

app.acIconControl=function(input)
{
 if(input.val().length)
  input.parent().addClass('has-text');
 else
  input.parent().removeClass('has-text');
}

app.acSelect=function(e,ui)
{
 var self=$(this);
 if(self.parents('header').length)
 {
  setTimeout(function()
  {
   app.go('list/bnd-'+encodeURIComponent(ui.item.value)+'/');
   self.parent().removeClass('has-text');
   self.val('').data('acid','');
  },1);
 }
 else
 {
  self.val(ui.item.label);
  if(self.parents('#list-filter').length)
   app.onListFilterChanged();
  self.parent().addClass('has-text');
  if(!self.parents('brand').length)
   self.data('acid',ui.item?ui.item.id:'');
  if(self.parents('#home-filter').length)
   app.makeHomeSearchURI();
 }
}

app.acLeftIconClick=function()
{
 var input=$(this).parent().children('input');
 if($(this).hasClass('ui-icon-search'))
  input.achighlight('search',input.achighlight().term);
 else if($(this).hasClass('ui-icon-calendar'))
  input.datepicker('show');
 if($(this).parents('#list-filter').length)
  app.onListFilterChanged();
}

app.acRightIconClick=function()
{
 if($(this).parents('#list-filter').length)
  app.onListFilterChanged();
 $(this).parent().removeClass('has-text').children('input').val('').data('acid','').focus();
}

app.acSourceBrand=function(request,response)
{
 $.getJSON('ac_brand','q='+request.term,response);
}

app.acSourceMenuProc=function(request,response)
{
 if(!request.term.length)
  response(app.acMenuProc);
 else
  $.getJSON('ac_mproc','q='+request.term,response);
}

app.acSourceMenuTerr=function(request,response)
{
 if(!request.term.length)
 {
  response(app.acMenuTerr);
  return;
 }
 //console.log('terr request');
 //console.log(request);
 app.g.geo.geocode({"address":request.term},function(results,status)
 {
  //console.log(status);
  var list=[];
  if (status==google.maps.GeocoderStatus.OK)
  {
   //return app.msg(app.json(results));
   for(index in results)
   {
    var r=results[index];
    //console.log(r);
    if((r.types.indexOf("establishment")>=0)/*||(r.types.indexOf("route")>=0)*/)
     continue;
    var v=r.geometry.viewport;
    var n0=[],n1=[];
    for(var k0 in v)
    {
     n0.push(k0);
     n1.push([]);
     for(var k1 in v[k0])
      n1[n0.length-1].push(k1);
    }
    var y0=v[n0[0]][n1[0][0]];
    var y1=v[n0[0]][n1[0][1]];
    var x0=v[n0[1]][n1[1][0]];
    var x1=v[n0[1]][n1[1][1]];
    var b={y:(y0+y1)/2,x:(x0+x1)/2,dy:(y1-y0)/2,dx:(x1-x0)/2};
    //v={ Ba : { k : 55.710521, j : 55.79718 }, qa : { j : 37.51414399999999, k : 37.71298189999993 } }
    //var b={y:(v.ta.b+v.ta.d)/2,x:(v.ga.d+v.ga.b)/2,dy:(v.ta.b-v.ta.d)/2,dx:(v.ga.d-v.ga.b)/2};
    //var b={y:(v.Aa.k+v.Aa.j)/2,x:(v.qa.j+v.qa.k)/2,dy:(v.Aa.j-v.Aa.k)/2,dx:(v.qa.k-v.qa.j)/2};
    var id=""+Math.round(b.y*1000000)+"|"+Math.round(b.dy*1000000)+
      "|"+Math.round(b.x*1000000)+"|"+Math.round(b.dx*1000000);
    list.push({id:id,label:r.formatted_address});
   }
  }
  else if (status!=google.maps.GeocoderStatus.ZERO_RESULTS)
   list=[{id:'',label:'Google service has returned an error: <b>'+status+'</b>'}];
  response(list);
 });
}

app.initAutocomplete=function()
{
 app.declareACHighlight();
 $('.search-frame,.search-frame input.search-edit').addClass('ui-corner-all');
 $('.search-frame input.search-edit')
 .on('change',function(){app.acIconControl($(this))})
 .on('mouseup',function(){app.acIconControl($(this))})
 .on('keyup',function(){app.acIconControl($(this))})
 .on('keydown',app.acKeyDown)
 .data('acid','');
 $('.search-frame .search-icon-left').click(app.acLeftIconClick);
 $('.search-frame .search-icon-right').click(app.acRightIconClick);
 $('.search-frame .search-edit').not('.date')
 .on('click',function(){$(this).achighlight('search','')})
 .on('focus',function(){$(this).achighlight('search','')})
 .on('autocompletechange',function(){app.acIconControl($(this))})
 .on('autocompleteselect',function(){$(this).parent().addClass('has-text')})
 .filter('[placeholder-focus]')
 .each(function(i,edit){$(edit).data('ph',$(this).attr('placeholder'));})
 .on('focus',function(){$(this).attr('placeholder',$(this).attr('placeholder-focus'))})
 .on('blur',function(){$(this).attr('placeholder',$(this).data('ph'))});
 //$('#search-brand').parent()
 //.focusin(function(){$(this).children('input').animate({width:"300px"});})
 //.focusout(function(){$(this).children('input').animate({width:"200px"});});
 /// Brand
 $('header .brand .search-edit')
 .achighlight({minLength:1,source:app.acSourceBrand,select:app.acSelect});
 $('#list-filter .brand .search-edit')
 .achighlight({minLength:0,source:app.acSourceBrand,select:app.acSelect});
 /// Procedure
 $('.proc .search-edit')
 .achighlight({minLength:0,source:app.acSourceMenuProc,select:app.acSelect,isProc:true});
 /// Territory
 $('.terr .search-edit')
 .achighlight({minLength:0,source:app.acSourceMenuTerr,select:app.acSelect,isTerr:true});
 /// Date
 $('.date .search-edit')
 //.on('dateselect',function(){$(this).parent().addClass('has-text')})
 //.on('dateclear',function(){$(this).parent().removeClass('has-text')})
 .datepicker(
 {
  hideIfNoPrevNext:true
 //,showButtonPanel:true
 //,numberOfMonths:2
 ,minDate:0
 //,maxDate:"+1M -1D"
 ,onSelect:function(date)
 {
  $(this).parent().addClass('has-text');
  $(this).data('acid',$(this).datepicker('getDate'));
  app.onListFilterChanged();
 }
 });
}

//// List engine ////

app.makePriceHTML=function(curr,price,fact,nostyle)
{
 var html=app.addCurr(fact?('<s>'+price+'</s> '+fact):price,curr,true,true);
 if(!nostyle)
  html=html.replaceAll('<s>','<s class="ui-state-highlight">');
 return html;
}

app.makeHomeSearchURI=function()
{
 var uri='list/';
 var filter=$('#home-filter');
 var proc=$('.proc .search-edit',filter).data('acid');
 if(proc.length)
  uri+=((proc.substr(0,1)=='c')?'cat':'prc')+'-'+proc.substr(1)+'/';
 var editTerr=$('.terr .search-edit',filter)
 var terr=''+editTerr.data('acid');
 if(terr.length)
  uri+='loc-'+encodeURIComponent(terr)+'/locT-'+encodeURIComponent(editTerr.val())+'/';
 //var date=''+$('.date .search-edit',filter).data('acid');
 //if(date.length)
 // uri+='date-'+date+'/';
 $('#button-search-start').attr('href',uri);
}

app.initListFilterTimeout=function(active)
{
 if(app.list.timeoutSearch)
  clearTimeout(app.list.timeoutSearch);
 if(active)
 {
  app.list.timeoutSearch=setTimeout(app.listSearchAgain,1000);
  $('#button-search-again').addClass('main ui-state-error');
 }
 else
  app.list.timeoutSearch=null;
}

app.onListFilterChanged=function()
{
 app.initListFilterTimeout(true);
}

app.fillListFilter=function()
{
 //log('app.fillListFilter');
 var filter=$('#list-filter');
 function apply(cls,name,kind)
 {
  var edit=$('.'+cls+' .search-edit',filter);
  var value=app.nvl(app.list.filter[name],'');
  edit.data('acid',value).parent().toggleClass('has-text',value!='');
  if((value!='')&&(kind!=1))
  {
   if(kind==2)
   {
    edit.datepicker('setDate',value.toDate());
    return;
   }
   value=app.nvl(app.list.filter[name+'T'],'');
  }
  edit.val(value);
 }
 apply('date','date',2);
 apply('brand','bnd',1);
 apply('terr','loc',0);
 var time=''+app.list.filter['time'];
 $('.date .options input:checkbox',filter).each(function(i)
 {
  this.checked=time.indexOf(i+1)>=0;
 });
 $('.soc .options input:checkbox',filter).each(function()
 {
  this.checked=app.list.filter.soc.indexOf(this.value)>=0;
 });
 var proc_options=$('.proc .options',filter);
 proc_options.each(function(i,cat)
 {
  $(cat).data('shown',$.grep(app.list.filter.cat,function(a){return a==cat.id.substr(14)}).length>0);
 });
 $('input:checkbox',proc_options).each(function(i,prc)
 {
  if($.grep(app.list.filter.prc,function(a){return a==prc.id.substr(9)}).length>0)
  {
   $(this).parents('.options').data('shown',true);
   this.checked=true;
  }
  else
   this.checked=false;
 });
 proc_options.each(function(i,cat)
 {
  if($(cat).data('shown'))
   $(cat).show();
  else
   $(cat).hide();
 });
//log('app.fillListFilter - end');
}

app.makeListSearchURI=function()
{
 var uri='list/';
 var filter=$('#list-filter');
 var soc=$('.soc input:checkbox:checked',filter).toArray().map(function(s){return parseInt(s.value);}).join('-');
 if(soc.length)
  uri+='soc-'+soc+'/';
 var proc=$('.proc input:checkbox:checked',filter);
 if(proc.length)
  uri+='prc-'+proc.toArray().map(function(p){return parseInt(p.id.substr(9));}).join('-')+'/';
 var cat=$('.proc .options',filter).not(':has(input:checked)').filter(function(){return $(this).css('display')=='block';});
 if(cat.length)
 {
  uri+='cat';
  cat.each(function(){uri+='-'+$(this).attr('id').substr(14);});
  uri+='/';
 }
 var editTerr=$('.terr .search-edit',filter)
 var terr=''+editTerr.data('acid');
 if(terr.length)
  uri+='loc-'+encodeURIComponent(terr)+'/locT-'+encodeURIComponent(editTerr.val())+'/';
 var bnd=''+$('.brand .search-edit',filter).val();
 if(bnd.length)
  uri+='bnd-'+encodeURIComponent(bnd)+'/';
 var date=''+$('.date .search-edit',filter).data('acid');
 if(date.length)
  uri+='date-'+date+'/';
 var time=$('.date .options input:checked',filter);
 if(time.length)
 {
  var value='';
  time.each(function(i){
   value+=''+$(time[i]).attr('value');
  });
  uri+='time-'+value+'/';
 }
 return uri;
}

app.listSearchAgain=function()
{
 $('#button-search-again').removeClass('main ui-state-error');
 app.initListFilterTimeout(false);
 app.go(app.makeListSearchURI());
}

app.listSearchMore=function()
{
 if(app.list.finished||app.list.timeoutSearch||(app.list.result.centres.length>=app.list.result.count))
  return;
 var index=app.list.index;
 var uri=app.makeListSearchURI()+'?a=&skip='+app.list.result.centres.length;
 $('#list-result .footer .loading').show();
 $.ajax(uri,{processData:false,success:function(data,textStatus,jqXHR)
 {
  if(app.list.index!=index)
   return;
  $('#list-result .footer .loading').hide();
  var result=eval('('+data+')');
  $.each(result.centres,app.appendListResultItem);
  if(result.centres.length<20)
   app.list.finished=true;
  setTimeout(app.onListTargetResize,20);
  app.onListTargetResize();
 }});
}

app.deleteListResultItems=function()
{
 $('#list-result table.cols .col').empty('.centre');
}

app.appendListResultItem=function(i,centre)
{
 if($('#list-centre-'+centre.id).length)
  return;
 app.list.result.centres.push(centre);
 app.createListResultItem(app.list.result.centres.length,centre);
}

app.createListResultItems=function()
{
 //log('app.createListResultItems');
 if(app.list.result)
  $.each(app.list.result.centres,app.createListResultItem);
 //log('app.createListResultItems - end');
}

app.listFindSmallestCol=function()
{
 var cols=$('#list-result table.cols .data-col:visible .col');
 if(!cols[0])
  return $($('#list-result table.cols .data-col .col')[0]);
 var colIndex=0,minHeight=$(cols[0]).height();
 cols.each(function(i,col)
 {
  var height=$(col).height();
  if(height<minHeight)
  {
   minHeight=height;
   colIndex=i;
  }
 });
 return $(cols[colIndex]);
}

app.savedListResultColWidth=null;
app.listResultColWidth=function()
{
 if(!app.savedListResultColWidth)
  app.savedListResultColWidth=$('#list-result table.cols td .col').first().width();
 return app.savedListResultColWidth;
}

app.createListResultItem=function(i,centre)
{
 var col=app.listFindSmallestCol();
 if(!centre.node)
 {
  var divCentre=$('<a href="ctr-'+centre.id+'/" id="list-centre-'+centre.id+'" class="ax centre ui-widget-content">');
  var divImage=$('<div class="image">');
  if(centre.image&&centre.image.src&&centre.image.w&&centre.image.h)
   divImage.css({backgroundImage:'url("'+centre.image.src+'")'}).attr('rto',centre.image.h/centre.image.w);
  var divBrandContainer=$('<div class="brand-container">');
  divBrandContainer.append($('<div class="brand">').html(centre.title));
  divBrandContainer.append($('<div class="addr">').html(centre.addr));
  divCentre.append(divImage).append(divBrandContainer);
  var divSrvs=$('<div class="srvs">');
  if('srvs' in centre)
   $.each(centre.srvs,function(i,srv)
   {
    var aSrv=$('<a class="ax srv">').attr('href','srv-'+srv.id+'/');
    if(!i)
     aSrv.addClass('first');
    var divSrv=$('<div class="title">').html(srv.title);
    var price=app.makePriceHTML(centre.curr,srv.price,srv.fact);
    var tip=$('<div class="tip">');
    tip.append($('<div class="price">').html(price));
    if('dura' in srv)
     tip.append($('<div class="dura ui-state-highlight">')
     .append($('<span class="ui-icon ui-icon-clock">'))
     .append($('<div class="text">').html(srv.dura+'&nbsp;'+app.txt.minutes)));
    aSrv.append(divSrv).append(tip);
    divSrvs.append(aSrv);
   });
  var aMore=$('<a class="more" href="ctr-'+centre.id+'/#srvs">').text(app.txt.list_more);
  divCentre.append(divSrvs.append(aMore));
  centre.node=divCentre;
 }
 col.append(centre.node);
}

app.showListResult=function()
{
 //log('app.showListResult');
 app.list.index++;
 app.list.finished=false;
 $('#list-result .footer .loading').hide();
 app.deleteListResultItems();
 if(app.list.result)
 {
  if(app.list.result.counts)
  {
   $('#list-filter .proc .option .value').each(function(i,v)
   {
    var id='p'+$(v).parents('.option').find('input').attr('id').substr(9);
    $(v).text((id in app.list.result.counts)?app.list.result.counts[id]:'');
   });
  }
  else
   $('#list-filter .proc .option .value').empty();
  $('#art-list .header').html(app.list.result.header);
 }
 app.createListResultItems();
 setTimeout(app.onListTargetResize,20);
 app.onListTargetResize();
 //log('app.showListResult - end');
}

var savedListResultMargin;
var savedListResultHeaderMargin;

app.hideListFilter=function()
{
 var filter=$('#list-filter');
 var result=$('#list-result');
 if(filter.css('margin-left')!='0px')
  return;
 savedListResultMargin=result.css('margin-left');
 savedListResultHeaderMargin=$('.header',result).css('margin-left');
 app.noresize=true;
 filter.toggle(0,function()
 {
  app.noresize=false;
  setTimeout(app.onListTargetResize,1);
 });
}

app.showListFilter=function()
{
 app.noresize=true;
 $('#list-filter')
 .toggle(0,function()
 {
  app.noresize=false;
  setTimeout(app.onListTargetResize,1);
 });
}

app.initListFilter=function()
{
 var art=$('#art-list');
 //$('#button-search-start').click(app.homeSearchStart);
 //$('#button-search-again').click(app.listSearchAgain);
 var filter=$('#list-filter');
 $('.topbtn',filter).click(app.hideListFilter);
 $('.handle',art).click(app.showListFilter);
 $('.topbtn',art).hover(function(){$(this).addClass('ui-state-hover')},function(){$(this).removeClass('ui-state-hover')});
 $('.date .options .button',filter).click(function()
 {
  var offset=parseInt($(this).attr('offset'))*86400*1000;
  var date=(new Date(Date.now()+offset));
  var edit=$('.date .search-edit',filter);
  edit.datepicker('setDate',date);
  edit.parent().addClass('has-text');
  edit.data('acid',date);
  app.onListFilterChanged();
 });
 $('.date .option',filter).click(function()
 {
  app.onListFilterChanged();
 });
 $('.soc .option',filter).click(function()
 {
  app.onListFilterChanged();
 });
 $('.proc .item',filter).click(function()
 {
  var id=$(this).attr('id').substr(14);
  var options=$('#list-cat-opts-'+id,$(this).parent());
  if(options.css('display')=='none')
  {
   $('.more,.hide',options).hide();
   $('.show',options).css('display','inline-block');
  }
  else
  {
   $('input:checkbox',options).each(function(){this.checked=false;});
  }
  options.slideToggle(300);
  app.onListFilterChanged();
 });
 $('.options .buttons',filter).children().click(function()
 {
  var show=$(this).hasClass('show');
  $(this).hide();
  $(this).parent().children(show?'.hide':'.show').css('display','inline-block');
  var more=$(this).parents('.options').children('.more');
  more.slideToggle(20*more.children().length);
 })
 //$('.options .show',filter).prepend($('<span class="ui-icon ui-icon-circle-triangle-s"></span>'));
 //$('.options .hide',filter).prepend($('<span class="ui-icon ui-icon-circle-triangle-n"></span>'));
 $('.option input:checkbox',filter).click(app.onListFilterChanged);
 //$('.option .value',filter).addClass('ui-priority-secondary');
}//search-icon-left

app.initListResult=function()
{
}

//// Centre page ////

app.initCtrHeader=function()
{
}

app.setupCtr=function(init)
{
 app.setupCtrHeader();
 app.setupCtrSidebar();
 app.setupGalleryImages($('#art-ctr .detail .gallery'),app.ctr.images);
 if(!init)
  app.setupCtrOverview();
 app.setupCtrServices();
 app.setupCtrReviews();
 app.showCtrRatings();
}

app.setupCtrHeader=function()
{
 var header=$('#art-ctr .header');
 $('.title .type',header).text(app.ctr.typeT);
 $('.title .centre',header).text(app.ctr.title);
 if(app.ctr.bndT.length)
  $('.title .brand a',header).text(app.ctr.bndT)
  .attr('href','bnd-'+app.ctr.bnd+'/');
 else
  $('.title .brand a',header).text('').attr('href','');
 $('.subtitle .loc a',header).text(app.ctr.addr)
 .attr('href',app.ctr.loc?('list/'+encodeURIComponent(app.ctr.loc.bounds)+'/locT-'+encodeURIComponent(app.ctr.addr)+'/'):'');
}

app.setupCtrSidebarMap=function(bar)
{// Schedule
 var loc=$('.loc',bar);
 if(app.ctr.loc)
 {
  $('a.map',loc).attr('href',app.ctr.loc.dynamicURI);
  $('img.map',loc).attr('src',app.ctr.loc.staticURI);
  loc.show();
 }
 else
 {
  loc.hide();
  $('a.map',loc).attr('href','');
  $('img.map',loc).attr('src','');
 }
}

app.setupCtrSidebarMetros=function(bar)
{// Metro stations
 var metros=$('.card .metros',bar);
 var list=$('.list',metros);
 list.html(null);
 if(app.ctr.metros)
  $.each(app.ctr.metros,function(i,metro){list.append($('<div class="metro">').text(metro));});
 metros.toggle(!!(app.ctr.metros&&app.ctr.metros.length));
}

app.setupCtrSidebarPhones=function(bar)
{// Phone numbers
 var phones=$('.card .phones',bar);
 var list=$('.list',phones);
 list.html(null);
 if(app.ctr.phones&&app.ctr.phones.length)
  $.each(app.ctr.phones,function(i,phone){list.append($('<div class="phone">').text(phone));});
 phones.toggle(!!(app.ctr.phones&&app.ctr.phones.length));
}

app.setupCtrSidebarSched=function(bar)
{// Schedule
 var body=$('.card .sched tbody',bar);
 body.html('');
 if(app.ctr.sched&&app.ctr.sched.length)
 {
  $.each(app.ctr.sched,function(i,day)
  {
   var tr=$('<tr>');
   tr.append($('<td>').text(day[0]));
   if(day[1])
    tr.append($('<td>').text(day[1][0])).append($('<th>').text('-')).append($('<td>').text(day[1][1]));
   else
    tr.append($('<th colspan="3">').text(app.txt.closed));
   body.append(tr);
  });
  $('.card .sched',bar).show();
 }
 else
 {
  $('.card .sched',bar).hide();
 }
}

app.formatCtrRating=function(value)
{
 return (''+(Math.floor(value*10+0.5)/10+0.01)).substr(0,3)+'<sub>/5</sub>';
}

app.setupCtrSidebar=function()
{
 var bar=$('#art-ctr .brief');
 $('.rating span.meta',bar).attr('outerHTML','')
 //$('.logo img',bar).attr('src',app.ctr.logo).parent().toggle(!!app.ctr.logo);
 app.setupCtrSidebarMap(bar);
 $('.card .title',bar).text(app.ctr.title);
 $('.card .addr',bar).text(app.ctr.addr);
 app.setupCtrSidebarMetros(bar);
 app.setupCtrSidebarPhones(bar);
 app.setupCtrSidebarSched(bar);
 $('.card',bar).toggle(
  !!(app.ctr.addr&&app.ctr.addr.length)||
  !!(app.ctr.metros&&app.ctr.metros.length)||
  !!(app.ctr.phones&&app.ctr.phones.length)||
  !!(app.ctr.sched&&app.ctr.sched.length)
 );
}

app.showHideMore=function(txt)
{
 if($('.text',txt).css('display')=='none')
  $('.more-form',txt).toggleClass('rd-hide',true);
 else if($('.text',txt).prop('scrollHeight')>0 && $('.text',txt).height()>0)
  $('.more-form',txt).toggleClass('rd-hide',$('.text',txt).prop('scrollHeight')<=$('.text',txt).height());
}

app.setupCtrOverview=function()
{
 var art=$('#art-ctr .descr');
 $('.text',art).html(app.ctr.descr);
 art.toggle(!!app.ctr.descr);
}

app.createCtrGroup=function(tagBody,group,anchorSrv)
{
 var tagGroup=$('<tr class="group ui-widget-header">').attr('grpid',group.id);
 tagGroup.append($('<td class="title">').text(group.title)
 .append($('<span class="size">').text('('+group.list.length+')')));
 tagGroup.append($('<td class="prices" colspan="2">').html(app.addCurr(group.price,app.ctr.curr,true,true)));
 tagBody.append(tagGroup);
 $.each(group.list,function(i,service)
 {
  if(!service.tips||!service.tips.length)
   return;
  var cls='ui-widget-content'+((service.id==anchorSrv)?' ui-state-error':'');
  var tagService=$('<tr class="srv '+cls+'">').attr('grp',group.id);
  var anchor='<a class="ax" href="srv-'+service.id+'/">';
  var mark='<a name="srv-'+service.id+'">';
  if(service.tips.length==1)
  {
   tagService.addClass("tip")
   var tip=service.tips[0];
   tagService.append($('<td class="name">').append($(mark)).append($(anchor).text(service.title)));
   var tagDura=$('<td class="dura">');
   if('dura' in tip)
    tagDura.append($(anchor).text(tip.dura).append($('<span class="ui-icon ui-icon-clock">')));
   else
    tagDura.append($(anchor).html('&nbsp;'));
   tagService.append(tagDura);
   tagService.append($('<td class="price">').append($(anchor).addClass('button main').html(app.makePriceHTML(app.ctr.curr,tip.price,tip.fact,1))));
  }
  else
  {
   tagService.addClass("tips")
   tagService.append($('<td class="name" colspan="4">').append($(mark)).append($(anchor).text(service.title)));
  }
  tagBody.append(tagService);
  if(service.tips.length>1)
  {
   $.each(service.tips,function(j,tip)
   {
    var tagTip=$('<tr class="tip '+cls+'">').attr('grp',group.id);
    anchor='<a class="ax" href="srv-'+service.id+'/?tip='+tip.id+'">';
    //tagTip.append($('<td class="name">').append($(anchor).html('&nbsp;')));
    tagTip.append($('<td class="name">').append($(anchor).text(tip.title||service.title)));
    var tagDura=$('<td class="dura">');
    if('dura' in tip)
     tagDura.append($(anchor).text(tip.dura).append($('<span class="ui-icon ui-icon-clock">')));
    else
     tagDura.append($(anchor).html('&nbsp;'));
    tagTip.append(tagDura);
    tagTip.append($('<td class="price">').append($(anchor).addClass('button main').html(app.makePriceHTML(app.ctr.curr,tip.price,tip.fact,1))));
    tagBody.append(tagTip);
   });
  }
 });
 tagBody.append($('<tr class=grp-splitter>').append($('<td colspan="4">').html('&nbsp;')));
}

app.setupCtrServices=function()
{
 var tagSrv=$('#art-ctr .services');
 var tagBody=$('.body',tagSrv);
 tagBody.empty();
 if(app.ctr.groups&&app.ctr.groups.length)
 {
  var uri=document.location.href;
  var pos=uri.indexOf('#srv-');
  var anchorSrv=(pos>=0)?parseInt(uri.substr(pos+5)):0;
  $.each(app.ctr.groups,function(i,group)
  {
   app.createCtrGroup(tagBody,group,anchorSrv);
  });
  $('.group',tagBody).click(function()
  {
   var grp=$(this).attr('grpid');
   $('[grp='+grp+']',tagBody).toggle();
  });
  app.setupButtons(tagSrv);
  tagSrv.show();
 }
 else
  tagSrv.hide();
}

app.modifyReviewStars=function(node,value)
{
 $('div',node).css('width',Math.round(value*20)+'%');
 if(value>2)
  $('.dark',node).removeClass('dark').addClass('light');
 else
  $('.light',node).removeClass('light').addClass('dark');
}

app.initReviewStars=function(node)
{
 $('tr:has(td)',node)
 .mouseenter(function(e)
 {
  $(e.currentTarget).addClass('ui-state-focus');
 })
 .mouseleave(function(e)
 {
  $(e.currentTarget).removeClass('ui-state-focus');
 });
 $('.edit.stars',node).val(0)
 .mousemove(function(e)
 {
  $.each([$(e.currentTarget)],function(i,t){app.modifyReviewStars(t,Math.ceil(5*e.offsetX/t.width()));});
 })
 .click(function(e)
 {
  $.each([$(e.currentTarget)],function(i,t){t.val(Math.ceil(5*e.offsetX/t.width()));});
 })
 .mouseleave(function(e)
 {
  $.each([$(e.currentTarget)],function(i,t){app.modifyReviewStars(t,t.val());});
 });
}

app.fillCtrReview=function()
{
 var review={rates:{}};
 var form=$('#dlg-review');
 $.each(['total','ambie','clean','staff','value'],function(i,item)
 {
  if(review.error)
   return;
  var rate=parseInt($('#rate-'+item,form).val());
  if(rate)
   review.rates[item]=rate;
  else
  {
   review.error=true;
   app.msg('Please rate the "'+$('label[for=rate-'+item+']',form).text()+'"',app.txt.write_review);
  }
 });
 if(review.error)
  return null;
 var text=$('textarea',form).val();
 if(text.length&&text.length<20)
 {
  app.msg(app.txt.review_short,app.txt.write_review);
  return null;
 }
 review.text=text;
 review.notif=!!$('#review-notifier',form).attr('checked');
 $('.prc-rates .edit.stars',form).each(function(i,item)
 {
  if(review.error)
   return;
  var rate=parseInt($(item).val());
  if(rate)
   review.rates['prc-'+item.id.substr(9)]=rate;
 });
 return review;
}

app.dialogCtrAddReview=function()
{
 var form=$('#dlg-review');
 $('textarea',form).val('');
 $('.edit.stars',form).val(0).children().width(0);
 var tagBody=$('.prc-rates tbody',form);
 tagBody.html('');
 $.each(app.ctr.ratings.cats,function(i,cat)
 {
  var tr=$('<tr>');
  tr.append($('<th colspan="2">').append($('<b>').text(cat.title)));
  tagBody.append(tr);
  $.each(cat.list,function(i,prc)
  {
   var tr=$('<tr>');
   tr.append($('<th>').append($('<label>').text(prc.title)));
   tr.append($('<td>').append($('<div>').attr('id','rate-prc-'+prc.id).addClass('edit stars small').append($('<div>').addClass('dark'))));
   tagBody.append(tr);
  });
 });
 app.initReviewStars(tagBody);
 $('#review-notifier',form).attr('checked',true);
 form.dialog({modal:true,resizable:true,width:'auto',buttons:[{text:app.txt.write_review,click:function()
 {
  var review=app.fillCtrReview();
  if(!review)
   return null;
  var params={};
  for(var key in review.rates)
   params[key]=review.rates[key];
  params['notif']=review.notif?'1':'0';
  if(review.text)
   params['text']=review.text;
  app.ajax(app.makeURI(params,'review'));
  form.dialog('close').dialog('destroy');
  app.go();
 }}]});
}

app.dialogCtrAddReviewComment=function()
{
 var form=$('#dlg-review-comment');
 var textarea=$('textarea',form);
 textarea.val('');
 var notifier=$('#review-comment-notifier',form);
 notifier.attr('checked',true);
 form.dialog({modal:true,resizable:true,width:'auto',buttons:[{text:app.txt.add_comment,click:function(e)
 {
  var text=textarea.val();
  if(!text.length)
   return app.msg(app.txt.comment_empty,app.txt.add_comment,textarea);
  if(text.length<20)
   return app.msg(app.txt.comment_short,app.txt.add_comment,textarea);
  var params={};
  params['review']=app.paramCtrReviewId;
  params['notif']=notifier.is(":checked")?'1':'0';
  params['text']=text;
  app.ajax(app.makeURI(params,'comment'));
  form.dialog('close').dialog("destroy");
  app.go();
  return null;
 }}]});
}

app.dialogCtrAddReviewCavil=function()
{
 var form=$('#dlg-review-cavil');
 var textarea=$('textarea',form);
 textarea.val('');
 var violation=$('#review-cavil-violation',form);
 violation.attr('checked',false);
 var illegal=$('#review-cavil-illegal',form);
 illegal.attr('checked',false);
 var notifier=$('#review-cavil-notifier',form);
 notifier.attr('checked',true);
 form.dialog({modal:true,resizable:true,width:'auto',buttons:[{text:app.txt.add_cavil,click:function()
 {
  if(!violation.is(":checked")&&!illegal.is(":checked"))
   return app.msg(app.txt.reason_empty,app.txt.add_cavil,violation);
  var text=textarea.val();
  if(!text.length)
   return app.msg(app.txt.cavil_empty,app.txt.add_cavil,textarea);
  if(text.length<20)
   return app.msg(app.txt.cavil_short,app.txt.add_cavil,textarea);
  var params='';
  if(app.paramCtrCommentId)
   params+='comment='+app.paramCtrCommentId+'&';
  else
   params+='review='+app.paramCtrReviewId+'&';
  params+='viol='+(violation.is(":checked")?'1':'0')+'&';
  params+='ilgl='+(illegal.is(":checked")?'1':'0')+'&';
  params+='notif='+(notifier.is(":checked")?'1':'0')+'&';
  params+='text='+encodeURIComponent(text);
  //app.ajax(document.location.href+'cavil?'+params);
  app.ajax(app.makeURI(params,'cavil'));
  form.dialog('close').dialog("destroy");
  app.go(document.location.href);
  return null;
 }}]});
}

app.initCtrReviews=function()
{
 app.initReviewStars($('#dlg-review .ctr-rates'));
 $('#button-ctr-write-review,#button-srv-write-review')
 .click(function(){app.ifLogin(app.dialogCtrAddReview)});
}

app.showCtrRatings=function(mode)
{
 mode=mode?mode:'ctr';
 var tagArt=$('#art-'+mode);
 $('.total .value',tagArt).html(app.ctr.ratings.facil.count?app.formatCtrRating(app.ctr.ratings.facil.total):'0.0');
 //$('.total .value',tagArt).html(app.formatCtrRating(app.ctr.ratings.facil.count?app.ctr.ratings.facil.total:0));
 $('.total .count span',tagArt).html(app.ctr.ratings.facil.count);
 app.modifyReviewStars($('.total .stars',tagArt),app.ctr.ratings.facil.total);
 $.each(['ambie','clean','staff','value'],function(i,item)
 {
  app.modifyReviewStars($('.facil .'+item+' .stars',tagArt),app.ctr.ratings.facil[item]);
 });
 var tagStat=$('.reviews .stat',tagArt);
 for(var i=5;i>0;i--)
 {
  $('.distr .rate'+i+' .bar',tagStat).css('width',(app.ctr.ratings.facil.count?Math.round(100*app.ctr.ratings.distr[i]/app.ctr.ratings.facil.count):0)+'%');
  $('.distr .rate'+i+' .value',tagStat).text(app.ctr.ratings.distr[i]);
 }
 var tagPrcs=$('.prcs tbody',tagStat);
 tagPrcs.html('');
 $.each(app.ctr.ratings.cats,function(i,cat)
 {
  if(!cat.rated)
   return;
  tagPrcs.append($('<tr>').append($('<th>').attr('colspan',2).text(cat.title)));
  $.each(cat.list,function(j,prc)
  {
   if(!prc.rcnt)
    return;
   tagPrcs.append($('<tr>').attr('title',prc.title+' ('+prc.rcnt+')')
   .append($('<td>').html('<label>'+prc.title+'</label> <i>('+prc.rcnt+')</i>'))
   .append($('<td>').append(app.createCtrStars(Math.round(100*prc.rsum/prc.rcnt)/100))));
  });
 });
}

app.createCtrStars=function(value)
{
 return $('<div class="stars small">')
 .html('<div class="'+(value>2?'light':'dark')+'" style="width:'+(value*20)+'%">');
}

app.createCtrReviewItem=function(review)
{
 var tagAvatar=$('<div class="avatar">')
 .append($('<a class="ax" href="clt-'+review.author+'/">')
 .append($('<img src="img/clt-'+review.author+'.png">')));
 var tagTitle=$('<div class="title ui-widget-header">')
 .append($('<div class="name">').text(review.name))
 .append($('<div class="written">').text(app.txt.written+': '+review.written));
 var tagRates=$('<div class="rates ui-widget-content">');
 var tagBrief=$('<div class="rate brief">')
 .append(app.createCtrStars(review.rateT)).append($('<div class="label">').text(app.txt.rate_total));
 var tagFull=$('<div class="full">')
 .append($('<div class="rate first">').append(app.createCtrStars(review.rateA)).append($('<div class="label">').text(app.txt.rate_ambie)))
 .append($('<div class="rate">').append(app.createCtrStars(review.rateS)).append($('<div class="label">').text(app.txt.rate_staff)))
 .append($('<div class="rate">').append(app.createCtrStars(review.rateC)).append($('<div class="label">').text(app.txt.rate_clean)))
 .append($('<div class="rate">').append(app.createCtrStars(review.rateV)).append($('<div class="label">').text(app.txt.rate_value)));
 if(review.prcRates)
 {
  var className=' first';
  $.each(review.prcRates,function(i,item){
   var id='p'+item.id;
   var title=null;
   $.each(app.acMenuProc,function(j,prc){
    if(prc.id==id)
     title=prc.value;
   });
   if(title)
   {
    tagFull.append($('<div class="rate'+className+'">').append(app.createCtrStars(item.rate)).append($('<div class="label">').text(title)));
    className='';
   }
  });
 }
 tagRates.append(tagBrief).append(tagFull);
 var tagText=$('<div class="text">').html(review.text);
 var tagCtrl=$('<div class="ctrl">')
 .append($('<div class="actions">')
 .append($('<a class="js action add-comment" href="#">').text(app.txt.add_comment))
 .append($('<a class="js action add-cavil" href="#">').text(app.txt.add_cavil)));
 var tagReview=$('<div class="review">')
 .attr('id','review-'+review.id)
 .append($('<a>').attr('name','review-'+review.id));
 var tagInner=$('<div class="inner">')
 .append(tagAvatar).append(tagTitle).append(tagRates).append(tagText).append(tagCtrl);
 if(review.comments)
 {
  var tagComments=$('<div class="comments">')
  .append($('<div class="comments-title big-title ui-widget-header">').text(app.txt.comments));
  $.each(review.comments,function(i,comment)
  {
   tagAvatar=$('<div class="avatar">')
   .append($('<a class="ax" href="clt-'+comment.author+'/">')
   .append($('<img src="img/clt-'+comment.author+'.png">')));
   var tagSubtitle=$('<div class="subtitle ui-widget-header">')
   .append($('<div class="name">').text(comment.name))
   .append($('<div class="written">').text(app.txt.written+': '+comment.written));
   tagText=$('<div class="text">').text(comment.text);
   tagCtrl=$('<div class="ctrl">')
   .append($('<div class="actions">')
   .append($('<a class="js action add-cavil" href="#">').text(app.txt.add_cavil)));
   tagComments.append($('<div class="comment">').attr('id','comment-'+comment.id)
   .append($('<a>').attr('name','comment-'+comment.id))
   .append(tagAvatar).append(tagSubtitle).append(tagText).append(tagCtrl));
  });
  tagInner.append(tagComments);
 }
 tagReview.append(tagInner);
 return tagReview;
}

app.setupCtrReviews=function(mode)
{
 mode=mode?mode:'ctr';
 var tagBody=$('#art-'+mode+' .reviews .body');
 tagBody.html('');
 $.each(app.ctr.reviews,function(i,review)
 {
  tagBody.append(app.createCtrReviewItem(review));
 });
 //$('.rates .full',tagBody).hide();
 $('.rates',tagBody).click(function(e)
 {
  $('.full',$(this)).slideToggle(300);
 });
 $('.action.add-comment',tagBody).click(function(e)
 {
  app.paramCtrReviewId=$(this).parents('.review').attr('id').substr(7);
  app.ifLogin(app.dialogCtrAddReviewComment);
 });
 $('.action.add-cavil',tagBody).click(function(e)
 {
  var comment=$(this).parents('.comment');
  app.paramCtrReviewId=comment.length?'':$(this).parents('.review').attr('id').substr(7);
  app.paramCtrCommentId=comment.length?comment.attr('id').substr(8):'';
  app.ifLogin(app.dialogCtrAddReviewCavil)
 });
}

//// Brand page ////

app.initBndHeader=function()
{
}

app.setupBnd=function(init)
{
 app.setupBndHeader();
 app.setupBndSidebar();
 app.setupGalleryImages($('#art-bnd .detail .gallery'),app.ctr.images);
 if(!init)
  app.setupBndOverview();
}

app.setupBndHeader=function()
{
 var header=$('#art-bnd .header');
 $('.title a',header).text(app.bnd.title);
}

app.setupBndSidebar=function()
{
 //var sidebar=$('#art-bnd .brief');
 //$('.logo img',sidebar).attr('src',app.bnd.logo).parent().toggle(!!app.bnd.logo);
}

app.setupBndOverview=function()
{
 $('#art-bnd .descr .text').html(app.ctr.descr);
 $('#art-bnd .descr').toggle(!!app.ctr.descr);
}

//// Service page ////

app.initSrv=function()
{
 $('#art-srv .brief .main-caption').click(function(){$('.book-hint',this).slideToggle('slow','swing');});
 $('#art-srv #srv-calendar').datepicker(
 {
  minDate:new Date()
 //,maxDate:"+1M -1D"
 ,onSelect:function(date){app.onSrvDateChanged($(this).datepicker('getDate'));}
 });
 if($(document).width()<=app.iphoneScreenWidth)
  $('#art-srv #srv-calendar').mouseup(function(){$('#art-srv .book-hint').toggle(false);});
 $('#art-srv #srv-tips ul').click(function(e)
 {
  var tip=$(e.toElement)
  if(e.toElement.tagName!='LI')
   tip=tip.parents('li');
  if((tip.length!=1)||tip.hasClass('selected'))
   return;
  $('li',this).removeClass('selected').removeClass('ui-state-active');
  tip.addClass('selected').addClass('ui-state-active');
  //app.srv.tip=($('li',this).length>1)?tip.attr('n'):0;
  app.srv.tip=tip.attr('n');
  app.setupSrvSlots();
  if(history&&('pushState' in history))
   history.pushState(document.title,document.title,app.makeSrvURI(app.srv.date,app.srv.tip));
 })
 //.on('mouseenter','li',function(){$(this).addClass('ui-state-hover')})
 //.on('mouseleave','li',function(){$(this).removeClass('ui-state-hover')});
 //$('#art-srv #srv-slots ul').click(function(e){app.ifLogin(app.actionSrvBook,e);})
 $('#art-srv #srv-slots ul').click(app.actionSrvBook);
 //.on('mouseenter','li',function(){var self=$(this);if(!self.hasClass('disabled'))self.addClass('ui-state-hover')})
 //.on('mouseleave','li',function(){$(this).removeClass('ui-state-hover')});
}

app.setupSrv=function(init)
{
 var header=$('#art-srv .header');
 $('.title a.srv',header).text(app.srv.title);
 $('.title a.srv',header).attr('href','ctr-'+app.ctr.id+'/#srv-'+app.srv.id);
 $('.subtitle a.ctr',header).text(app.ctr.title);
 $('.subtitle a.ctr',header).attr('href','ctr-'+app.ctr.id+'/');
 app.setupSrvSidebar();
 app.setupCtrReviews('srv');
 app.showCtrRatings('srv');
 var detail=$('#art-srv .detail');
 //app.setupGalleryImages($('.gallery',detail),app.ctr.images);
 if(!init)
  app.setupSrvOverview(detail);
}

app.setupSrvSidebar=function()
{
 var bar=$('#art-srv .brief');
 $('#book-form').appendTo(bar);
 $('.main-caption .book-hint',bar).toggle(false);
 $('.rating span.meta',bar).attr('outerHTML','')
 $('#art-srv #srv-calendar').datepicker('setDate',app.srv.date.toDate());
 app.setupSrvTips();
}

app.setupSrvTips=function()
{
 var tips=$('#srv-tips ul');
 tips.html('');
 $.each(app.srv.tips,function(i,tip)
 {
  var price=tip.price;
  for(i in tip.slots)
   if(tip.slots[i].p<price)
    price=tip.slots[i].p;
  var text=app.strInt(price);
  if(price<tip.price)
   text+='&nbsp;-&nbsp;'+app.strInt(tip.price);
  var li=$('<li>').attr('n',tip.id)//.addClass('ui-state-default')
  .append($("<div class='radio'>"))
  .append($("<div class='title'>").text(tip.title))
  .append($("<div class='dura'>").html("<span class='ui-icon ui-icon-clock'></span>"+tip.duration+' '+app.txt.minutes))
  .append($("<div class='price'>").html(app.addCurr(text,app.ctr.curr,true,true)));
  if(tip.id==app.srv.tip)
   li.addClass('selected').addClass('ui-state-active');
  tips.append(li);
 });
 app.setupSrvSlots();
 //$('#srv-tips').toggle((app.URI.param('date').length>0)&&(app.srv.tips.length>1));
 $('#srv-tips').toggle(false);
 $('#srv-slots').toggle((app.URI.param('date').length>0)&&(app.srv.tips.length>0));
}

app.setupSrvSlots=function()
{
 var slots=$('#srv-slots ul');
 slots.html('');
 var tip=app.findSrvTip();
 if(!tip)
  return;
 $.each(tip.slots,function(i,slot)
 {
  var li=$('<li>')//.addClass('ui-state-default')
  .attr('a',slot.a).attr('b',slot.b).attr('d',slot.d).attr('p',slot.p)
  .append($("<div class='icon'>").append($("<div class='ui-icon ui-icon-tag'>")))
  .append($("<div class='time'>").html(slot.aa))
  .append($("<div class='price'>").html(app.makePriceHTML(app.ctr.curr,app.strInt(tip.price),slot.p<tip.price?app.strInt(slot.p):'')));
  var title='';
  if('dt' in slot)
   li.attr('dt',slot.dt);
  if(!('c' in slot)||(slot.c>3))
   li.addClass('free');
  else if(slot.c)
  {
   if(slot.c==1)
    title+='Last available time slot<br>';
   else
    title+=slot.c+' available time slots left<br>';
   li.addClass('last');
  }
  else
  {
   title+='No available time slots<br>';
   li.addClass('busy');
  }
  if(slot.c)
   li.attr('c',slot.c);
  if('z' in slot)
  {
   li.addClass('booked').attr('z',slot.z.id);
   title+='<b>You have an appointment:</b><br>'+slot.z.a+' - '+slot.z.b;
   if (slot.z.srv_id==app.srv.id)
    li.addClass('ui-state-highlight');
   else
   {
    title+='<br>'+slot.z.s;
    if (slot.z.centre_id!=app.ctr.id)
     title+='<br>'+slot.z.c;
   }
   li.addClass('booked');
  }
  if('x' in slot)
   li.addClass('disabled').addClass('ui-state-disabled');
  if(title.length)
   li.attr('title',title).tooltip();
  slots.append(li);
 });
 $('#srv-slots').toggle((app.srv.tips.length>0)&&(app.srv.tips[0].slots.length>0));
}

app.findSrvTip=function()
{
 var tip=null;
 if(app.srv.tip)
 {
  for(var i in app.srv.tips)
  {
   if(app.srv.tips[i].id!=app.srv.tip)
    continue;
   tip=app.srv.tips[i];
   break;
  }
 }
 if(!tip&&app.srv.tips.length)
 {
  tip=app.srv.tips[0];
  app.srv.tip=tip.id;
 }
 return tip;
}

app.makeSrvURI=function(date,tip,cmd)
{
 if(typeof cmd=='undefined')
  cmd='';
 var params={date:date};
 if(app.srv.tips.length>1)
  params.tip=tip;
 return app.addParams('srv-'+app.srv.id+'/'+cmd,params);
}

app.onSrvDateChanged=function(date)
{
 var slots=$('#art-srv #srv-slots ul');
 slots.addClass('invalid');
 var uri=app.makeSrvURI(date,app.srv.tip,'tips');
 var res=app.ajax(uri);
 if(res)
 {
  app.URI=app.parseUri(uri);
  app.srv.date=date.toString();
  app.srv.tips=('tips' in res)?res.tips:[];
  app.findSrvTip();
  app.setupSrvTips();
 }
 slots.removeClass('invalid');
 if(history&&('pushState' in history))
  history.pushState(document.title,document.title,app.makeSrvURI(app.srv.date,app.srv.tip));
}

app.setupSrvOverview=function(node)
{
 $('.descr .text',node).html(app.srv.descr);
 $('.descr',node).toggle(!!app.srv.descr);
 $('.restr .text',node).html(app.srv.restr);
 $('.restr',node).toggle(!!app.srv.restr);
 $('.notes .text',node).html(app.srv.notes);
 $('.notes',node).toggle(!!app.srv.notes);
}

app.actionSrvBook=function(e)
{
 var target=e.toElement||e.originalEvent.target;
 var slot=$(target);
 if(target.tagName!='LI')
  slot=slot.parents('li');
 if((slot.length!=1)||slot.hasClass('disabled'))
  return true;
 var tip=app.findSrvTip();
 if(!tip)
  return true;
 var a=slot.attr('a');
 //var b=slot.attr('b');
 //var c=slot.attr('c');
 //var d=slot.attr('d');
 //var p=slot.attr('p');
 var URI=app.parseUri();
 URI.path=app.home+'pay/';
 var uri=app.addParams(URI,{tip:tip.id,date:app.srv.date,time:a/*,qty:1*/});
 //log(uri);return;
 //app.ifLogin(uri);
 app.go(uri);
 return false;
}

//// Payment ////

app.initPay=function()
{
 var art=$('#art-pay');

  // promo code
  inputProCode=$('.pro-code input',art);
  if(!!app.pay.proCode)
   inputProCode.val(app.pay.proCode);
  inputProCode.keyup(function(){app.setupPayBottom(art,true);})
   .focusout(function(){app.setupPayBottom(art);})
   .focus(function(){$('.pro-code',art).removeClass('error');});

 // check boxes
 $('.opts .checkbox label',art).click(function()
 {
  if(art.hasClass('has-disc'))
   return;
  var cb=$(this).parent();
  if(cb.hasClass('pay-now'))
   cb.parent().removeClass('offline').addClass('online');
  else
   cb.parent().removeClass('online').addClass('offline');
 });
 var divPhone=$('#art-pay .phone');
 var inputPhone=$('input',divPhone);
 inputPhone.keyup(function()
 {
  if(divPhone.hasClass('error')&&app.checkPhone(inputPhone.val()))
   divPhone.removeClass('error');
 });
 $('.button.back',art).click(function(){history.back();});
 $('.button.login',art).click(function(){app.topw.showLoginForm();return false;});
 $('.button.order',art).click(function()
 {
  $.ajax('//www.googleadservices.com/pagead/conversion/971340048/?label=ZIPKCP-wm2MQkPKVzwM&guid=ON&script=0');
  if(!app.seance.online)
  {
   app.topw.showLoginForm();
   return false;
  }
  var phone=inputPhone.val();
  if(!app.checkPhone(phone))
  {
   divPhone.addClass('error');
   return false;
  }
  divPhone.removeClass('error');
  var self=$(this);
  if (self.hasClass('button-clicked'))
   return false;
  self.addClass('button-clicked');
  var curr=(app.pay.curr||app.curr)[0].id;
  var descr=app.pay.ctrT+'; '+app.pay.srvT+'; '+app.pay.dateT+'; '+app.pay.timeT+'; '+
   app.pay.dura+'; '+app.pay.qty+'x'+app.pay.fact+'; '+app.pay.total+' '+curr;
  if(app.pay['proCodes'] && app.pay.proCode!=='')
   descr=descr+'; '+app.pay.proCode+' : -'+app.pay.proCodeAmnt.replace(/&nbsp;/g, '');
  var uriParams={
   dura:app.pay.dura,
   price:app.pay.price,
   disc:Math.round(100*(1-app.pay.total/app.pay.price)),
   fact:app.pay.fact,
   total:app.pay.total,
   curr:curr,
   ctr:app.pay.ctr,
   srv:app.pay.srv,
   descr:descr,
   phone:phone
  };
  if(app.pay.cmp_clnt_id)
   uriParams.cmp_clt_id=app.pay.cmp_clnt_id;
  if(app.pay.msgId)
   uriParams.msg=app.pay.msgId;
  var uri=app.makeURI(uriParams,'order');
  //log(uri);
  var res=app.ajax(uri);
  //log(res);
  if(!res||!('ref' in res)||!(res.ref>0))
  {
   var URI=app.parseUri();
   var params={date:URI.params.date,tip:URI.params.tip};
   uri=app.addParams('srv-'+app.pay.srv+'/',params);
   app.go(uri);
   return false;
  }
  app.go('pay/?ref='+res.ref);
  return false;
 });
 $('.button.book',art).click(function()
 {
  $.ajax('//www.googleadservices.com/pagead/conversion/971340048/?label=ZIPKCP-wm2MQkPKVzwM&guid=ON&script=0');
  if(!app.seance.online)
  {
   app.topw.showLoginForm();
   return;
  }
  var self=$(this);
  if (self.hasClass('button-clicked'))
   return;
  self.addClass('button-clicked');
  var curr=(app.pay.curr||app.curr)[0].id;
  var descr=app.pay.ctrT+'; '+app.pay.srvT+'; '+app.pay.dateT+'; '+app.pay.timeT+'; '+
   app.pay.dura+'; '+app.pay.qty+'x'+app.pay.fact+'; '+app.pay.total+' '+curr;
  if(app.pay['proCodes'] && app.pay.proCode!=='')
   descr=descr+'; '+app.pay.proCode+' : -'+app.pay.proCodeAmnt.replace(/&nbsp;/g, '');
  var params={
   dura:app.pay.dura,
   price:app.pay.price,
   disc:Math.round(100*(1-app.pay.total/app.pay.price)),
   fact:app.pay.fact,
   total:app.pay.total,
   curr:curr,
   ctr:app.pay.ctr,
   srv:app.pay.srv,
   descr:descr
  };
  if(app.pay.cmp_clnt_id)
   params.cmp_clt_id=app.pay.cmp_clnt_id;
  if(app.pay.msgId)
   params.msg=app.pay.msgId;
  var uri=app.makeURI(params,'book');
  //log(uri);
  var res=app.ajax(uri);
  //log(res);
  if(!res)
  {
   var URI=app.parseUri();
   var params={date:URI.params.date,tip:URI.params.tip};
   uri=app.addParams('srv-'+app.pay.srv+'/',params);
   app.go(uri);
   return;
  }
  if(!('pay' in res)||(typeof res.pay!='object'))
   {log('Invalid response');return;}
  if(!('uri' in res.pay)||(typeof res.pay.uri!='string'))
   {log('Invalid response');return;}
  if(!('data' in res.pay)||(typeof res.pay.data!='object'))
   {log('Invalid response');return;}
  var form=$('<form>').attr('action',res.pay.uri).attr('method','post');
  for(n in res.pay.data)
   form.append($('<input>').attr('type','hidden').attr('name',n).attr('value',res.pay.data[n]));
  $(document.body).append(form);
  form[0].submit();
 });
}

app.setupPay=function()
{
 var art=$('#art-pay');
 if(!app.pay||(app.pay.status!='OK'))
 {
  $('#art-pay>*').hide();
  return;
 }
 $('#art-pay>*').show();
 $('.button-clicked',art).removeClass('button-clicked');
 art.toggleClass('has-ref',!!app.pay.ref);
 $('.pro-code input',art).prop('disabled',!!app.pay.ref).toggleClass('noselect',!!app.pay.ref);
 if(!app.pay.ref)
  $('.pro-code input',art).val('');
 $('.ref-data .book-id .value',art).text(app.pay.ref);
 $('.ctr',art).text(app.pay.ctrT);
 $('.srv',art).text(app.pay.srvT);
 $('.date',art).text(app.pay.dateT);
 $('.time',art).text(app.pay.timeT);
 $('.dura',art).html(app.pay.dura+'&nbsp;'+app.txt.minutes);
 //$('.curr',art).text(app.pay.curr);
 $('.price .value',art).html(app.addCurr(app.strInt(app.pay.price),app.pay.curr,false,true))
 art.toggleClass('has-pro-code',!!app.pay['proCodes']);
 app.setupPayBottom(art);
}

app.setupPayBottom=function(art,pCodeFocus){
 art.toggleClass('has-disc',!!app.pay.disc);
 art.toggleClass('signin-disc',!!app.pay.signInDscAmnt);
 art.toggleClass('is-type-pay',app.pay.ref&&(app.pay.type=='P'));
 art.toggleClass('is-type-book',app.pay.ref&&(app.pay.type=='B'));

 $('.disc .prc',art).text(app.pay.disc);
 $('.disc .value',art).html(app.addCurr(app.strInt(app.pay.totalDiscount),app.pay.curr,false,true));

 $('.signin-disc-description',art).text(app.pay.sgnInDscDscr);
 $('.signin-disc-total',art).html(app.addCurr(app.strInt(app.pay.signInDscAmnt),app.pay.curr,false,true));

 $('.total .value',art).html(app.addCurr(app.strInt(app.pay.total),app.pay.curr,false,true));
 $('.change',art).attr('href',app.pay.hrefChg||app.base);

 if(app.pay['proCodes']){
  app.pay.proCode='';
  app.pay.proCodeAmnt=null;
  app.pay.msgId=null;
  var pCodes=app.pay['proCodes'];
  var pCode = null;
  if(app.pay.ref){
   pCode=pCodes;
   pCode['cnt']=1;
  }
  else
   pCode=app.checkProCode($('.pro-code input',art).val().toUpperCase(),pCodes);
  var vldPcode=(pCode && (pCode['cnt']==1 || !pCodeFocus));
  if(vldPcode){
   app.pay.msgId=pCode['cmp_msgs_id'];
   app.pay.proCode=pCode['promo_code'];
   app.pay.proCodeAmnt=app.addCurr(app.strInt(pCode['evnt_dsc_amnt']),app.pay.curr,false,true)
   $('.pro-code .disc-total',art).html(app.pay.proCodeAmnt);
  }
  else
   $('.pro-code',art).toggleClass('error',(!pCodeFocus && $('.pro-code input',art).val()!==''));
  art.toggleClass('has-pro-code-vld',!!vldPcode)
   .toggleClass('has-disc',!vldPcode && !!app.pay.disc)
   .toggleClass('signin-disc',!vldPcode && !!app.pay.signInDscAmnt);
 }

 var total=app.pay.price;
 if(!!app.pay.totalDiscount && $('.pay-bottom .disc.disc-show').css('display')!='none')
  total=total-app.pay.totalDiscount;
 if(!!app.pay.signInDscAmnt && $('.pay-bottom .disc.signin-disc-show').css('display')!='none')
  total=total-app.pay.signInDscAmnt;
 if(!!pCode && $('.pay-bottom .disc .pro-code-vld-show').css('display')!='none')
  total=total-pCode['evnt_dsc_amnt'];
 if(total<0)
  total=0;
 app.pay.total=total;
 app.pay.fact=app.pay.total;
 $('.total .amount .value',art).html(app.addCurr(app.strInt(app.pay.total),app.pay.curr,false,true));

 var booktype=app.pay.ctrBookType;
 if(total==0)
  booktype='B';
 switch(booktype)
 {
 case 'B':
  $('.opts',art).addClass('offline').removeClass('online disc-hide signin-disc-hide').toggleClass('pro-code-vld-hide',total!=0);
  $('.opts .checkbox,.opts .prompt',art).toggleClass('pro-code-vld-hide',total==0);
  $('.opts .phone .prompt',art).toggleClass('pro-code-vld-hide',total!=0);
  $('.pay-now',art).addClass('inactive');
  break;
 case 'P':
  $('.opts',art).addClass('online').removeClass('offline disc-hide signin-disc-hide');
  $('.pay-later',art).addClass('inactive');
  break;
 default:
  $('.opts',art).addClass('online disc-hide signin-disc-hide').removeClass('offline');
  $('.inactive',art).removeClass('inactive');
 }
}

//// Image gallery ////

app.gallery={startX:NaN,stop:false,pos:0,dir:1,interval:null}

app.initGalleryImages=function()
{
 $('article .detail .gallery')
 .mouseenter(function(){app.gallery.stop=true;app.gallery.startX=NaN;})
 .mouseleave(function(){app.gallery.stop=false;})
 .mousedown(function(){app.gallery.startX=event.pageX;})
 .mouseup(function(event){app.dragGalleryImages(event);app.gallery.startX=event.NaN;})
 .mousemove(app.dragGalleryImages);
}

app.dragGalleryImages=function(event)
{
 if(isNaN(app.gallery.startX))
  return;
 var gallery=$('article.active .detail .gallery');
 var images=$('.images',gallery);
 app.gallery.pos-=(event.pageX-app.gallery.startX);
 if(event.pageX>app.gallery.startX)
 {
  if(app.gallery.pos<0)
   app.gallery.pos=0;
  app.gallery.dir=-1;
 }
 else if(event.pageX<app.gallery.startX)
 {
  var max=images.width()-gallery.width();
  if(app.gallery.pos>max)
   app.gallery.pos=max;
  app.gallery.dir=1;
 }
 app.gallery.startX=event.pageX;
 images.css('margin-left','-'+app.gallery.pos+'px');
}

app.initGalleryImagesInterval=function(active)
{
 if(app.gallery.interval)
  clearInterval(app.gallery.interval);
 app.shiftGalleryImages(true);
 app.gallery.interval=active?setInterval(app.shiftGalleryImages,50):null;
}

app.shiftGalleryImages=function(start)
{
 if(!start&&app.gallery.stop)
  return;
 var gallery=$('article.active .detail .gallery');
 var images=$('.images',gallery);
 if(start)
 {
  app.gallery.stop=false;
  app.gallery.pos=0;
  app.gallery.dir=1;
 }
 else
 {
  app.gallery.pos+=app.gallery.dir;
  if(app.gallery.dir>0)
  {
   var max=images.width()-gallery.width();
   if(app.gallery.pos>max)
   {
    app.gallery.pos=max;
    app.gallery.dir=-1;
   }
  }
  else
  {
   if(app.gallery.pos<0)
   {
    app.gallery.pos=0;
    app.gallery.dir=1;
   }
  }
 }
 images.css('margin-left','-'+app.gallery.pos+'px');
}

app.setupGalleryImage=function(node,image)
{
 var width=Math.round(node.height()*image.width/image.height*10)/10;
 node.width(width)
 .css('background-image','url('+encodeURI(image.href)+')')
 .css('background-size','100%')
 .show();
 return width;
}

app.setupGalleryImages=function(gallery,images)
{
 var node_array=$('.image',gallery);
 if(!images||!images.length)
  return gallery.hide();
 var width=0;
 for(var i=0;i<node_array.length;i++)
 {
  var node=$(node_array.get(i));
  if(i<images.length)
   width+=app.setupGalleryImage(node,images[i]);
  else
   node.hide();
 }
 $('.images',gallery).width(width);
 return gallery.show();
}

app.initTopicTitle=function()
{
 $('.topic-title').click(function()
 {
  $('.more-form',$(this).parents('.small')).toggleClass('rd-hide',($(this).siblings('.text').css('display')!=='none'));
  $(this).siblings().not('.more-form').slideToggle(300);
 }).css('cursor','pointer');
};

app.initAdvTitle=function()
{
 $('.adv-title').click(function()
 {
  //log($(this).parent());
  //Why this is not working:
  //app.onHomeAdvResizeRow($(this).parent());
  $(this).siblings().toggle();
 }).css('cursor','pointer');
};

app.checkPhone=function(phone)
{
 if (phone==='')
  return false;
 var value = phone.replace('+','').replace(/-/g,'').replace(/ /g,'');
 if (value.substr(0,3)==='971' && value.substr(0,4)==='9715' && value.length===12)
  return true;
 if (value.substr(0,1)==='0' && value.substr(0,2)==='05' && value.length===10)
  return true;
 if (value.substr(0,3)!=='971' && phone.substr(0,1)==='+' && value.length>=10)
  return true;
 return false;
};

app.initTopMenu=function()
{
 $('header .hmenu-button').click(function(){$('nav .topw').hide();$('header .hmenu,.hmenu-background').toggleClass('hmenu-mobile');});
 $('header .hmenu .hitem').click(function(){$('nav .topw').hide();app.hideTopMenu();});
 $('header .hmenu-background').click(app.hideTopMenu);
 $('header .hmenu')
 .menu({position:{my:'left top',at:'left bottom'}})
 .find('.hitem .hsubmenu').parents('.hitem')
 .each(function(){this.submenu=$('.hsubmenu',this)[0]})
 .mouseleave(function(){var s=this.submenu;s.timer=setTimeout(function(){$(s).hide();},300)})
 .mouseenter(function(){if(this.submenu.timer)clearTimeout(this.submenu.timer)});
 $('header .hmenu').addClass('hmenu-on');
}

app.hideTopMenu=function()
{
// log('app.hideTopMenu');
 $('.hmenu-mobile').removeClass('hmenu-mobile');
}

//// Client profile engine ////

app.initClientMenu=function()
{
 var button=$('#button-client');
 var menu=$('#client-menu');
 menu.visible=false;
 var timer=null;
 function toggle(on)
 {
  if(on==null)
   on=!menu.visible;
  else if(on==menu.visible)
   return;
  //if(on) // doesn't work :-(
  // menu.css('right',(''+$('button-lang').width())+'px');
  menu.visible=on;
  menu.slideToggle(100);
 }
 menu.menu();
 button.click(function(){toggle(null)});
 $('#button-client,#client-menu')
 .mouseleave(function(){if(menu.visible)timer=setTimeout(function(){toggle(false);},300)})
 .mouseenter(function(){if(timer)clearTimeout(timer)});
 $('a',menu).click(function(){toggle(false);});
 //$('#client-menu-logout').click(function(){app.seance.logout();return false;});
 $('#client-menu-passwd').click(function(){app.topw.showPasswdForm(true);return false;});
 $('#client-menu-logout').click(function(){app.seance.execLogout(app.topw.showMessageForm);return false;});
}

app.cltEditingCloseAll=function(save,but)
{
 $('#art-clt tr.record.editing[field]').each(function(i,item)
 {
  if(item!=but)
   app.cltEditingClose($(item),save);
 });
}

app.makeCltName=function()
{
 app.clt.name=app.clt.lastname;
 if(app.clt.firstname.length)
  app.clt.name=app.clt.firstname+' '+app.clt.name;
 if(app.clt.title&&app.clt.title.length)
  app.clt.name=app.clt.title+' '+app.clt.name;
 $('#clt-name').text(app.clt.name);
 $('#button-client').text(app.clt.name);
}

app.cltEditingClose=function(rec,save)
{
 if(!rec.hasClass('editing'))
  return;
 rec.removeClass('editing');
 if(!save)
  return;
 var field=rec.attr('field');
 var params={field:field};
 switch(rec.attr('type'))
 {
 case 'text':
  var input=$('input',rec);
  params.value=input.val();
  if(params.value==app.clt[field])
   return;
  app.clt[field]=params.value;
  $('.value',rec).text(params.value);
  input.val('');
  if((field=='title')||(field=='firstname')||(field=='lastname'))
   app.makeCltName();
  break;
 case 'list':
  var select=$('select',rec);
  params.value=select.val();
  if(params.value==app.clt[field])
   return;
  app.clt[field]=params.value;
  $('.value',rec).text($('option[value="'+params.value+'"]',select).text());
  select.val('');
  break;
 case 'date':
  var input1=$('input[part="day"]',rec);
  var input2=$('input[part="mon"]',rec);
  var input3=$('input[part="year"]',rec);
  var d=parseInt(input1.val());
  var m=parseInt(input2.val());
  var y=parseInt(input3.val());
  if(d)
   params.d=d;
  if(m)
   params.m=m;
  if(y)
   params.y=y;
  var old=app.clt[field]||{};
  if((params.d==old.day)&&(params.m==old.mon)&&(params.y==old.year))
   return;
  app.clt[field]={day:params.d,mon:params.m,year:params.y};
  $('.value [part="day"]',rec).text(params.d);
  $('.value [part="mon"]',rec).text(params.m);
  $('.value [part="year"]',rec).text(params.y);
  input1.val('');
  input2.val('');
  input3.val('');
  break;
 }
 var uri=app.makeURI(params,'change');
 app.ajax(uri);
}

app.cltEditingOpen=function(rec,node)
{
 var field=rec.attr('field');
 app.cltEditingCloseAll(true,rec);
 if(rec.hasClass('editing'))
  return;
 rec.addClass('editing');
 var value=app.clt[field];
 var type=rec.attr('type')
 if(type=='text')
 {
  if($(document).width()>=app.ipadScreenWidth)
   $('input',rec).val(value).select().focus();
 }
 else if(type=='list')
 {
  var select=$('select',rec);
  if(!select[0].childNodes.length)
  {
   var res=app.ajax(app.makeURI({field:field},'list'));
   if(res&&res.list)
   {
    select.append($('<option>').attr('value',''));
    for(v in res.list)
     select.append($('<option>').attr('value',v).text(res.list[v]));
   }
  }
  if($(document).width()>=app.ipadScreenWidth)
   select.val(value).focus();
 }
 else if(type=='date')
 {
  $('input[part="day"]',rec).val(value?value.day:'');
  $('input[part="mon"]',rec).val(value?value.mon:'');
  $('input[part="year"]',rec).val(value?value.year:'');
  var part=$(node).attr('part');
  if(!part)
   part=value?(value.day?'day':value.mon?'mon':value.year?'year':'day'):'day';
  if($(document).width()>=app.ipadScreenWidth)
   $('input[part="'+part+'"]',rec).select().focus();
 }
}

app.initCltEditing=function()
{
 var art=$('#art-clt');
 $('tr.record[type]',art).click(function(e){app.cltEditingOpen($(this),e.target)});
 $('tr.record input,tr.record select',art)
 .blur(function()
 {
  var rec=$(this).parents('[field]');
  var field=rec.attr('field');
  if(!app.clt.timeouts)
   app.clt.timeouts={};
  app.clt.timeouts[field]=setTimeout(function(){app.cltEditingClose(rec,true);},500);
 })
 .focus(function()
 {
  var rec=$(this).parents('[field]');
  var field=rec.attr('field');
  if(app.clt.timeouts&&app.clt.timeouts[field])
  {
   clearTimeout(app.clt.timeouts[field]);
   app.clt.timeouts[field]=null;
  }
 })
 .keydown(function(e)
 {
  if(e.keyCode==$.ui.keyCode.ENTER)//if(e.which==13)
   app.cltEditingClose($(this).parents('[field]'),true);
  else if(e.keyCode==$.ui.keyCode.ESCAPE)//if(e.which==27)
   app.cltEditingClose($(this).parents('[field]'));
 });
}

app.initClt=function()
{
 app.initCltEditing();
 var art=$('#art-clt');
 $('[field] .change',art).click(function()
 {
  if(!art.hasClass('editable'))
   return;
  var field=$(this).parents('[field]').attr('field');
  var dlg=null;
  var params={modal:true,resizable:false,buttons:[{text:'OK',click:function(){}}]};
  switch(field)
  {
  case 'img':
   dlg=$('#dlg-clt-img');
   params.width=600;
   params.buttons[0]={text:app.txt.button_upload,click:app.uploadCltImg};
   params.buttons[1]={text:app.txt.button_delete,click:app.clearCltImg};
   break;
  case 'note':
   dlg=$('#dlg-clt-note');
   $('.value',dlg).val(app.clt.note.replaceAll("<br/>","\n"));
   params.buttons[0].click=app.editCltNote;
   break;
  default:
   return;
  }
  dlg.dialog(params);
 });
 $('#dlg-clt-img input[type="file"]').change(function(event)
 {
  var file=event.target.files.length?event.target.files[0]:null;
  if(file&&file.size>300000)
  {
   app.msg(app.txt.error_file_too_large+': '+file.size);
   $(this).val('');
   file=null;
  }
  app.upload.file=file;
  var dlg=$('#dlg-clt-img');
  if(file)
   dlg.dialog('option','buttons')[0].click();
  else
   dlg.dialog('close');
 });
 $('#dlg-clt-name,#dlg-clt-gender,#dlg-clt-birthday')
 .keydown(function(event)
 {
  if(event.keyCode==$.ui.keyCode.ENTER)
   $(this).dialog('option','buttons')[0].click();
 });
}

app.setupClt=function()
{
 var art=$('#art-clt');
 art.toggleClass('editable',!!app.clt.edit);
 $('.obj-header .public-view',art).attr('href','clt-'+app.clt.id+'/?view=public');
 $('#clt-img').attr('src',app.clt.img);
 $('.record',art).each(function(i,item)
 {
  var rec=$(item);
  var field=rec.attr('field');
  var value=app.clt[field];
  switch(rec.attr('type'))
  {
  case 'text' :
   $('.value',rec).text(value);
   rec.toggle(app.clt.edit||!!value);
   break;
  case 'list' :
   //var text=((field+'T')in app.clt)?app.clt[field+'T']:value;
   var text=app.clt[field+'T']||value;
   $('.value',rec).text(text);
   rec.toggle(app.clt.edit||!!text);
   break;
  case 'date' :
   var d=value?parseInt(value.day):0;
   var m=value?parseInt(value.mon):0;
   var y=value?parseInt(value.year):0;
   $('.value [part="day"]',rec).text(d&&m?(""+(100+d)).substr(1):'??');
   $('.value [part="mon"]',rec).text(m?(""+(100+m)).substr(1):'??');
   $('.value [part="year"]',rec).text(y?y:'????');
   rec.toggle(app.clt.edit||!!(d+m+y));
   break;
  }
 });
 app.makeCltName();
 $('#clt-note',art).html(app.clt.note);
 $('.descr',art).toggle(app.clt.edit||app.clt.note.length>0);
}

app.upload={'file':null};

app.uploadCltImg=function()
{
 if(!app.upload.file)
  return false;
 var data=new FormData();
 data.append('image',app.upload.file);
 app.upload.file=null;
 var res=$.ajax(
 {
  url: app.makeURI({field:'avatar',v:new Date().getTime()},'upload'),
  type: 'post',
  data: data,
  async: false,
  cache: false,
  dataType: 'json',
  processData: false, // Don't process the files
  contentType: false  // Set content type to false as jQuery will tell the server its a query string request
 });
 if(res.readyState!=4)
  return app.msg('Invalid res.readyState: '+res.readyState);
 if(!res.responseJSON)
  return app.msg('Invalid res.responseText: '+res.responseText);
 if('error' in res.responseJSON)
  return app.msg('Error: '+res.responseJSON.error);
 if('failure' in res.responseJSON)
  return app.msg('Failure: '+res.responseJSON.failure);
 if(res.responseJSON.result!='OK')
  return app.msg('Invalid res.responseJSON.result: '+res.responseJSON.result);
 if(!res.responseJSON.data)
  return app.msg('Invalid res.responseJSON: no data in response');
 $('header #button-client').css('background-image','url('+res.responseJSON.data.pic+')');
 $('#art-clt #clt-img').attr('src',res.responseJSON.data.photo);
 $('#dlg-clt-img').dialog('close');
 return true;
}

app.clearCltImg=function()
{
 if(!confirm(app.txt.clear_avatar+'?'))
  return;
 var res=app.ajax(app.makeURI({field:'avatar'},'clear'));
 if(!res)
  return;
 $('header #button-client').css('background-image','url('+res.data.pic+')');
 $('#art-clt #clt-img').attr('src',res.data.photo);
 $('#dlg-clt-img').dialog('close');
}

app.editCltGender=function()
{
 var dlg=$('#dlg-clt-gender');
 var radio=$('.gender input:checked',dlg);
 var gender=radio.val();
 if(!app.ajax(app.makeURI({field:'gender',value:gender},'change')))
  return;
 app.clt.gender=gender;
 $('#art-clt .records .record[field="gender"] .value').text(radio.parent().text());
 dlg.dialog('close').dialog("destroy");
}

app.editCltBirthday=function()
{
 var dlg=$('#dlg-clt-birthday');
 var bday=parseInt($('.birthday .bday',dlg).val());
 var bmon=parseInt($('.birthday .bmon',dlg).val());
 var byear=parseInt($('.birthday .byear',dlg).val());
 if(byear>0&&byear<100)
  byear+=byear>15?1900:2000;
 if(byear<0||byear>2015)
 {
  $('.birthday .byear',dlg).focus().select();
  return;
 }
 if(bmon<0||bmon>12)
 {
  $('.birthday .bmon',dlg).focus().select();
  return;
 }
 if(bday)
 {
  if(!bmon)
   bday=0;
  else
  {
   var d=new Date(byear?byear:2000,bmon-1,bday);
   if(bday!=d.getDate())
   {
    $('.birthday .bday',dlg).focus().select();
    return;
   }
  }
 }
 var res=app.ajax(app.makeURI({field:'birthday',bday:(bday?bday:""),bmon:(bmon?bmon:""),byear:(byear?byear:"")},'change'));
 if(!res)
  return;
 app.clt.bday=('bday' in res)?res.bday:'';
 app.clt.bmon=('bmon' in res)?res.bmon:'';
 app.clt.byear=('byear' in res)?res.byear:'';
 app.setupBirthday($('#art-clt .record[field="birthday"]'));
 dlg.dialog('close').dialog("destroy");
}

app.editCltNote=function()
{
 var dlg=$('#dlg-clt-note');
 var note=$('.value',dlg).val();
 if(!app.ajax(app.makeURI({field:'note',value:note},'change')))
  return;
 app.clt.note=note;
 $('#art-clt #clt-note').html(note.replaceAll("\n","<br/>"));
 dlg.dialog('close').dialog("destroy");
}

app.setupBks=function(bks)
{
 var data=$('#art-bookings .data tbody');
 data.text('');
 if(bks&&bks.length)
 {
  $.each(bks,function(i,b)
  {
   data.append($('<tr>')
   .append($('<td>').addClass('right').append($('<a class="ax">').attr('href','pay/?ref='+b.id).text(b.id)))
   .append($('<td>').addClass('center').text(b.date))
   .append($('<td>').addClass('center').text(b.time))
   .append($('<td>').append($('<a class="ax" href="ctr-'+b.ctr+'/">').text(b.ctrT)))
   .append($('<td>').append($('<a class="ax" href="srv-'+b.srv+'/">').text(b.srvT)))
   .append($('<td>').addClass('right').text(b.dura))
   .append($('<td>').addClass('right').text(b.total))
   );
  });
 }
}

app.setupFAQ=function(faq)
{
 var topics=$('#art-faq .topics');
 var menu=$('.menu ul',topics);
 var data=$('.content .data',topics);
 menu.text('');
 data.text('');
 if(faq&&faq.length)
 {
  $.each(faq,function(i,q)
  {
   menu.append($('<li>').append($('<a>').attr('href','fag/#faq-'+q.id).addClass('ax').text(q.title)));
   data.append($('<div>').addClass('topic')
   .append($('<div>').addClass('title').append($('<a>').attr('name','faq-'+q.id).text(q.title)))
   .append($('<div>').addClass('text').html(q.reply))
   );
  });
 }
}

app.setupPlc=function(data)
{
 $.each(['terms','booking','privacy'],function(i,t){$('#art-policy .content .'+t+' .text').html(data[t]);});
}

app.initLogo=function()
{
 $('header a.logo').click(function()
 {
  var result=true;
  //console.log('$("header a.logo").click() enter');
  if(app.mode=='home')
  {
   app.showNextHomeBgImg(false);
   result=false;
  }
  //console.log('$("header a.logo").click() exit');
  return result;
 });
}

//// Business management ////

app.initBusiness=function()
{
 //$('#button-your-business').button();
 $('#art-biz .signup .button')//.button()
 .click(function()
 {
  $('#dlg-signup').dialog({modal:true,resizable:false
  ,buttons:[{text:app.txt.button_signup,click:app.seance.signup}]
  ,open:(function(){app.clearDlgFields($(this));})
  ,close:(function(){app.clearDlgFields($(this));})});
 });
}

app.list.loadMethod='init';

app.buttonSearchStartClick=function()
{
 app.list.loadMethod=$(this).attr('id');
}

app.setupButtons=function(tag)
{
 $('a.button',tag).addClass('ui-widget ui-state-default ui-corner-all');
// $('.button.main',tag).addClass('ui-state-error');
}

app.initButtons=function()
{
 app.setupButtons($('body'));
 $('#button-search-start').click(app.buttonSearchStartClick);
 $('article .detail .topic.compact .text,.more-form').click(function()
 {
  $(this).parent('.topic').toggleClass('small large');
//  app.showHideMore($(this).parent('.topic'));
 });
}

//// Home background control ////

app.initHomeBgImgTimeout=function(active)
{
//log('app.initHomeBgImgTimeout(active:'+active+') enter');
 clearTimeout(app.bgs.timeout);
 app.bgs.timeout=null;
 if(!active)
  app.showNextHomeBgImg(true);
 else
  app.bgs.timeout=setTimeout(app.onHomeBgImgTimeout,30000);
//log('app.initHomeBgImgTimeout(active:'+active+') exit');
}

app.onHomeBgImgTimeout=function()
{
 //console.log('onHomeBgImgTimeout() enter');
 app.bgs.timeout=null;
 app.showNextHomeBgImg(null);
 //console.log('onHomeBgImgTimeout() exit');
}

app.showFirstMenuBg=function()
{
 //console.log(app.bgs);
//log('app.showFirstMenuBg() enter');
 var slideshow=$('#home-slideshow');
 var slide=slideshow.children().first();
 if(slide.length)
 {
  slide.addClass('active');
  if(slide.next().length)
   app.initHomeBgImgTimeout(true);
 }
//log('app.showFirstMenuBg() exit');
}

app.showNextHomeBgImg=function(stop)
{
//log('app.showNextHomeBgImg(stop:'+stop+') enter');
 stop=(stop===true);
 var slideshow=$('#home-slideshow');
 var next=$('.slide.next',slideshow);
 if(stop&&!next.length)
 {
 //log('app.showNextHomeBgImg(stop:'+stop+') exit: stop && no next');
  return;
 }
 var w=slideshow.width(),h=slideshow.height();
 var activeSlideStyle={width:w,height:h,left:0,top:0,opacity:1};
 var activeImageStyle={left:0,top:0};
 if(next.length)
 {
  next.stop().addClass('active').removeClass('next').css(activeSlideStyle);
  next.children(':first').stop().css(activeImageStyle);
  $('.slide.prev',slideshow).removeClass('prev');
 }
 if(stop||($('#home-main').css('opacity')=='0'))
 {
 //log('app.showNextHomeBgImg(stop:'+stop+') exit: stop || no opacity');
  return;
 }
 var slides=$('.slide:not(.active)',slideshow);
 if(!slides.length)
 {
 //log('app.showNextHomeBgImg(stop:'+stop+') exit: no slides');
  return;
 }
 var slide=slides.eq(Math.round(slides.length*Math.random())%slides.length);//Random slide
 var image=slide.children(':first');
 if(image[0].style.backgroundImage)
 {
  app.showSlideImage(slideshow,slide,image,w,h,activeSlideStyle,activeImageStyle);
 //log('app.showNextHomeBgImg(stop:'+stop+') exit: image shown');
  return;
 }
 var imgNode=new Image();
 var src=image.attr('src');
 imgNode.onload=function()
 {
  //console.log('onHomeBgImgLoaded(uri:'+src+') enter');
  image[0].style.backgroundImage='url('+src+')';
  image.removeAttr('src');
  app.showNextHomeBgImg(true);
  app.showSlideImage(slideshow,slide,image,w,h,activeSlideStyle,activeImageStyle);
  //console.log('onHomeBgImgLoaded(uri:'+src+') exit');
 }
 imgNode.src=src;
//log('app.showNextHomeBgImg(stop:'+stop+') exit: image requested');
}

app.showSlideImage=function(slideshow,slide,image,w,h,activeSlideStyle,activeImageStyle)
{
//log('app.showSlideImage() enter');
 if(app.msie)
 {
  $('.slide.active',slideshow).removeClass('active');
  slide.css(activeSlideStyle).addClass('active');
  image.css(activeImageStyle);
 }
 else
 {
  $('.slide.active',slideshow).addClass('prev').removeClass('active');
  var slideCSS={/*width:w,height:h,left:0,top:0,*/opacity:0};
  var imageCSS={/*width:w,height:h,left:0,top:0*/};
  // 0,1:fade; 2,3:slide; 4,5,6,7:grow
  /*var effect=Math.round(64*Math.random())%64;
  var effectX=effect%8;
  var effectY=(effect-effectX)/8;
  if(effectX>1||effectY>1)//0,1:fade
  {
   if(Math.random()>=0.5)
    slideCSS.opacity=1;
   switch(effectX)
   {
    case 2:imageCSS.left=-w;break;//2:slide right
    case 3:imageCSS.left=w;break;//3:slide left
    case 4:slideCSS.width=0;break;//4:grow right
    case 5:case 6:slideCSS.width=0;slideCSS.left=w/2;imageCSS.left=-w/2;break;//5,6:grow left and right
    case 7:slideCSS.width=0;slideCSS.left=w;imageCSS.left=-w;break;//7:grow left
   }
   switch(effectY)
   {
    case 2:imageCSS.top=-h;break;//2:slide down
    case 3:imageCSS.top=h;break;//3:slide up
    case 4:slideCSS.height=0;break;//4:grow down
    case 5:case 6:slideCSS.height=0;slideCSS.top=h/2;imageCSS.top=-h/2;break;//5,6:grow down and up
    case 7:slideCSS.height=0;slideCSS.top=h;imageCSS.top=-h;break;//7:grow up
   }
  }*/
  var speed=5000;
  slide.css(slideCSS).addClass('next').animate(activeSlideStyle,speed,'',function()
  {
   slide.addClass('active').removeClass('next');
   $('.slide.prev',slideshow).removeClass('prev');
  });
  image.css(imageCSS).animate(activeImageStyle,speed);
 }
 app.initHomeBgImgTimeout(true);
//log('app.showSlideImage() exit');
 return true;
}

//// Root initialization ////

app.onSlideShowResize=function()
{
 if(app.mode!='home')
  return;
 var actElement=$(document.activeElement);
 actElement.blur();
 var bottomHight=100;
 var homeContentMargin=20;
 var art=$('#art-home');
 var home=$('#home-main',art);
 var slideshow=$('#home-slideshow',art);
 var homeContentHeight=0;
 slideshow.siblings().each(function(){homeContentHeight=homeContentHeight+$(this).outerHeight(true);});
 var headerHeight=$('header').outerHeight();
 var homeHeight = $(window).outerHeight()-headerHeight-bottomHight;
 if(homeHeight<homeContentHeight+homeContentMargin)
  homeHeight=homeContentHeight+homeContentMargin;
 home.width(art.width());
 home.height(homeHeight);
 slideshow.css({width:home.width(),height:homeHeight});
 $('.slide,.slide *',slideshow).stop().css({width:home.width(),height:homeHeight,left:0,top:0});
 var contentFirst=slideshow.siblings(':first');
 var contentPaddingTop=parseInt(contentFirst.css('padding-top'));
 contentFirst.css('padding-top',(home.height()+contentPaddingTop-homeContentHeight)/2);
 if(actElement)
  actElement.focus();
 app.showNextHomeBgImg(true);
//log('app.onSlideShowResize() exit');
}

app.list.colcount=null;

app.onListTargetResize=function()
{
//log('app.onListTargetResize');
 var cols=$('#list-result table.cols .data-col');
 var spacers=$('#list-result table.cols .spacer');
 var col=$('#list-result table.cols .col');
 var data=$('#list-result .data');
 var size=data.innerWidth();
 var spacerWidth=parseInt(spacers.outerWidth(true));
 var colwidth=parseInt(col.css('min-width'))+spacerWidth*2;
 if($(document).width()>=app.ipadScreenWidth)
  colwidth-=spacerWidth/2;
 var count=Math.floor(size/colwidth);
 if(!count||(count<1))
  count=1;
 if(count!=app.list.colcount)
 {
  app.list.colcount=count;
  app.deleteListResultItems();
  cols.each(function(i)
  {
   $(this).toggle(i<count).removeClass('last-data-col');
   if(i==count-1)
    $(this).addClass('last-data-col');
  });
  spacers.each(function(i)
  {
   $(this).toggle(i<count-1);
  });
  app.createListResultItems();
 }
 $('#list-result .image').each(function()
 {
  $(this).height($(this).attr('rto')*$(this).width());
 });
//log('app.onListTargetResize - end');
}

app.onHomeAdvResizeRow=function(row)
{
 var count=$('.rd-img-cntnr',row).length;
 if (!count)
 {
  $('.rd-adv-tbl',row).css('display','none');
  return;
 }

 //Maybe move to global variables:
 var BaseScreenWidthPC=100;
 var MinImgCntnrWidth=230;

 var wndWidth=$('body').width();
 var big=row.hasClass('big');
 var ratio=big?(app.top.img.HEIGHT1/app.top.img.WIDTH1):(.85*app.top.img.HEIGHT2/app.top.img.WIDTH2);
 var imgHeight='auto';
 var PaddingPC=big?1:2;
 var imgsPerRow=Math.floor(wndWidth/MinImgCntnrWidth);

 $('.rd-rw-1',row).removeClass('rd-rw-1');
 $('.rd-rw-2',row).removeClass('rd-rw-2');

 if(imgsPerRow>1){
  if(imgsPerRow<count)
    imgsPerRow=Math.floor(count/2);
  else
   imgsPerRow=count;
  $('.rd-img-cntnr',row).each(function(i){
   var imgCntr = $(this);
   var widthPC = (BaseScreenWidthPC-imgsPerRow*PaddingPC)/imgsPerRow;
   if(i+1<=imgsPerRow){
    imgCntr.addClass('rd-rw-1')
   }else{
    imgCntr.addClass('rd-rw-2')
    widthPC=(BaseScreenWidthPC-(count-imgsPerRow)*PaddingPC)/(count-imgsPerRow);
   };
   imgCntr.css('width',widthPC+'%');
   imgHeight=wndWidth*(widthPC/100)*ratio;
   if(big)
    imgCntr.height(imgHeight+$('.book-button').outerHeight(true)+10);
   else
    imgCntr.height('auto');
   $('img',this).height(imgHeight);
  });
 }else{
  $('.rd-img-cntnr',row).css('width',BaseScreenWidthPC-PaddingPC+'%');
  imgHeight=wndWidth*ratio;
  $('img',row).width('100%').css('height',imgHeight);
  if (big)
   $('.rd-img-cntnr',row).css('height',imgHeight+$('.book-button').outerHeight(true)+10);
  else
   $('.rd-img-cntnr',row).css('height','auto');
 }

 $('.rd-row-wrppr .rd-rw-1',row).unwrap();
 $('.rd-rw-1',row).wrapAll("<div style='width:100%;float:left;' class='rd-row-wrppr'>");

 $('.rd-row-wrppr .rd-rw-2',row).unwrap();
 $('.rd-rw-2',row).wrapAll("<div style='width:100%;float:left;' class='rd-row-wrppr'>");

 if($('.rd-adv-tbl',row).css('display')==='none')
  $('.rd-adv-tbl',row).css('display','table');

 if (big)
 {
  $('.book-button',row).not('.a').each(function()
  {
   var thisElmnt = $(this);
   var ctrHref = thisElmnt.parent().attr('href');
   thisElmnt.wrapInner('<a id="button-service" href="' + ctrHref + '#srvs" class="ax button main">');
   thisElmnt.addClass('a');
  });
  $('.rd-img-cntnr',row).each(function()
  {
   var cntnr = $(this);
   var imgHeight = $('img',cntnr).outerHeight();
   var cntnrHeight = imgHeight + $('.book-button',cntnr).outerHeight();
   var divTitle=$('.img-title',cntnr);
   var titleHeight=divTitle.innerHeight();
//   var margin=-titleHeight/1.6 - cntnrHeight/2 + $('.book-button',cntnr).outerHeight();
   var margin=-cntnrHeight+(imgHeight-titleHeight)/2;
   divTitle.css('margin-top',margin);
  });
 }
 else
 {
  $('.book-link',row).not('.a').each(function()
  {
   var thisElmnt = $(this);
   var ctrHref = thisElmnt.parent().attr('href');
   thisElmnt.wrapInner('<a id="link-service" href="' + ctrHref + '#srvs">');
   thisElmnt.addClass('a');
  });
 }
}

app.onHomeAdvResize=function()
{
 if(app.mode=='home')
  $.each($('#home-adv .adv-row'),function(i,row){app.onHomeAdvResizeRow($(row));});
}

app.onBizSlideResize=function()
{
 $.each($('#art-biz .slide'),function(i,e){$(e).height($('img',e).height());});
}

app.onWindowResize=function()
{
 if(app.noresize)
  return;
 var header=$('header');
 $('.logo span',header).toggle(header.width()>800);
 app.onSlideShowResize();
 app.onHomeAdvResize();
 if(app.mode=='list')
  app.onListTargetResize();
 if(app.mode=='biz')
  app.onBizSlideResize();
 if(app.mode=='ctr')
 {
  var gallery=$('.gallery .images');
  if((gallery.width()==0)&&(gallery.css('display')=='block'))
   app.setupGalleryImages($('#art-ctr .detail .gallery'),app.ctr.images);
 }
 var pop=$('#nav-pop-mod');
 if(pop.css('visibility')=='visible')
  app.resizeModalPopup(pop);
 $('article .detail .topic.compact.small').each(function(){app.showHideMore($(this));});
//log('app.onWindowResize - end');
 }

app.onWindowScroll=function()
{
 var wnd=$(window);
 //console.log('book: onWindowScroll('+wnd.scrollLeft()+','+wnd.scrollTop()+')');
 //var scrollPosH=wnd.scrollLeft();
 //$('header').css('left',-scrollPosH);
 var scrollPosV=wnd.scrollTop();
 var bodySizeV=$('body').height();
 var windowSizeV=wnd.height();
 var footerSizeV=$('footer').height();
 //console.log('scrollPosV='+scrollPosV+', bodySizeV='+bodySizeV+', windowSizeV='+windowSizeV);
 //console.log(bodySizeV-windowSizeV-10-scrollPosV);
 if ($(document).width()>=app.ipadScreenWidth)
  $('#home-more').toggle(scrollPosV<bodySizeV-windowSizeV-10);
 if((app.mode=='list')&&!app.list.finished)
 {
  if(scrollPosV>=bodySizeV-windowSizeV-footerSizeV-300)
   app.listSearchMore();
 }
}

app.onHomeMoreClick=function()
{
 var wnd=$(window);
 //wnd.scrollTop(wnd.scrollTop()+500);
 wnd.scrollTop($('body').height()-$(window).height()+$('header').height());
}

//// Navigation management ////
app.iphoneScreenWidth = 550;

app.setMode=function(mode,init)
{
 var isHome=(mode=='home');
 var isList=(mode=='list');
 var isCtr=(mode=='ctr');
 var isBnd=(mode=='bnd');
 var isSrv=(mode=='srv');
 var isPay=(mode=='pay');
 var isClt=(mode=='clt');
 var isBiz=(mode=='biz');
 app.initHomeBgImgTimeout(isHome);
 app.initListFilterTimeout(false);
 app.initGalleryImagesInterval(isCtr||isBnd||isSrv);
 if(isHome||app.mode=='home')
 {
  $('#home-filter .search-edit').val('').data('acid','').parent().removeClass('has-text');
  app.makeHomeSearchURI();
 }
 $('header .brand').toggle(!isList);
 $('#button-search-again').toggle(isList);
 if($(document).width()>=app.ipadScreenWidth)
  $('#button-your-business').toggle(!isBiz);
 if(isList)
 {
  if(init||(app.mode!=mode))
   app.fillListFilter();
  app.showListResult();
  if(($(document).width()<=app.iphoneScreenWidth)&&(app.list.loadMethod!=''))
   $('#list-filter').css('display','none');
  app.list.loadMethod='';
 }
 else if(isCtr)
  app.setupCtr(init);
 else if(isBnd)
  app.setupBnd(init);
 else if(isSrv)
  app.setupSrv(init);
 else if(isPay)
  app.setupPay();
 else if(isClt)
  app.setupClt();
 if((mode!=app.mode)||init)
 {
  var id='#art-'+mode;
  $('section article').not(id).removeClass('active');
  $(id).addClass('active');
  app.mode=mode;
 }
 if(isBiz)
  app.onBizSlideResize();
 if(app.mode=='home')
 {
  app.onSlideShowResize();
  app.onHomeAdvResize();
 }
}

app.go=function(uri,fwd)
{
 uri=''+(uri?uri:document.location.href);
 if((''+uri).substr(0,4)!='http')
  uri=$('head base').attr('href')+uri;
 if(!history||!('pushState' in history))
 {
  if(fwd!==true)
   document.location=uri;
  return false;
 }
 var xuri=app.addParams(uri,'a=');
 var x=$.get(xuri)
 .fail(function(xhr)
 {
  app.msg(app.json(xhr),app.txt.server_error);
 }).done(function(json)
 {
  var res=eval('('+json+')');
  if('uri' in res)
  {
   if(res.uri.substr(0,4)=='http')
    document.location=res.uri;
   if(res.uri!=uri)
    return app.go(res.uri);
  }
  if('failure' in res)
   {res.uri=uri;console.log(res);}
  if('error' in res)
   app.msg(res.error,app.txt.server_error);
  if('title' in res)
   document.title=res.title;
  if('subtitle' in res)
   app.subtitle=res.subtitle;
  if(fwd!==false)
   history.pushState(document.title,document.title,uri);
  if(typeof ga!="undefined")
  {
   ga('send','pageview');
   //console.log('GA pageview sent again: '+location.href);
  }
  scrollTo(0,0);
  app.URI=app.parseUri(uri);
  app.list.filter=('filter' in res)?res.filter:{};
  app.list.result=('result' in res)?res.result:[];
  app.pay=app.nvl(res.pay,{});
  app.clt=('clt' in res)?res.clt:{};
  switch(res.mode)
  {
  case 'ctr':
   app.ctr.id=('ctr' in res)?res.ctr:null;
   app.ctr.title=('ctrT' in res)?res.ctrT:'';
   app.ctr.type=('type' in res)?res.type:null;
   app.ctr.typeT=('typeT' in res)?res.typeT:'';
   app.ctr.bnd=('bnd' in res)?res.bnd:null;
   app.ctr.bndT=('bndT' in res)?res.bndT:'';
   app.ctr.addr=('addr' in res)?res.addr:'';
   app.ctr.curr=('curr' in res)?res.curr:null;
   app.ctr.descr=('descr' in res)?res.descr:'';
   //app.ctr.logo=('logo' in res)?res.logo:null;
   app.ctr.loc=('loc' in res)?res.loc:null;
   app.ctr.images=('images' in res)?res.images:[];
   app.ctr.groups=('groups' in res)?res.groups:[];
   app.ctr.ratings=('ratings' in res)?res.ratings:null;
   app.ctr.reviews=('reviews' in res)?res.reviews:[];
   app.ctr.metros=('metros' in res)?res.metros:[];
   app.ctr.phones=('phones' in res)?res.phones:[];
   app.ctr.sched=('sched' in res)?res.sched:[];
   break;
  case 'bnd':
   app.bnd.id=('bnd' in res)?res.bnd:'';
   app.bnd.title=('bndT' in res)?res.bndT:'';
   //app.bnd.logo=('logo' in res)?res.logo:'';
   app.ctr.images=('images' in res)?res.images:[];
   app.ctr.descr=('descr' in res)?res.descr:'';
   break;
  case 'srv':
   app.srv.id=('srv' in res)?res.srv:null;
   app.srv.title=('srvT' in res)?res.srvT:'';
   app.ctr.id=('ctr' in res)?res.ctr:null;
   app.ctr.title=('ctrT' in res)?res.ctrT:'';
   app.ctr.curr=('curr' in res)?res.curr:null;
   //app.ctr.logo=('logo' in res)?res.logo:'';
   app.srv.date=('date' in res)?res.date:(''+(new Date()).addDays(1));
   app.srv.tip=('tip' in res)?res.tip:0;
   app.srv.tips=('tips' in res)?res.tips:[];
   app.ctr.images=('images' in res)?res.images:[];
   app.ctr.ratings=('ratings' in res)?res.ratings:null;
   app.ctr.reviews=('reviews' in res)?res.reviews:[];
   app.srv.descr=('descr' in res)?res.descr:'';
   app.srv.restr=('restr' in res)?res.restr:'';
   app.srv.notes=('notes' in res)?res.notes:'';
   break;
  case 'bookings':
   app.setupBks(res.bookings);
   break;
  case 'faq':
   app.setupFAQ(res.faq);
   break;
  case 'policy':
   app.setupPlc(res)
   break;
  }
  var mode=('mode' in res)?res.mode:'home';
  var path=(app.mode=='home')?'':(uri.substr(uri.indexOf(mode)));
  $('header a.lang').each(function(){$(this).attr('href',app.home+$(this).attr('path')+path);});
  app.setMode(mode);
  if(uri.indexOf('#')>0)
  {
   if($(uri.substr(uri.indexOf('#'))).length)
    scrollTo(0,$(uri.substr(uri.indexOf('#'))).offset().top);
   else
    scrollTo(0,$('a[name="'+uri.substr(uri.indexOf('#')+1)+'"]').offset().top);
  }
  $('article .detail .topic.compact.small').each(function(){app.showHideMore($(this));});
 });
 return x.readyState==1;
}


/// Pop-up messages ///
app.popupMouseLeaveCount=0;
app.hideHomeMainPopup=function(pop){
 app.popupMouseLeaveCount=app.popupMouseLeaveCount+1;
 $('.pop-message',pop).css('display','none');
}

app.initHomeMainPopup=function(){
// log('initHomeMainPopup');
 var pop=$('#home-main-pop');
 if ($('.pop-message',pop).text()!==''){
  if($(document).width()>=app.ipadScreenWidth+100){
   $('.pop-subject',pop).click(function(){$('.pop-message',pop).toggle();});
   pop.on('mouseenter', function(){$('.pop-message',pop).css('display','block');$('.pop-subject',pop).css('display','block');}) //pop.css({"opacity":"1","bottom":"-25px"});
    .on('mouseleave', function(){app.hideHomeMainPopup(pop);}); //pop.delay(1500).animate({opacity:".7",bottom:"-55px"});
   $(window).on('scroll', function(){$('.pop-message',pop).css('display','none')});
  }
  //this needs to be reworked later:
  if ($('#button-login').css('visibility')=='visible'){
   pop.css('display','block');
   $('.pop-message',pop).css('display','block');
   $('#home-main').toggleClass('has-pop',true);
//   setTimeout(function(){
//    pop.parent().css({position:'fixed',left:0,top:0,width:'100%',height:'100%','z-index':20,'padding-top':'20%','background':'rgba(255,255,255,0.4)'});
//    pop.css('background-color','#105C54');
//    $('.link-signup').click(function(){pop.parent().css('display','none');$('#home-main').toggleClass('has-pop',false);});
//   },3000);
  }else{
   pop.css('display','none');
   $('#home-main').toggleClass('has-pop',false);
  }
 }
}


app.popupMouseLeaveCount=0;
app.hidePopup=function(pop){
 app.popupMouseLeaveCount=app.popupMouseLeaveCount+1;
 $('.pop-message',pop).css('display','none');
 if($(document).width()>=app.ipadScreenWidth && app.popupMouseLeaveCount>1)
  $('.pop-subject',pop).css('display','none');
}

app.initPopup=function(){
// log('initPopup');
 var pop=$('nav .nav-pop');
 if ($('.pop-message',pop).text()!==''){
  $('.pop-subject',pop).click(function(){$('.pop-message',pop).toggle();});
  pop.on('mouseenter', function(){$('.pop-message',pop).css('display','block');$('.pop-subject',pop).css('display','block');}) //pop.css({"opacity":"1","bottom":"-25px"});
   .on('mouseleave', function(){app.hidePopup(pop);}); //pop.delay(1500).animate({opacity:".7",bottom:"-55px"});
  $(window).on('scroll', function(){$('.pop-message',pop).css('display','none')});

  //this needs to be reworked later:
  if ($('#button-login').css('visibility')=='visible'){
   pop.css('display','block');
   $('.pop-message',pop).css('display','block');
  }else
   pop.css('display','none');
 }
}

app.homeMainPopDisplay=null;
app.initModalPopup=function(){
 var pop=$('#nav-pop-mod');
 if ($('.pop-message',pop).text()!==''){
  //this needs to be reworked later:
  if ($('#button-login').css('visibility')=='visible'){
//   $('nav').toggleClass('has-pop-mod',true);
   setTimeout(function(){
    if($('nav .topw.login').css('display')=='block' || $('nav .topw.signup').css('display')=='block' || $('#button-login').css('visibility')!=='visible')
     return;
    var msgWndH=$('#nav-pop-mod-wnd',pop).height();
    if(msgWndH<$(window).height()*1.1){
     app.homeMainPopDisplay=$('#home-main-pop').css('display');
     $('#home-main-pop').css('display','none');
     app.resizeModalPopup(pop);
     if(app.dlyPop<=0)
     {
      pop.css('visibility','visible');
      setTimeout(function(){$('#nav-pop-mod-wnd',pop).css('visibility','visible');},1000);
      setTimeout(function(){$('.nav-pop-mod-bkg',pop).fadeTo(3500,0);},2000);
     }
     else
     {
      $('.nav-pop-mod-bkg',pop).css('visibility','hidden');
      $('#nav-pop-mod-wnd',pop).css('visibility','visible');
      pop.css('visibility','visible');
     }
    }else
     pop.css('display','none');
    $('.link-signup,.ui-icon-circle-close,.continue a',pop).click(app.hideModalPopup);
   },app.dlyPop);
  }else{
   pop.css('display','none');
//   $('#home-main').toggleClass('has-pop',false);
  }
 }
}

app.resizeModalPopup=function(pop){
 var msgWndH=$('#nav-pop-mod-wnd',pop).height();
 pop.css('padding-top',(pop.height()-msgWndH)/2.2);
}

app.hideModalPopup=function(){
 $('#nav-pop-mod').css('display','none');
 $('#home-main-pop').css('display',app.homeMainPopDisplay);
}

app.checkProCode=function(codeVal,pCodes){
 var vldPcode=null;
 var cnt=0;
 $.each(pCodes,function(i,pCode){
  if(codeVal===pCode['promo_code']){
   vldPcode=pCode;
  }
  if(codeVal===pCode['promo_code'].substr(0,codeVal.length)){
   cnt=cnt+1;
  }
 });
 if(vldPcode)
  vldPcode['cnt']=cnt;
 return vldPcode;
}

$(function()
{
 app.URI=app.parseUri();
 //console.log('book initialization...');
 $('header .button').css('visibility', 'visible');
 //$('article .obj-header').addClass('ui-widget ui-widget-header');
 $('#home-more').click(app.onHomeMoreClick);
 app.initLogo();
 app.initBusiness();
 app.initLoginForms();
 app.initTopMenu();
 app.initClientMenu();
 app.initListFilter();
 app.initListResult();
 app.initCtrHeader();
 app.initGalleryImages();
 app.initTopicTitle();
 //app.initAdvTitle();
 app.initCtrReviews();
 app.initBndHeader();
 app.initSrv();
 app.initPay();
 app.initClt();
 app.initAutocomplete();
 app.initButtons();
 app.showFirstMenuBg();
 setTimeout(app.onWindowResize,20);
 app.onWindowResize();
 app.onListTargetResize();
 app.setMode(app.mode,true);
 //app.g.map=new google.maps.Map(document.getElementById("map"));
 app.g.geo=new google.maps.Geocoder();
 if(app.mode=='home')
 {
  app.initHomeMainPopup();
  $('header .brand .search-edit').focus();
 }
 //console.log('book initialized');
 if(app.topw.msg)
  app.topw.showMessageForm(app.topw.msg,app.topw.msguri?function(){app.go(app.topw.msguri);}:null);
 app.initPopup();
 app.initModalPopup();
 //clone more-form:
 $('.more-form').insertAfter('article .detail .topic.compact .text');
})/*(jQuery)*/;
