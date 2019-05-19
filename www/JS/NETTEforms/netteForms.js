/*! netteForms.js | (c) 2004, 2016 David Grudl (http://davidgrudl.com) */
(function(e,p){if(e.JSON)if("function"===typeof define&&define.amd)define(function(){return p(e)});else if("object"===typeof module&&"object"===typeof module.exports)module.exports=p(e);else{var d=!e.Nette||!e.Nette.noInit;e.Nette=p(e);d&&e.Nette.initOnLoad()}})("undefined"!==typeof window?window:this,function(e){function p(a){return function(b){return a.call(this,b)}}var d={formErrors:[],version:"2.4",addEvent:function(a,b,c){"DOMContentLoaded"===b&&"loading"!==a.readyState?c.call(this):a.addEventListener?
a.addEventListener(b,c):"DOMContentLoaded"===b?a.attachEvent("onreadystatechange",function(){"complete"===a.readyState&&c.call(this)}):a.attachEvent("on"+b,p(c))},getValue:function(a){var b;if(a){if(a.tagName){if("radio"===a.type){var c=a.form.elements;for(b=0;b<c.length;b++)if(c[b].name===a.name&&c[b].checked)return c[b].value;return null}if("file"===a.type)return a.files||a.value;if("select"===a.tagName.toLowerCase()){b=a.selectedIndex;c=a.options;var f=[];if("select-one"===a.type)return 0>b?null:
c[b].value;for(b=0;b<c.length;b++)c[b].selected&&f.push(c[b].value);return f}if(a.name&&a.name.match(/\[\]$/)){c=a.form.elements[a.name].tagName?[a]:a.form.elements[a.name];f=[];for(b=0;b<c.length;b++)("checkbox"!==c[b].type||c[b].checked)&&f.push(c[b].value);return f}return"checkbox"===a.type?a.checked:"textarea"===a.tagName.toLowerCase()?a.value.replace("\r",""):a.value.replace("\r","").replace(/^\s+|\s+$/g,"")}return a[0]?d.getValue(a[0]):null}return null},getEffectiveValue:function(a){var b=d.getValue(a);
a.getAttribute&&b===a.getAttribute("data-nette-empty-value")&&(b="");return b},validateControl:function(a,b,c,f,q){a=a.tagName?a:a[0];b=b||d.parseJSON(a.getAttribute("data-nette-rules"));f=void 0===f?{value:d.getEffectiveValue(a)}:f;for(var g=0,k=b.length;g<k;g++){var h=b[g],l=h.op.match(/(~)?([^?]+)/),e=h.control?a.form.elements.namedItem(h.control):a;h.neg=l[1];h.op=l[2];h.condition=!!h.rules;if(e)if("optional"===h.op)q=!d.validateRule(a,":filled",null,f);else if(!q||h.condition||":filled"===h.op)if(e=
e.tagName?e:e[0],l=a===e?f:{value:d.getEffectiveValue(e)},l=d.validateRule(e,h.op,h.arg,l),null!==l)if(h.neg&&(l=!l),h.condition&&l){if(!d.validateControl(a,h.rules,c,f,":blank"===h.op?!1:q))return!1}else if(!h.condition&&!l&&!d.isDisabled(e)){if(!c){var p=d.isArray(h.arg)?h.arg:[h.arg];b=h.msg.replace(/%(value|\d+)/g,function(b,c){return d.getValue("value"===c?e:a.form.elements.namedItem(p[c].control))});d.addError(e,b)}return!1}}return"number"!==a.type||a.validity.valid?!0:(c||d.addError(a,"Please enter a valid value."),
!1)},validateForm:function(a,b){var c=a.form||a,f=!1;d.formErrors=[];if(c["nette-submittedBy"]&&null!==c["nette-submittedBy"].getAttribute("formnovalidate"))if(f=d.parseJSON(c["nette-submittedBy"].getAttribute("data-nette-validation-scope")),f.length)f=new RegExp("^("+f.join("-|")+"-)");else return d.showFormErrors(c,[]),!0;var e={},g;for(g=0;g<c.elements.length;g++){var k=c.elements[g];if(!k.tagName||k.tagName.toLowerCase()in{input:1,select:1,textarea:1,button:1}){if("radio"===k.type){if(e[k.name])continue;
e[k.name]=!0}if(!(f&&!k.name.replace(/]\[|\[|]|$/g,"-").match(f)||d.isDisabled(k)||d.validateControl(k,null,b)||d.formErrors.length))return!1}}f=!d.formErrors.length;d.showFormErrors(c,d.formErrors);return f},isDisabled:function(a){if("radio"===a.type){for(var b=0,c=a.form.elements;b<c.length;b++)if(c[b].name===a.name&&!c[b].disabled)return!1;return!0}return a.disabled},addError:function(a,b){d.formErrors.push({element:a,message:b})},showFormErrors:function(a,b){for(var c=[],f,e=0;e<b.length;e++){var g=
b[e].element,k=b[e].message;d.inArray(c,k)||(c.push(k),!f&&g.focus&&(f=g))}c.length&&(alert(c.join("\n")),f&&f.focus())},expandRuleArgument:function(a,b){if(b&&b.control){var c=a.elements.namedItem(b.control),f={value:d.getEffectiveValue(c)};d.validateControl(c,null,!0,f);b=f.value}return b}},t=!1;d.validateRule=function(a,b,c,f){f=void 0===f?{value:d.getEffectiveValue(a)}:f;":"===b.charAt(0)&&(b=b.substr(1));b=b.replace("::","_");b=b.replace(/\\/g,"");var e=d.isArray(c)?c.slice(0):[c];if(!t){t=!0;
for(var g=0,k=e.length;g<k;g++)e[g]=d.expandRuleArgument(a.form,e[g]);t=!1}return d.validators[b]?d.validators[b](a,d.isArray(c)?e:e[0],f.value,f):null};d.validators={filled:function(a,b,c){return"number"===a.type&&a.validity.badInput?!0:""!==c&&!1!==c&&null!==c&&(!d.isArray(c)||!!c.length)&&(!e.FileList||!(c instanceof e.FileList)||c.length)},blank:function(a,b,c){return!d.validators.filled(a,b,c)},valid:function(a){return d.validateControl(a,null,!0)},equal:function(a,b,c){function f(a){return"number"===
typeof a||"string"===typeof a?""+a:!0===a?"1":""}if(void 0===b)return null;c=d.isArray(c)?c:[c];b=d.isArray(b)?b:[b];a=0;var e=c.length;a:for(;a<e;a++){for(var g=0,k=b.length;g<k;g++)if(f(c[a])===f(b[g]))continue a;return!1}return!0},notEqual:function(a,b,c){return void 0===b?null:!d.validators.equal(a,b,c)},minLength:function(a,b,c){if("number"===a.type){if(a.validity.tooShort)return!1;if(a.validity.badInput)return null}return c.length>=b},maxLength:function(a,b,c){if("number"===a.type){if(a.validity.tooLong)return!1;
if(a.validity.badInput)return null}return c.length<=b},length:function(a,b,c){if("number"===a.type){if(a.validity.tooShort||a.validity.tooLong)return!1;if(a.validity.badInput)return null}b=d.isArray(b)?b:[b,b];return(null===b[0]||c.length>=b[0])&&(null===b[1]||c.length<=b[1])},email:function(a,b,c){return/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i.test(c)},
url:function(a,b,c,d){/^[a-z\d+.-]+:/.test(c)||(c="http://"+c);return/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i.test(c)?(d.value=c,!0):!1},regexp:function(a,b,c){a="string"===typeof b?b.match(/^\/(.*)\/([imu]*)$/):
!1;try{return a&&(new RegExp(a[1],a[2].replace("u",""))).test(c)}catch(f){}},pattern:function(a,b,c,d,q){if("string"!==typeof b)return null;try{try{var f=new RegExp("^(?:"+b+")$",q?"ui":"u")}catch(k){f=new RegExp("^(?:"+b+")$",q?"i":"")}if(e.FileList&&c instanceof FileList){for(a=0;a<c.length;a++)if(!f.test(c[a].name))return!1;return!0}return f.test(c)}catch(k){}},patternCaseInsensitive:function(a,b,c){return d.validators.pattern(a,b,c,null,!0)},integer:function(a,b,c){return"number"===a.type&&a.validity.badInput?
!1:/^-?[0-9]+$/.test(c)},"float":function(a,b,c,d){if("number"===a.type&&a.validity.badInput)return!1;c=c.replace(/ +/g,"").replace(/,/g,".");return/^-?[0-9]*\.?[0-9]+$/.test(c)?(d.value=c,!0):!1},min:function(a,b,c){if("number"===a.type){if(a.validity.rangeUnderflow)return!1;if(a.validity.badInput)return null}return null===b||parseFloat(c)>=b},max:function(a,b,c){if("number"===a.type){if(a.validity.rangeOverflow)return!1;if(a.validity.badInput)return null}return null===b||parseFloat(c)<=b},range:function(a,
b,c){if("number"===a.type){if(a.validity.rangeUnderflow||a.validity.rangeOverflow)return!1;if(a.validity.badInput)return null}return d.isArray(b)?(null===b[0]||parseFloat(c)>=b[0])&&(null===b[1]||parseFloat(c)<=b[1]):null},submitted:function(a){return a.form["nette-submittedBy"]===a},fileSize:function(a,b,c){if(e.FileList)for(a=0;a<c.length;a++)if(c[a].size>b)return!1;return!0},image:function(a,b,c){if(e.FileList&&c instanceof e.FileList)for(a=0;a<c.length;a++)if((b=c[a].type)&&"image/gif"!==b&&"image/png"!==
b&&"image/jpeg"!==b)return!1;return!0},"static":function(a,b){return b}};d.toggleForm=function(a,b){var c;d.toggles={};for(c=0;c<a.elements.length;c++)a.elements[c].tagName.toLowerCase()in{input:1,select:1,textarea:1,button:1}&&d.toggleControl(a.elements[c],null,null,!b);for(c in d.toggles)d.toggle(c,d.toggles[c],b)};d.toggleControl=function(a,b,c,f,e){b=b||d.parseJSON(a.getAttribute("data-nette-rules"));e=void 0===e?{value:d.getEffectiveValue(a)}:e;for(var g=!1,k=[],h=function(){d.toggleForm(a.form,
a)},l,p=0,q=b.length;p<q;p++){var n=b[p],u=n.op.match(/(~)?([^?]+)/),m=n.control?a.form.elements.namedItem(n.control):a;if(m){l=c;if(!1!==c){n.neg=u[1];n.op=u[2];l=a===m?e:{value:d.getEffectiveValue(m)};l=d.validateRule(m,n.op,n.arg,l);if(null===l)continue;else n.neg&&(l=!l);n.rules||(c=l)}if(n.rules&&d.toggleControl(a,n.rules,l,f,e)||n.toggle){g=!0;if(f){u=!document.addEventListener;var t=m.tagName?m.name:m[0].name;m=m.tagName?m.form.elements:m;for(var r=0;r<m.length;r++)m[r].name!==t||d.inArray(k,
m[r])||(d.addEvent(m[r],u&&m[r].type in{checkbox:1,radio:1}?"click":"change",h),k.push(m[r]))}for(var v in n.toggle||[])Object.prototype.hasOwnProperty.call(n.toggle,v)&&(d.toggles[v]=d.toggles[v]||(n.toggle[v]?l:!l))}}}return g};d.parseJSON=function(a){return"{op"===(a||"").substr(0,3)?eval("["+a+"]"):JSON.parse(a||"[]")};d.toggle=function(a,b,c){if(a=document.getElementById(a))a.style.display=b?"":"none"};d.initForm=function(a){d.toggleForm(a);a.noValidate||(a.noValidate=!0,d.addEvent(a,"submit",
function(b){d.validateForm(a)||(b&&b.stopPropagation?(b.stopPropagation(),b.preventDefault()):e.event&&(event.cancelBubble=!0,event.returnValue=!1))}))};d.initOnLoad=function(){d.addEvent(document,"DOMContentLoaded",function(){for(var a=0;a<document.forms.length;a++)for(var b=document.forms[a],c=0;c<b.elements.length;c++)if(b.elements[c].getAttribute("data-nette-rules")){d.initForm(b);break}d.addEvent(document.body,"click",function(a){for(a=a.target||a.srcElement;a;){if(a.form&&a.type in{submit:1,
image:1}){a.form["nette-submittedBy"]=a;break}a=a.parentNode}})})};d.isArray=function(a){return"[object Array]"===Object.prototype.toString.call(a)};d.inArray=function(a,b){if([].indexOf)return-1<a.indexOf(b);for(var c=0;c<a.length;c++)if(a[c]===b)return!0;return!1};d.webalize=function(a){a=a.toLowerCase();var b="",c;for(c=0;c<a.length;c++){var e=d.webalizeTable[a.charAt(c)];b+=e?e:a.charAt(c)}return b.replace(/[^a-z0-9]+/g,"-").replace(/^-|-$/g,"")};d.webalizeTable={"\u00e1":"a","\u00e4":"a","\u010d":"c",
"\u010f":"d","\u00e9":"e","\u011b":"e","\u00ed":"i","\u013e":"l","\u0148":"n","\u00f3":"o","\u00f4":"o","\u0159":"r","\u0161":"s","\u0165":"t","\u00fa":"u","\u016f":"u","\u00fd":"y","\u017e":"z"};return d});