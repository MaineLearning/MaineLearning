/*
 * Autocomplete - jQuery plugin 1.0 Beta
 *
 * Copyright (c) 2007 Dylan Verheul, Dan G. Switzer, Anjesh Tuladhar, Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.autocomplete.js 4485 2008-01-20 13:52:47Z joern.zaefferer $
 *
 */

;
(function($){

    $.fn.extend({
        autocomplete:function(urlOrData,options){
            var isUrl=typeof urlOrData=="string";
            options=$.extend({},$.Autocompleter.defaults,{
                url:isUrl?urlOrData:null,
                data:isUrl?null:urlOrData,
                delay:isUrl?$.Autocompleter.defaults.delay:10,
                type:$.Autocompleter.defaults.type,
                max:options&&!options.scroll?10:150
                },options);
            options.highlight=options.highlight||function(value){
                return value;
            };
            
            return this.each(function(){
                new $.Autocompleter(this,options);
            });
        },
        result:function(handler){
            return this.bind("result",handler);
        },
        search:function(handler){
            return this.trigger("search",[handler]);
        },
        flushCache:function(){
            return this.trigger("flushCache");
        },
        setOptions:function(options){
            return this.trigger("setOptions",[options]);
        },
        unautocomplete:function(){
            return this.trigger("unautocomplete");
        }
    });

$.Autocompleter=function(input,options){
    var KEY={
        UP:38,
        DOWN:40,
        DEL:46,
        TAB:9,
        RETURN:13,
        ESC:27,
        COMMA:188,
        PAGEUP:33,
        PAGEDOWN:34
    };
    
    var $input=$(input).attr("autocomplete","off").addClass(options.inputClass);
    var timeout;
    var previousValue="";
    var cache=$.Autocompleter.Cache(options);
    var hasFocus=0;
    var lastKeyPressCode;
    var config={
        mouseDownOnSelect:false
    };
    
    var select=$.Autocompleter.Select(options,input,selectCurrent,config);
    $input.keydown(function(event){
        lastKeyPressCode=event.keyCode;
        switch(event.keyCode){
            case KEY.UP:
                event.preventDefault();
                if(select.visible()){
                select.prev();
            }else{
                onChange(0,true);
            }
            break;
            case KEY.DOWN:
                event.preventDefault();
                if(select.visible()){
                select.next();
            }else{
                onChange(0,true);
            }
            break;
            case KEY.PAGEUP:
                event.preventDefault();
                if(select.visible()){
                select.pageUp();
            }else{
                onChange(0,true);
            }
            break;
            case KEY.PAGEDOWN:
                event.preventDefault();
                if(select.visible()){
                select.pageDown();
            }else{
                onChange(0,true);
            }
            break;
            case options.multiple&&$.trim(options.multipleSeparator)==","&&KEY.COMMA:case KEY.TAB:case KEY.RETURN:
                if(selectCurrent()){
                if(!options.multiple)
                    $input.blur();
                event.preventDefault();
                $input.focus();
            }
            break;
            case KEY.ESC:
                select.hide();
                break;
            default:
                clearTimeout(timeout);
                timeout=setTimeout(onChange,options.delay);
                break;
        }
    }).keypress(function(){}).focus(function(){
    hasFocus++;
}).blur(function(){
    hasFocus=0;
    if(!config.mouseDownOnSelect){
        hideResults();
    }
}).click(function(){
    if(hasFocus++>1&&!select.visible()){
        onChange(0,true);
    }
}).bind("search",function(){
    var fn=(arguments.length>1)?arguments[1]:null;
    function findValueCallback(q,data){
        var result;
        if(data&&data.length){
            for(var i=0;i<data.length;i++){
                if(data[i].result.toLowerCase()==q.toLowerCase()){
                    result=data[i];
                    break;
                }
            }
            }
if(typeof fn=="function")fn(result);else $input.trigger("result",result&&[result.data,result.value]);
}
$.each(trimWords($input.val()),function(i,value){
    request(value,findValueCallback,findValueCallback);
});
}).bind("flushCache",function(){
    cache.flush();
}).bind("setOptions",function(){
    $.extend(options,arguments[1]);
    if("data"in arguments[1])
        cache.populate();
}).bind("unautocomplete",function(){
    select.unbind();
    $input.unbind();
});
function selectCurrent(){
    var selected=select.selected();
    if(!selected)
        return false;
    var v=selected.result;
    previousValue=v;
    if(options.multiple){
        var words=trimWords($input.val());
        if(words.length>1){
            v=words.slice(0,words.length-1).join(options.multipleSeparator)+options.multipleSeparator+v;
        }
        v+=options.multipleSeparator;
    }
    $input.val(v);
    hideResultsNow();
    $input.trigger("result",[selected.data,selected.value]);
    return true;
}
function onChange(crap,skipPrevCheck){
    if(lastKeyPressCode==KEY.DEL){
        select.hide();
        return;
    }
    var currentValue=$input.val();
    if(!skipPrevCheck&&currentValue==previousValue)
        return;
    previousValue=currentValue;
    currentValue=lastWord(currentValue);
    if(currentValue.length>=options.minChars){
        $input.addClass(options.loadingClass);
        jQuery('.ajax-loader').show();
        if(!options.matchCase)
            currentValue=currentValue.toLowerCase();
        request(currentValue,receiveData,hideResultsNow);
    }else{
        stopLoading();
        select.hide();
    }
};

function trimWords(value){
    if(!value){
        return[""];
    }
    var words=value.split($.trim(options.multipleSeparator));
    var result=[];
    $.each(words,function(i,value){
        if($.trim(value))
            result[i]=$.trim(value);
    });
    return result;
}
function lastWord(value){
    if(!options.multiple)
        return value;
    var words=trimWords(value);
    return words[words.length-1];
}
function autoFill(q,sValue){
    if(options.autoFill&&(lastWord($input.val()).toLowerCase()==q.toLowerCase())&&lastKeyPressCode!=8){
        $input.val($input.val()+sValue.substring(lastWord(previousValue).length));
        $.Autocompleter.Selection(input,previousValue.length,previousValue.length+sValue.length);
    }
};

function hideResults(){
    clearTimeout(timeout);
    timeout=setTimeout(hideResultsNow,200);
};

function hideResultsNow(){
    select.hide();
    clearTimeout(timeout);
    stopLoading();
    if(options.mustMatch){
        $input.search(function(result){
            if(!result)$input.val("");
        });
    }
};

function receiveData(q,data){
    if(data&&data.length&&hasFocus){
        stopLoading();
        select.display(data,q);
        var newData=data[0].value.split(';');
        data.value=newData[0];
        autoFill(q,data.value);
        select.show();
    }else{
        hideResultsNow();
    }
};

function request(term,success,failure){
    if(!options.matchCase)
        term=term.toLowerCase();
    var data=cache.load(term);
    if(data&&data.length){
        success(term,data);
    }else if((typeof options.url=="string")&&(options.url.length>0)){
        var extraParams={};
        
        $.each(options.extraParams,function(key,param){
            extraParams[key]=typeof param=="function"?param():param;
        });
        $.ajax({
            mode:"abort",
            port:"autocomplete"+input.name,
            dataType:options.dataType,
            url:options.url,
            data:$.extend({
                q:lastWord(term),
                limit:options.max,
                type:options.type,
                action:'autocomplete_results',
                'cookie':encodeURIComponent(document.cookie)
                },extraParams),
            success:function(data){
                var parsed=options.parse&&options.parse(data)||parse(data);
                cache.add(term,parsed);
                success(term,parsed);
            }
        });
}else{
    failure(term);
}
};

function parse(data){
    var parsed=[];
    var rows=data.split("\n");
    for(var i=0;i<rows.length;i++){
        var row=$.trim(rows[i]);
        if(row){
            row=row.split("|");
            parsed[parsed.length]={
                data:row,
                value:row[0],
                result:options.formatResult&&options.formatResult(row,row[0])||row[0]
                };
            
    }
    }
return parsed;
};

function stopLoading(){
    $input.removeClass(options.loadingClass);
    jQuery('.ajax-loader').hide();
};

};

$.Autocompleter.defaults = {
    inputClass: "ac_input",
    resultsClass: "ac_results",
    loadingClass: "ac_loading",
    minChars: 1,
    delay: 400,
    matchCase: false,
    matchSubset: true,
    matchContains: false,
    cacheLength: 10,
    max: 100,
    type: 'resps',
    mustMatch: false,
    extraParams: {},
    selectFirst: false,
    formatItem: function(row) {
        return row[0];
    },
    autoFill: false,
    width: 0,
    multiple: false,
    multipleSeparator: ", ",
    highlight: function(value, term) {
        return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term.replace(/([\^\$\(\)\[\]\{\}\*\.\+\?\|\\])/gi, "\\$1") + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>");
    },
    scroll: true,
    scrollHeight: 250,
    attachTo: 'body'
};

$.Autocompleter.Cache=function(options){
    var data={};
    
    var length=0;
    function matchSubset(s,sub){
        if(!options.matchCase)
            s=s.toLowerCase();
        var i=s.indexOf(sub);
        if(i==-1)return false;
        return i==0||options.matchContains;
    };
    
    function add(q,value){
        if(length>options.cacheLength){
            flush();
        }
        if(!data[q]){
            length++;
        }
        data[q]=value;
    }
    function populate(){
        if(!options.data)return false;
        var stMatchSets={},nullData=0;
        if(!options.url)options.cacheLength=1;
        stMatchSets[""]=[];
        for(var i=0,ol=options.data.length;i<ol;i++){
            var rawValue=options.data[i];
            rawValue=(typeof rawValue=="string")?[rawValue]:rawValue;
            var value=options.formatItem(rawValue,i+1,options.data.length);
            if(value===false)
                continue;
            var firstChar=value.charAt(0).toLowerCase();
            if(!stMatchSets[firstChar])
                stMatchSets[firstChar]=[];
            var row={
                value:value,
                data:rawValue,
                result:options.formatResult&&options.formatResult(rawValue)||value
                };
                
            stMatchSets[firstChar].push(row);
            if(nullData++<options.max){
                stMatchSets[""].push(row);
            }
        };
        
    $.each(stMatchSets,function(i,value){
        options.cacheLength++;
        add(i,value);
    });
}
setTimeout(populate,25);
function flush(){
    data={};
    
    length=0;
}
return{
    flush:flush,
    add:add,
    populate:populate,
    load:function(q){
        if(!options.cacheLength||!length)
            return null;
        if(!options.url&&options.matchContains){
            var csub=[];
            for(var k in data){
                if(k.length>0){
                    var c=data[k];
                    $.each(c,function(i,x){
                        if(matchSubset(x.value,q)){
                            csub.push(x);
                        }
                    });
            }
            }
    return csub;
}else
if(data[q]){
    return data[q];
}else
if(options.matchSubset){
    for(var i=q.length-1;i>=options.minChars;i--){
        var c=data[q.substr(0,i)];
        if(c){
            var csub=[];
            $.each(c,function(i,x){
                if(matchSubset(x.value,q)){
                    csub[csub.length]=x;
                }
            });
        return csub;
    }
    }
}
return null;
}
};

};

$.Autocompleter.Select=function(options,input,select,config){
    var CLASSES={
        ACTIVE:"ac_over"
    };
    
    var listItems,active=-1,data,term="",needsInit=true,element,list;
    function init(){
        if(!needsInit)
            return;
        element=$("<div/>").hide().addClass(options.resultsClass).css("position","absolute").appendTo(options.attachTo);
        list=$("<ul>").appendTo(element).mouseover(function(event){
            if(target(event).nodeName&&target(event).nodeName.toUpperCase()=='LI'){
                active=$("li",list).removeClass(CLASSES.ACTIVE).index(target(event));
                $(target(event)).addClass(CLASSES.ACTIVE);
            }
        }).click(function(event){
        $(target(event)).addClass(CLASSES.ACTIVE);
        select();
        input.focus();
        return false;
    }).mousedown(function(){
        config.mouseDownOnSelect=true;
    }).mouseup(function(){
        config.mouseDownOnSelect=false;
    });
    if(options.width>0)
        element.css("width",options.width);
    needsInit=false;
}
function target(event){
    var element=event.target;
    while(element&&element.tagName!="LI")
        element=element.parentNode;
    if(!element)
        return[];
    return element;
}
function moveSelect(step){
    listItems.slice(active,active+1).removeClass();
    movePosition(step);
    var activeItem=listItems.slice(active,active+1).addClass(CLASSES.ACTIVE);
    if(options.scroll){
        var offset=0;
        listItems.slice(0,active).each(function(){
            offset+=this.offsetHeight;
        });
        if((offset+activeItem[0].offsetHeight-list.scrollTop())>list[0].clientHeight){
            list.scrollTop(offset+activeItem[0].offsetHeight-list.innerHeight());
        }else if(offset<list.scrollTop()){
            list.scrollTop(offset);
        }
    }
};

function movePosition(step){
    active+=step;
    if(active<0){
        active=listItems.size()-1;
    }else if(active>=listItems.size()){
        active=0;
    }
}
function limitNumberOfItems(available){
    return options.max&&options.max<available?options.max:available;
}
function fillList(){
    list.empty();
    var max=limitNumberOfItems(data.length);
    for(var i=0;i<max;i++){
        if(!data[i])
            continue;
        var formatted=options.formatItem(data[i].data,i+1,max,data[i].value,term);
        if(formatted===false)
            continue;
        var li=$("<li>").html(options.highlight(formatted,term)).addClass(i%2==0?"ac_event":"ac_odd").appendTo(list)[0];
        $.data(li,"ac_data",data[i]);
    }
    listItems=list.find("li");
    if(options.selectFirst){
        listItems.slice(0,1).addClass(CLASSES.ACTIVE);
        active=0;
    }
    list.bgiframe();
}
return{
    display:function(d,q){
        init();
        data=d;
        term=q;
        fillList();
    },
    next:function(){
        moveSelect(1);
    },
    prev:function(){
        moveSelect(-1);
    },
    pageUp:function(){
        if(active!=0&&active-8<0){
            moveSelect(-active);
        }else{
            moveSelect(-8);
        }
    },
pageDown:function(){
    if(active!=listItems.size()-1&&active+8>listItems.size()){
        moveSelect(listItems.size()-1-active);
    }else{
        moveSelect(8);
    }
},
hide:function(){
    element&&element.hide();
    active=-1;
},
visible:function(){
    return element&&element.is(":visible");
},
current:function(){
    return this.visible()&&(listItems.filter("."+CLASSES.ACTIVE)[0]||options.selectFirst&&listItems[0]);
},
show:function(){
    var offset=$(input).offset();
    element.css({
        width:typeof options.width=="string"||options.width>0?options.width:$(input).width(),
        top:offset.top+input.offsetHeight,
        left:offset.left
        }).show();
    if(options.scroll){
        list.scrollTop(0);
        list.css({
            maxHeight:options.scrollHeight,
            overflow:'auto'
        });
        if($.browser.msie&&typeof document.body.style.maxHeight==="undefined"){
            var listHeight=0;
            listItems.each(function(){
                listHeight+=this.offsetHeight;
            });
            var scrollbarsVisible=listHeight>options.scrollHeight;
            list.css('height',scrollbarsVisible?options.scrollHeight:listHeight);
            if(!scrollbarsVisible){
                listItems.width(list.width()-parseInt(listItems.css("padding-left"))-parseInt(listItems.css("padding-right")));
            }
        }
    }
},
selected:function(){
    var selected=listItems&&listItems.filter("."+CLASSES.ACTIVE).removeClass(CLASSES.ACTIVE);
    return selected&&selected.length&&$.data(selected[0],"ac_data");
},
unbind:function(){
    element&&element.remove();
}
};

};

$.Autocompleter.Selection=function(field,start,end){
    if(field.createTextRange){
        var selRange=field.createTextRange();
        selRange.collapse(true);
        selRange.moveStart("character",start);
        selRange.moveEnd("character",end);
        selRange.select();
    }else if(field.setSelectionRange){
        field.setSelectionRange(start,end);
    }else{
        if(field.selectionStart){
            field.selectionStart=start;
            field.selectionEnd=end;
        }
    }
field.focus();
};
})(jQuery);

