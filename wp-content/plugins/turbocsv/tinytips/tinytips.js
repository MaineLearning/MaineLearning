/***********************************************************/
/*                    tinyTips Plugin                      */
/*                      Version: 1.1                       */
/*                      Mike Merritt                       */
/*                 Updated: Mar 2nd, 2010                  */
/* Copyright (c) 2009 Mike Merritt

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
/***********************************************************/

(function(a){a.fn.tinyTips=function(h,b){if(h==="null"){h="light"}var d=h+"Tip";var c='<div class="'+d+'"><div class="content"></div><div class="bottom">&nbsp;</div></div>';var e=300;var f;var g;a(this).hover(function(){a("body").append(c);var k="div."+d;f=a(k);f.hide();if(b==="title"){var i=a(this).attr("title")}else{if(b!=="title"){var i=b}}a(k+" .content").html(i);g=a(this).attr("title");a(this).attr("title","");var m=f.height()+2;var j=(f.width()/2)-(a(this).width()/2);var n=a(this).offset();var l=n;l.top=n.top-m;l.left=n.left-j;f.css("position","absolute").css("z-index","1000");f.css(l).fadeIn(e)},function(){a(this).attr("title",g);f.fadeOut(e,function(){a(this).remove()})})}})(jQuery);