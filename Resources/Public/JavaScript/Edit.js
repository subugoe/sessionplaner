define(["jquery","TYPO3/CMS/Sessionplaner/DragDrop","TYPO3/CMS/Backend/Modal"],function(a,b,c){"use strict";var d={uiBlock:null,stash:null,sessionData:{},ajaxActive:!1};return d.getUrlVars=function(){for(var a,b=[],c=window.location.href.slice(window.location.href.indexOf("?")+1).split("&"),d=0;d<c.length;d++)a=c[d].split("="),b.push(a[0]),b[a[0]]=a[1];return b},d.getUrlVar=function(a){return d.getUrlVars()[a]},d.getTemplateElement=function(b){return a(a("#"+b).html().replace(/^\s+|\s+$/g,""))},d.prepareData=function(b){var c={};return a.each(b,function(a,b){c[b.name]=b.value}),c},d.applySessionValuesToForm=function(b,c){return a.each(c,function(c,d){var e=a("dd ."+c+":first",b);e.length>0&&e.val(d)}),b},d.applySessionValuesToCard=function(b,c){return a.each(c,function(c,d){var e=a("."+c,b);e.data("value",d),e.children().length<2&&e.text(d)}),b},d.addValuesFromParent=function(a,b){return"stash"===b.attr("id")?(a.slot=0,a.room=0):(a.slot=b.data("slot"),a.room=b.data("room")),a},d.createSessionCard=function(a){var b=d.getTemplateElement("sessionCardTemplate");return b=d.applySessionValuesToCard(b,a)},d.updateSessionCard=function(b){var c=a('.uid[data-value="'+b.uid+'"]',".t3js-page-ce").parent();d.applySessionValuesToCard(c,b)},d.getDataFromCard=function(b){var c={};return a(b).children().children().each(function(){var b=a(this);c[b.data("name")]=b.data("value")}),c},d.createSessionSuccess=function(a){d.sessionData.uid=a.data.uid;var c=d.createSessionCard(d.sessionData);d.stash.append(c),b.initializeDraggable(c)},d.updateSessionSuccess=function(){d.updateSessionCard(d.sessionData)},d.movedSessionSuccess=function(){},d.beforeSend=function(){var a=!0;return d.ajaxActive?a=!1:(d.ajaxActive=!0,d.uiBlock.removeClass("hidden")),a},d.afterSend=function(){d.uiBlock.addClass("hidden"),d.ajaxActive=!1},d.createSession=function(b){var c=d.prepareData(a("form",b.target).serializeArray());d.sessionData=c,a.ajax({type:"POST",url:TYPO3.settings.ajaxUrls.evoweb_sessionplaner_edit,context:this,params:{},data:{id:d.getUrlVar("id"),tx_sessionplaner:{action:"addSession",session:c}},beforeSend:d.beforeSend,complete:d.afterSend,success:d.createSessionSuccess})},d.updateSession=function(b){var c=d.prepareData(a("form",b.target).serializeArray());c.uid=d.sessionData.uid,d.sessionData=c,a.ajax({type:"POST",url:TYPO3.settings.ajaxUrls.evoweb_sessionplaner_edit,context:this,params:{},data:{id:d.getUrlVar("id"),tx_sessionplaner:{action:"updateSession",session:c}},beforeSend:d.beforeSend,complete:d.afterSend,success:d.updateSessionSuccess})},d.movedSession=function(b,c){var e=d.getDataFromCard(b);e=d.addValuesFromParent(e,c),d.sessionData=e,a.ajax({type:"POST",url:TYPO3.settings.ajaxUrls.evoweb_sessionplaner_edit,context:this,params:{},data:{id:d.getUrlVar("id"),tx_sessionplaner:{action:"updateSession",session:e}},beforeSend:d.beforeSend,complete:d.afterSend,success:d.movedSessionSuccess})},d.createNewSessionForm=function(){var a=d.getTemplateElement("sessionFormTemplate"),b=null!==opener&&"undefined"!=typeof opener.top.TYPO3?opener.top:top;c.confirm("Create session",a,b.TYPO3.Severity.ok,[{text:"Create session",active:!0,btnClass:"btn-default",name:"ok"},{text:"Cancel",active:!0,btnClass:"btn-default",name:"cancel"}]).on("button.clicked",function(){c.dismiss()}).on("confirm.button.ok",d.createSession)},d.editSessionForm=function(){d.sessionData=d.getDataFromCard(this);var a=d.applySessionValuesToForm(d.getTemplateElement("sessionFormTemplate"),d.sessionData),b=null!==opener&&"undefined"!=typeof opener.top.TYPO3?opener.top:top;c.confirm("Edit session",a,b.TYPO3.Severity.ok,[{text:"Update session",active:!0,btnClass:"btn-default",name:"ok"},{text:"Cancel",active:!0,btnClass:"btn-default",name:"cancel"}]).on("button.clicked",function(){c.dismiss()}).on("confirm.button.ok",d.updateSession)},d.initializeDragAndDrop=function(){var a=b.onDrop;b.onDrop=function(b,c,e){d.movedSession(b,c),a(b,c,e)}},d.initialize=function(){d.uiBlock=a("#t3js-ui-block"),d.stash=a("#stash"),a(document).on("click","#actions-document-new",d.createNewSessionForm).on("dblclick",".t3js-page-ce",d.editSessionForm),d.initializeDragAndDrop()},a(d.initialize),d});