/* Copyright (c) 2006 Brandon Aaron (http://brandonaaron.net)
 * bgIframe. Version 2.1.1
 */
(function($){
    $.fn.bgIframe=$.fn.bgiframe=function(s){
        if($.browser.msie&&/6.0/.test(navigator.userAgent)){
            s=$.extend({
                top:'auto',
                left:'auto',
                width:'auto',
                height:'auto',
                opacity:true,
                src:'javascript:false;'
            },s||{});
            var prop=function(n){
                return n&&n.constructor==Number?n+'px':n;
            },html='<iframe class="bgiframe"frameborder="0"tabindex="-1"src="'+s.src+'"'+'style="display:block;position:absolute;z-index:-1;'+(s.opacity!==false?'filter:Alpha(Opacity=\'0\');':'')+'top:'+(s.top=='auto'?'expression(((parseInt(this.parentNode.currentStyle.borderTopWidth)||0)*-1)+\'px\')':prop(s.top))+';'+'left:'+(s.left=='auto'?'expression(((parseInt(this.parentNode.currentStyle.borderLeftWidth)||0)*-1)+\'px\')':prop(s.left))+';'+'width:'+(s.width=='auto'?'expression(this.parentNode.offsetWidth+\'px\')':prop(s.width))+';'+'height:'+(s.height=='auto'?'expression(this.parentNode.offsetHeight+\'px\')':prop(s.height))+';'+'"/>';
            return this.each(function(){
                if($('> iframe.bgiframe',this).length==0)this.insertBefore(document.createElement(html),this.firstChild);
            });
        }
        return this;
    };

})(jQuery);

