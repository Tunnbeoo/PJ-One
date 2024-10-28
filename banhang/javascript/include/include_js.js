/*==============================================================================
Name: 		include_js.js
Version: 	1.0
Author:Ly Ngoc Tung
Created: 	Auguest 24, 2005
Updated:	September 21, 2005
================================================================================
Copyright (c) 2005Ly Ngoc Tung - Mobile: 0918596004
================================================================================*/
function IncludeMainScripts(contextPath,arrFileName){var i;var scriptsToInclude="";var len=arrFileName.length;if(typeof(len)=="undefined")return;for(i=0;i<len;i++)scriptsToInclude+=getScriptInc(contextPath+"/"+arrFileName[i]);document.writeln(scriptsToInclude);};function getScriptInc(scriptFilePath){return "<script src='"+scriptFilePath+"' type='text/javascript'></script>";}