/* Copyright (c) 2007 Paul Bakaus (paul.bakaus@googlemail.com) and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * dimensions. Requires: jQuery 1.2+
 */
(function($){
    $.dimensions={
        version:'@VERSION'
    };
    
    $.each(['Height','Width'],function(i,name){
        $.fn['inner'+name]=function(){
            if(!this[0])return;
            var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';
            return this[name.toLowerCase()]()+num(this,'padding'+torl)+num(this,'padding'+borr);
        };
        
        $.fn['outer'+name]=function(options){
            if(!this[0])return;
            var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';
            options=$.extend({
                margin:false
            },options||{});
            return this[name.toLowerCase()]()
            +num(this,'border'+torl+'Width')+num(this,'border'+borr+'Width')
            +num(this,'padding'+torl)+num(this,'padding'+borr)
            +(options.margin?(num(this,'margin'+torl)+num(this,'margin'+borr)):0);
        };
    
    });
$.each(['Left','Top'],function(i,name){
    $.fn['scroll'+name]=function(val){
        if(!this[0])return;
        return val!=undefined?this.each(function(){
            this==window||this==document?window.scrollTo(name=='Left'?val:$(window)['scrollLeft'](),name=='Top'?val:$(window)['scrollTop']()):this['scroll'+name]=val;
        }):this[0]==window||this[0]==document?self[(name=='Left'?'pageXOffset':'pageYOffset')]||$.boxModel&&document.documentElement['scroll'+name]||document.body['scroll'+name]:this[0]['scroll'+name];
    };

});
$.fn.extend({
    position:function(){
        var left=0,top=0,elem=this[0],offset,parentOffset,offsetParent,results;
        if(elem){
            offsetParent=this.offsetParent();
            offset=this.offset();
            parentOffset=offsetParent.offset();
            offset.top-=num(elem,'marginTop');
            offset.left-=num(elem,'marginLeft');
            parentOffset.top+=num(offsetParent,'borderTopWidth');
            parentOffset.left+=num(offsetParent,'borderLeftWidth');
            results={
                top:offset.top-parentOffset.top,
                left:offset.left-parentOffset.left
                };
            
    }
    return results;
},
offsetParent:function(){
    var offsetParent=this[0].offsetParent;
    while(offsetParent&&(!/^body|html$/i.test(offsetParent.tagName)&&$.css(offsetParent,'position')=='static'))
        offsetParent=offsetParent.offsetParent;
    return $(offsetParent);
}
});
var num=function(el,prop){
    return parseInt($.css(el.jquery?el[0]:el,prop))||0;
};

})(jQuery);


// Tags autocompleter
jQuery.fn.autoCompleteTags = function(options) {
    var tmp = this;
    var settings = 
    {
        ul         : tmp,
        urlLookup  : ajaxurl,
        acOptions  : {
            type: 'tags'
        },
        foundClass : ".resps-tab",
        inputID : "#tags"
    }
    
    if(options) jQuery.extend(settings, options);
    
    var tags =
    {
        params  : settings,
        removeFind : function(o){
            tags.removeName(o);
            jQuery(o).unbind('click').parent().remove();
            jQuery(settings.inputID,tmp).focus();
            return tmp.tags;
        },
        removeName: function(o){
            var newID = o.parentNode.id.split('-');
            jQuery('#tag_names').removeClass(newID[1]);
        }
    }
    
    jQuery(settings.foundClass+" img.p").click(function(){
        tags.removeFind(this);
    });
    
    jQuery(settings.inputID,tmp).autocomplete(settings.urlLookup,settings.acOptions);
    jQuery(settings.inputID,tmp).result(function(e,d,f){
        var f = settings.foundClass.replace(/\./,'');
        var tag = String(d).replace(/\ /,'**');
        var d = String(d);
        var v = '<li class="'+f+'" id="un-'+d+'"><span>'+d+'</span> <span class="p">X</span></li>';
        var x = jQuery('#gtm_form .paste-tags').append(v);
        
        jQuery('#tag_names').addClass('|'+tag);

        
        jQuery('.p',x[0].previousSibling).click(function(){
            tags.removeFind(this);
        });
        jQuery(settings.inputID,tmp).val('');
    });
    
    return tags;
}

// Tags autocompleter
jQuery.fn.autoCompleteCats = function(options) {
    var tmp = this;
    var settings = 
    {
        ul         : tmp,
        urlLookup  : ajaxurl,
        acOptions  : {
            type: 'cats'
        },
        foundClass : ".resps-tab",
        inputID : "#cats"
    }
    
    if(options) jQuery.extend(settings, options);
    
    var tags =
    {
        params  : settings,
        removeFind : function(o){
            tags.removeName(o);
            jQuery(o).unbind('click').parent().remove();
            jQuery(settings.inputID,tmp).focus();
            return tmp.tags;
        },
        removeName: function(o){
            var newID = o.parentNode.id.split('-');
            jQuery('#cat_names').removeClass(newID[1]);
        }
    }
    
    jQuery(settings.foundClass+" img.p").click(function(){
        tags.removeFind(this);
    });
    
    jQuery(settings.inputID,tmp).autocomplete(settings.urlLookup,settings.acOptions);
    jQuery(settings.inputID,tmp).result(function(e,d,f){
        var f = settings.foundClass.replace(/\./,'');
        var tag = String(d).replace(/\ /,'**');
        var d = String(d);
        var v = '<input type="checkbox" name="cats[]" value="'+d+'"id="un-'+d+'" checked="checked"/><span>'+d+'</span>';
        var x = jQuery('#gtm_form .paste-cats').prepend(v);
        
        //jQuery('#cat_names').addClass('|'+tag);

        
        jQuery('.p',x[0].previousSibling).click(function(){
            tags.removeFind(this);
        });
        jQuery(settings.inputID,tmp).val('');
    });
    
    return tags;
}


