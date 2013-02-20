// uses WordPress 3.3+ features of including jquery-ui effects
var cred_cred=function($, locale, helpObj)
{
    $.fn.fadeToggle = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'toggle'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };
    $.fn.slideFadeToggle = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'toggle', height: 'toggle'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };
    $.fn.slideFadeDown = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'show', height: 'show'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };
    $.fn.slideFadeUp = function(speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function(){$(this).stop(true).animate({opacity: 'hide', height: 'hide'}, speed, easing, function() {
            if (jQuery.browser.msie) { this.style.removeAttribute('filter'); }
            if (jQuery.isFunction(callback)) { callback(); }
            });
        });
    };

    function hasUIEffect(effect) { return $.effects && $.effects[effect]; }

    $.fn.__show=function()
    {
        if (hasUIEffect('scale'))
            $(this).stop(true,true).show({
                effect:'scale',
                direction:'both',
                scale:'box',
                origin:['top','left'],
                easing:'expoEaseOut',
                speed:'slow'
                });
        else $(this).show();
    };

    $.fn.__hide=function()
    {
        if (hasUIEffect('scale'))
            $(this).stop(true,true).hide({
                effect:'scale',
                direction:'both',
                scale:'box',
                origin:['top','left'],
                easing:'expoEaseOut',
                speed:'slow'
            });
        else $(this).hide();
    };

    $.extend({
    //
    //$.fileDownload('/path/to/url/', options)
    //  see directly below for possible 'options'
    fileDownload: function (fileUrl, options) {

        var defaultFailCallback = function (responseHtml, url) {
            alert("A file download error has occurred, please try again.");
        };

        //provide some reasonable defaults to any unspecified options below
        var settings = $.extend({

            //
            //Requires jQuery UI: provide a message to display to the user when the file download is being prepared before the browser's dialog appears
            //
            preparingMessageHtml: null,

            //
            //Requires jQuery UI: provide a message to display to the user when a file download fails
            //
            failMessageHtml: null,

            //
            //the stock android browser straight up doesn't support file downloads initiated by a non GET: http://code.google.com/p/android/issues/detail?id=1780
            //specify a message here to display if a user tries with an android browser
            //if jQuery UI is installed this will be a dialog, otherwise it will be an alert
            //
            androidPostUnsupportedMessageHtml: "Unfortunately your Android browser doesn't support this type of file download. Please try again with a different browser.",

            //
            //Requires jQuery UI: options to pass into jQuery UI Dialog
            //
            dialogOptions: { modal: true },

            //
            //a function to call after a file download dialog/ribbon has appeared
            //Args:
            //  url - the original url attempted
            //
            successCallback: function (url) { },

            beforeDownloadCallback : false,
            //
            //a function to call after a file download dialog/ribbon has appeared
            //Args:
            //  responseHtml    - the html that came back in response to the file download. this won't necessarily come back depending on the browser.
            //                      in less than IE9 a cross domain error occurs because 500+ errors cause a cross domain issue due to IE subbing out the
            //                      server's error message with a "helpful" IE built in message
            //  url             - the original url attempted
            //
            failCallback: false,

            //failBeforeDownloadCallback : false,

            //
            // the HTTP method to use. Defaults to "GET".
            //
            httpMethod: "GET",

            //
            // if specified will perform a "httpMethod" request to the specified 'fileUrl' using the specified data.
            // data must be an object (which will be $.param serialized) or already a key=value param string
            //
            data: null,

            //
            //a period in milliseconds to poll to determine if a successful file download has occured or not
            //
            checkInterval: 100,

            //
            //the cookie name to indicate if a file download has occured
            //
            cookieName: "__CREDExportDownload",

            //
            //the cookie value for the above name to indicate that a file download has occured
            //
            cookieValue: "true",

            //
            //the cookie path for above name value pair
            //
            cookiePath: "/",

            //
            //the title for the popup second window as a download is processing in the case of a mobile browser
            //
            popupWindowTitle: "Initiating file download...",

            //
            //Functionality to encode HTML entities for a POST, need this if data is an object with properties whose values contains strings with quotation marks.
            //HTML entity encoding is done by replacing all &,<,>,',",\r,\n characters.
            //Note that some browsers will POST the string htmlentity-encoded whilst others will decode it before POSTing.
            //It is recommended that on the server, htmlentity decoding is done irrespective.
            //
            encodeHTMLEntities: true
        }, options);


        //Setup mobile browser detection: Partial credit: http://detectmobilebrowser.com/
        var userAgent = (navigator.userAgent || navigator.vendor || window.opera).toLowerCase();

        var isIos = false;                  //has full support of features in iOS 4.0+, uses a new window to accomplish this.
        var isAndroid = false;              //has full support of GET features in 4.0+ by using a new window. POST will resort to a POST on the current window.
        var isOtherMobileBrowser = false;   //there is no way to reliably guess here so all other mobile devices will GET and POST to the current window.

        if (/ip(ad|hone|od)/.test(userAgent)) {

            isIos = true;

        } else if (userAgent.indexOf('android') != -1) {

            isAndroid = true;

        } else {

            isOtherMobileBrowser = /avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|playbook|silk|iemobile|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(userAgent.substr(0, 4));

        }

        var httpMethodUpper = settings.httpMethod.toUpperCase();

        if (isAndroid && httpMethodUpper != "GET") {
            //the stock android browser straight up doesn't support file downloads initiated by non GET requests: http://code.google.com/p/android/issues/detail?id=1780

            if ($().dialog) {
                $("<div>").html(settings.androidPostUnsupportedMessageHtml).dialog(settings.dialogOptions);
            } else {
                alert(settings.androidPostUnsupportedMessageHtml);
            }

            return;
        }

        //wire up a jquery dialog to display the preparing message if specified
        var $preparingDialog = null;
        /*if (settings.preparingMessageHtml) {

            $preparingDialog = $("<div>").html(settings.preparingMessageHtml).dialog(settings.dialogOptions);

        }*/

        if (settings.beforeDownloadCallback) {

            settings.beforeDownloadCallback();
        }

        var internalCallbacks = {

            onSuccess: function (url) {

                //remove the perparing message if it was specified
                /*if ($preparingDialog) {
                    $preparingDialog.dialog('close');
                };*/

                settings.successCallback(url);

            },

            onFail: function (responseHtml, url) {

                //remove the perparing message if it was specified
                if ($preparingDialog) {
                    $preparingDialog.dialog('close');
                };

                //wire up a jquery dialog to display the fail message if specified
                if (settings.failMessageHtml) {

                    $("<div>").html(settings.failMessageHtml).dialog(settings.dialogOptions);

                    //only run the fallcallback if the developer specified something different than default
                    //otherwise we would see two messages about how the file download failed
                    if (settings.failCallback && settings.failCallback != defaultFailCallback) {
                        settings.failCallback(responseHtml, url);
                    }

                } else if (settings.failCallback) {

                    settings.failCallback(responseHtml, url);
                }
            }
        };


        //make settings.data a param string if it exists and isn't already
        if (settings.data !== null && typeof settings.data !== "string") {
            settings.data = $.param(settings.data);
        }


        var $iframe,
            downloadWindow,
            formDoc,
            $form;

        if (httpMethodUpper === "GET") {

            if (settings.data !== null) {
                //need to merge any fileUrl params with the data object

                var qsStart = fileUrl.indexOf('?');

                if (qsStart != -1) {
                    //we have a querystring in the url

                    if (fileUrl.substring(fileUrl.length - 1) !== "&") {
                        fileUrl = fileUrl + "&";
                    }
                } else {

                    fileUrl = fileUrl + "?";
                }

                fileUrl = fileUrl + settings.data;
            }

            if (isIos || isAndroid) {

                downloadWindow = window.open(fileUrl);
                downloadWindow.document.title = settings.popupWindowTitle;
                window.focus();

            } else if (isOtherMobileBrowser) {

                window.location(fileUrl);

            } else {

                //create a temporary iframe that is used to request the fileUrl as a GET request
                $iframe = $("<iframe>")
                    .hide()
                    .attr("src", fileUrl)
                    .appendTo("body");
            }

        } else {

            var formInnerHtml = "";

            if (settings.data !== null) {

                $.each(settings.data.replace(/\+/g, ' ').split("&"), function () {

                    var kvp = this.split("=");

                    var key = settings.encodeHTMLEntities ? htmlSpecialCharsEntityEncode(decodeURIComponent(kvp[0])) : decodeURIComponent(kvp[0]);
                    if (!key) return;
                    var value = kvp[1] || '';
                    value = settings.encodeHTMLEntities ? htmlSpecialCharsEntityEncode(decodeURIComponent(kvp[1])) : decodeURIComponent(kvp[1]);

                    formInnerHtml += '<input type="hidden" name="' + key + '" value="' + value + '" />';
                });
            }

            if (isOtherMobileBrowser) {

                $form = $("<form>").appendTo("body");
                $form.hide()
                    .attr('method', settings.httpMethod)
                    .attr('action', fileUrl)
                    .html(formInnerHtml);

            } else {

                if (isIos) {

                    downloadWindow = window.open("about:blank");
                    downloadWindow.document.title = settings.popupWindowTitle;
                    formDoc = downloadWindow.document;
                    window.focus();

                } else {

                    $iframe = $("<iframe style='display: none' src='about:blank'></iframe>").appendTo("body");
                    formDoc = getiframeDocument($iframe);
                }

                formDoc.write("<html><head></head><body><form method='" + settings.httpMethod + "' action='" + fileUrl + "'>" + formInnerHtml + "</form>" + settings.popupWindowTitle + "</body></html>");
                $form = $(formDoc).find('form');
            }

            $form.submit();
        }


        //check if the file download has completed every checkInterval ms
        setTimeout(checkFileDownloadComplete, settings.checkInterval);


        function checkFileDownloadComplete() {

            //has the cookie been written due to a file download occuring?
            if (document.cookie.indexOf(settings.cookieName + "=" + settings.cookieValue) != -1) {

                //execute specified callback
                internalCallbacks.onSuccess(fileUrl);

                //remove the cookie and iframe
                var date = new Date(1000);
                document.cookie = settings.cookieName + "=; expires=" + date.toUTCString() + "; path=" + settings.cookiePath;

                cleanUp(false);

                return;
            }

            //has an error occured?
            //if neither containers exist below then the file download is occuring on the current window
            if (downloadWindow || $iframe) {

                //has an error occured?
                try {

                    var formDoc;
                    if (downloadWindow) {
                        formDoc = downloadWindow.document;
                    } else {
                        formDoc = getiframeDocument($iframe);
                    }

                    if (formDoc && formDoc.body != null && formDoc.body.innerHTML.length > 0) {

                        var isFailure = true;

                        if ($form && $form.length > 0) {
                            var $contents = $(formDoc.body).contents().first();

                            if ($contents.length > 0 && $contents[0] === $form[0]) {
                                isFailure = false;
                            }
                        }

                        if (isFailure) {
                            internalCallbacks.onFail(formDoc.body.innerHTML, fileUrl);

                            cleanUp(true);

                            return;
                        }
                    }
                }
                catch (err) {

                    //500 error less than IE9
                    internalCallbacks.onFail('', fileUrl);

                    cleanUp(true);

                    return;
                }
            }


            //keep checking...
            setTimeout(checkFileDownloadComplete, settings.checkInterval);
        }

        //gets an iframes document in a cross browser compatible manner
        function getiframeDocument($iframe) {
            var iframeDoc = $iframe[0].contentWindow || $iframe[0].contentDocument;
            if (iframeDoc.document) {
                iframeDoc = iframeDoc.document;
            }
            return iframeDoc;
        }

        function cleanUp(isFailure) {

            setTimeout(function() {

                if (downloadWindow) {

                    if (isAndroid) {
                        downloadWindow.close();
                    }

                    if (isIos) {
                        if (isFailure) {
                            downloadWindow.focus(); //ios safari bug doesn't allow a window to be closed unless it is focused
                            downloadWindow.close();
                        } else {
                            downloadWindow.focus();
                        }
                    }
                }

            }, 0);
        }

        function htmlSpecialCharsEntityEncode(str) {
            return str.replace(/&/gm, '&amp;')
                .replace(/\n/gm, "&#10;")
                .replace(/\r/gm, "&#13;")
                .replace(/</gm, '&lt;')
                .replace(/>/gm, '&gt;')
                .replace(/"/gm, '&quot;')
                .replace(/'/gm, '&apos;'); //single quotes just to be safe
        }
    }
});


    var KEYCODE_ENTER = 13, KEYCODE_ESC = 27;
    var PREFIX = '_cred_cred_prefix_';

    // php functions
    var php = {
        stripslashes : function(str)
        {
            // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   improved by: Ates Goral (http://magnetiq.com)
            // +      fixed by: Mick@el
            // +   improved by: marrtins
            // +   bugfixed by: Onno Marsman
            // +   improved by: rezna
            // +   input by: Rick Waldron
            // +   reimplemented by: Brett Zamir (http://brett-zamir.me)
            // +   input by: Brant Messenger (http://www.brantmessenger.com/)
            // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
            // *     example 1: stripslashes('Kevin\'s code');
            // *     returns 1: "Kevin's code"
            // *     example 2: stripslashes('Kevin\\\'s code');
            // *     returns 2: "Kevin\'s code"
            return (str + '').replace(/\\(.?)/g, function (s, n1) {
                switch (n1) {
                case '\\':
                    return '\\';
                case '0':
                    return '\u0000';
                case '':
                    return '';
                default:
                    return n1;
                }
            });
        },

        isNumber : function(n)
        {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }
    };

    // private properties
    var _priv = {
        form_id : 0,
        form_name : '',
        field_data : null,
        CodeMirrorEditor : false,
        CodeMirrorJSEditor : false,
        CodeMirrorCSSEditor : false
    };

    // auxilliary functions
    var aux = {
        pad : '\t',
        nl : '\r\n',

        swapEl : function($item, func)
        {
            var props = { position: 'absolute', visibility: 'hidden', display: 'block' },
                //dim = { width:0, height:0, innerWidth: 0, innerHeight: 0,outerWidth: 0,outerHeight: 0 },
                $hiddenParents = $item.parents().add($item).not(':visible');
                //includeMargin = (!includeMargin)? false : includeMargin;

            var oldProps = [];
            $hiddenParents.each(function() {
                var old = {};

                for ( var name in props ) {
                    old[ name ] = this.style[ name ];
                    this.style[ name ] = props[ name ];
                }

                oldProps.push(old);
            });

            func();
            /*dim.width = $item.width();
            dim.outerWidth = $item.outerWidth(includeMargin);
            dim.innerWidth = $item.innerWidth();
            dim.height = $item.height();
            dim.innerHeight = $item.innerHeight();
            dim.outerHeight = $item.outerHeight(includeMargin);*/

            $hiddenParents.each(function(i) {
                var old = oldProps[i];
                for ( var name in props ) {
                    this.style[ name ] = old[ name ];
                }
            });

            //return dim;
        },

        enableExtraCodeMirror : function()
        {
            // if codemirror activated, enable syntax highlight
            if (window.CodeMirror)
            {
                aux.swapEl($("#cred-extra-css-editor"), function(){
                    _priv.CodeMirrorCSSEditor = CodeMirror.fromTextArea(document.getElementById("cred-extra-css-editor"), {
                        mode: "css",
                        tabMode: "indent",
                        lineWrapping: true,
                        lineNumbers : true
                    });
                });
                $("#cred-extra-css-editor").hide();
                aux.swapEl($("#cred-extra-js-editor"), function(){
                    _priv.CodeMirrorJSEditor = CodeMirror.fromTextArea(document.getElementById("cred-extra-js-editor"), {
                        mode: "javascript",
                        tabMode: "indent",
                        lineWrapping: true,
                        lineNumbers : true
                    });
                });
                $("#cred-extra-js-editor").hide();
            }
        },

        toggleCodeMirror : function(on)
        {
            // if codemirror activated, enable syntax highlight
            if (window.CodeMirror)
            {
                if (!on && window.CodeMirror && _priv.CodeMirrorEditor)
                {
                    _priv.CodeMirrorEditor.toTextArea();
                    _priv.CodeMirrorEditor=false;
                    return !on;
                }
                else if (on && window.CodeMirror && !_priv.CodeMirrorEditor)
                {
                    CodeMirror.defineMode("myshortcodes", function(config, parserConfig) {

                      var indentUnit = config.indentUnit;
                      var Kludges = {
                        autoSelfClosers: {
                        },
                        implicitlyClosed: {
                        },
                        contextGrabbers: {
                            },
                        doNotIndent: {},
                        allowUnquoted: false,
                        allowMissing: false
                      };

                      // Return variables for tokenizers
                      var tagName, type;

                      function inText(stream, state) {
                        function chain(parser) {
                          state.tokenize = parser;
                          return parser(stream, state);
                        }

                        var ch = stream.next();
                        if (ch == "[") {
                            type = stream.eat("/") ? "closeShortcode" : "openShortcode";
                            stream.eatSpace();
                            tagName = "";
                            var c;
                            while ((c = stream.eat(/[^\s\u00a0=<>\"\'\[\]\/?]/))) tagName += c;
                            state.tokenize = inShortcode;
                            return "shortcode";
                        }
                        else {
                          stream.eatWhile(/[^\[]/);
                          return null;
                        }
                      }

                      function inShortcode(stream, state) {
                        var ch = stream.next();
                        if (ch == "]" || (ch == "/" && stream.eat("]"))) {
                          state.tokenize = inText;
                          type = ch == "]" ? "endShortcode" : "selfcloseShortcode";
                          return "shortcode";
                        }
                        else if (ch == "=") {
                          type = "equals";
                          return null;
                        }
                        else if (/[\'\"]/.test(ch)) {
                          state.tokenize = inAttribute(ch);
                          return state.tokenize(stream, state);
                        }
                        else {
                          stream.eatWhile(/[^\s\u00a0=<>\"\'\[\]\/?]/);
                          return "word";
                        }
                      }

                      function inAttribute(quote) {
                        return function(stream, state) {
                          while (!stream.eol()) {
                            if (stream.next() == quote) {
                              state.tokenize = inShortcode;
                              break;
                            }
                          }
                          return "string";
                        };
                      }

                      var curState, setStyle;
                      function pass() {
                        for (var i = arguments.length - 1; i >= 0; i--) curState.cc.push(arguments[i]);
                      }
                      function cont() {
                        pass.apply(null, arguments);
                        return true;
                      }

                      function pushContext(tagName, startOfLine) {
                        var noIndent = Kludges.doNotIndent.hasOwnProperty(tagName) || (curState.context && curState.context.noIndent);
                        curState.context = {
                          prev: curState.context,
                          shortcodeName: tagName,
                          tagName: null,
                          indent: curState.indented,
                          startOfLine: startOfLine,
                          noIndent: noIndent
                        };
                      }
                      function popContext() {
                        if (curState.context) curState.context = curState.context.prev;
                      }

                      function element(type) {
                        if (type == "openShortcode")
                        {
                            curState.shortcodeName = tagName;
                            return cont(attributes, endtag(curState.startOfLine));
                        }
                        else
                            return cont();
                      }
                      function endtag(startOfLine) {
                        return function(type) {
                          if (type == "selfcloseShortcode" ||
                              (type == "endShortcode" && Kludges.autoSelfClosers.hasOwnProperty(curState.shortcodeName.toLowerCase()))) {
                                maybePopContext(curState.shortcodeName.toLowerCase());
                                return cont();
                          }
                          if (type == "endShortcode") {
                            maybePopContext(curState.shortcodeName.toLowerCase());
                            pushContext(curState.shortcodeName, startOfLine);
                            return cont();
                          }
                          return cont();
                        };
                      }
                      function endclosetag(err) {
                        return function(type) {
                          if (err)
                          {
                            setStyle = "error";
                          }
                          if (type == "endShortcode") { popContext(); return cont(); }
                          setStyle = "error";
                          return cont(arguments.callee);
                        };
                      }
                      function maybePopContext(nextTagName) {
                        var parentTagName;
                        while (true) {
                          if (!curState.context) {
                            return;
                          }
                          parentTagName = curState.context.shortcodeName.toLowerCase();
                          if (!Kludges.contextGrabbers.hasOwnProperty(parentTagName) ||
                              !Kludges.contextGrabbers[parentTagName].hasOwnProperty(nextTagName)) {
                            return;
                          }
                          popContext();
                        }
                      }

                      function attributes(type) {
                        if (type == "word") {setStyle = "attribute"; return cont(attribute, attributes);}
                        if (type == "endShortcode" || type == "selfcloseShortcode") return pass();
                        setStyle = "error";
                        return cont(attributes);
                      }
                      function attribute(type) {
                        if (type == "equals") return cont(attvalue, attributes);
                        if (!Kludges.allowMissing) setStyle = "error";
                        return (type == "endShortcode" || type == "selfcloseShortcode") ? pass() : cont();
                      }
                      function attvalue(type) {
                        if (type == "string") return cont(attvaluemaybe);
                        if (type == "word" && Kludges.allowUnquoted) {setStyle = "string"; return cont();}
                        setStyle = "error";
                        return (type == "endShortcode" || type == "selfCloseShortcode") ? pass() : cont();
                      }
                      function attvaluemaybe(type) {
                        if (type == "string") return cont(attvaluemaybe);
                        else return pass();
                      }

                      var shortcodesOverlay= (function(){
                        return {
                        startState: function() {
                          return {tokenize: inText, cc: [], indented: 0, startOfLine: true, tagName: null, shortcodeName: null, context: null};
                        },

                        token: function(stream, state) {
                          if (stream.sol()) {
                            state.startOfLine = true;
                            state.indented = stream.indentation();
                          }
                          if (stream.eatSpace()) return null;

                          setStyle = type = tagName = null;
                          var style = state.tokenize(stream, state);
                          state.type = type;
                          if ((style || type)) {
                            curState = state;
                            while (true) {
                              var comb = state.cc.pop() || element;
                              if (comb(type || style)) break;
                            }
                          }
                          state.startOfLine = false;
                          return setStyle || style;
                        },


                        electricChars: "/"
                      };
                  })();
                    /*var shortcodesOverlay =  {
                            token: function(stream, state)
                            {
                                var ch, str, attr=false, attrval=false, shortname=false;
                                if (stream.match("["))
                                {
                                    while ((ch = stream.next()) != null)
                                    {
                                      if (ch == "]") break;
                                    }
                                    return "myshortcodes";
                                  }
                                  while (stream.next() != null && !stream.match("[", false)) {}
                                    return null;
                            }
                        };*/
                        return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), shortcodesOverlay);
                    });
                    _priv.CodeMirrorEditor = CodeMirror.fromTextArea(document.getElementById("content"), {
                        mode: 'myshortcodes',//"text/html",
                        tabMode: "indent",
                        lineWrapping: true,
                        lineNumbers : true
                    });

                    return on;
                }
            }
            return false;
        },

        InsertAtCursor : function(myField, myValue)
        {
            var $myField=myField;
            var ed=myField.attr('id'); // myField;
            if (ed && ed.charAt(0)=='#')
                ed=ed.substring(1);
            // if tinyMCE
            if (ed && window.tinyMCE!=undefined && (editor=window.tinyMCE.get(ed)) != null && editor.isHidden() == false)
            {
                editor.focus();
                editor.execCommand("mceInsertContent",false, myValue);
            }
            // else if CodeMirror
            else if (ed && window.CodeMirror!=undefined && _priv.CodeMirrorEditor && ($myField[0]==_priv.CodeMirrorEditor.getTextArea()))
            {
                _priv.CodeMirrorEditor.focus()
                if (_priv.CodeMirrorEditor.somethingSelected())
                {
                    _priv.CodeMirrorEditor.replaceSelection(myValue);
                }
                else
                {
                    // set at current cursor
                    var current_cursor=_priv.CodeMirrorEditor.getCursor(true);
                    _priv.CodeMirrorEditor.setSelection(current_cursor, current_cursor);
                    _priv.CodeMirrorEditor.replaceSelection(myValue);
                    // append
                    //_priv.CodeMirrorEditor.setValue(_priv.CodeMirrorEditor.getValue()+myValue);
                }
            }
            // else other text fields
            else
            {
                myField=myField[0]; //$(myField)[0];
                if (document.selection)
                {
                    myField.focus();
                    sel = document.selection.createRange();
                    sel.text = myValue;
                }
                else if ((myField.selectionStart != null) && (myField.selectionStart != undefined)/* == 0 || myField.selectionStart == '0'*/)
                {
                    var startPos = parseInt(myField.selectionStart);
                    var endPos = parseInt(myField.selectionEnd);
                    myField.value = myField.value.substring(0, startPos) + myValue +
                                myField.value.substring(endPos, myField.value.length);
                }
                else
                {
                     myField.value += myValue;
                }
            }
            $myField.trigger('paste');
        },

        wrapOrPaste : function(myField, myValue1, myValue2)
        {
            var $myField=myField;
            var ed=myField.attr('id'); // myField;
            if (ed && ed.charAt(0)=='#')
                ed=ed.substring(1);
            // if tinyMCE
            if (ed && window.tinyMCE!=undefined && (editor=window.tinyMCE.get(ed)) != null && editor.isHidden() == false)
            {
                editor.focus();
                editor.execCommand("mceReplaceContent",false, myValue1+editor.selection.getContent({format : 'raw'})+myValue2);
                //console.log(editor);
            }
            // else if CodeMirror
            else if (ed && window.CodeMirror!=undefined && _priv.CodeMirrorEditor && ($myField[0]==_priv.CodeMirrorEditor.getTextArea()))
            {
                _priv.CodeMirrorEditor.focus()
                if (_priv.CodeMirrorEditor.somethingSelected())
                {
                    _priv.CodeMirrorEditor.replaceSelection(myValue1+_priv.CodeMirrorEditor.getSelection()+myValue2);
                }
                else
                {
                    // set at current cursor
                    var current_cursor=_priv.CodeMirrorEditor.getCursor(true);
                    _priv.CodeMirrorEditor.setSelection(current_cursor, current_cursor);
                    _priv.CodeMirrorEditor.replaceSelection(myValue1+myValue2);
                }
            }
            // else other text fields
            else
            {
                myField=myField[0];
                if (document.selection)
                {
                    myField.focus();
                    sel = document.selection.createRange();
                    //console.log(sel);
                    sel.text = myValue1+ sel.text +myValue2;
                }
                else if ((myField.selectionStart != null) && (myField.selectionStart != undefined)/* == 0 || myField.selectionStart == '0'*/)
                {
                    var startPos = parseInt(myField.selectionStart);
                    var endPos = parseInt(myField.selectionEnd);
                    var sel = myField.value.substring(startPos, endPos);
                    myField.value = myField.value.substring(0, startPos) + myValue1+sel+myValue2 +
                                myField.value.substring(endPos, myField.value.length);
                    //console.log('substring');
                }
                else
                {
                     myField.value += myValue1+myValue2;
                }
            }
            $myField.trigger('paste');
        },

        successfunc : function(resp)
        {
            var data=null, cont2, cont3;
            var cont=$('#cred-shortcodes-box-inner');
            cont.empty();

            // save data for future refernce
            _priv.field_data=resp;

            if (resp.form_fields && parseInt(resp.form_fields_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.form_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.form_fields;
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        data=$("<a href='javascript:void(0);' class='button cred_field_add' title='"+resp2[f].description+"'>"+resp2[f].name+"</a>");
                        data.data('field',resp2[f]);
                        cont3.append(data);
                    }
                }
            }
            if (resp.post_fields && parseInt(resp.post_fields_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.post_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.post_fields;
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        data=$("<a href='javascript:void(0);' class='button cred_field_add' title='"+resp2[f].description+"'>"+resp2[f].name+"</a>");
                        data.data('field',resp2[f]);
                        cont3.append(data);
                    }
                }
            }
            if (resp.custom_fields && parseInt(resp.custom_fields_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.custom_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.custom_fields;
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        data=$("<a href='javascript:void(0);' class='button cred_field_add' title='"+resp2[f].description+"'>"+resp2[f].name+"</a>");
                        data.data('field',resp2[f]);
                        cont3.append(data);
                    }
                }
            }
            if (resp.taxonomies && parseInt(resp.taxonomies_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.taxonomy_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.taxonomies;
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        resp2[f].taxonomy=true;
                        data=$("<a href='javascript:void(0);' class='button cred_field_add'>"+resp2[f].label+"</a>");
                        data.data('field',resp2[f]);
                        cont3.append(data);
                        if (resp2[f].hierarchical)
                        {
                            resp2[f].aux={master_taxonomy:resp2[f].name,name:resp2[f].name+'_add_new',add_new_taxonomy:true};
                            data=$("<a href='javascript:void(0);' class='button cred_field_add'>"+resp2[f].label+' Add New'+"</a>");
                            data.data('field',resp2[f].aux);
                            cont3.append(data);
                        }
                        else
                        {
                            resp2[f].aux={master_taxonomy:resp2[f].name,name:resp2[f].name+'_popular',popular:true};
                            data=$("<a href='javascript:void(0);' class='button cred_field_add'>"+resp2[f].label+' Popular'+"</a>");
                            data.data('field',resp2[f].aux);
                            cont3.append(data);
                        }
                    }
                }
            }
            if (resp.parents && parseInt(resp.parents_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.parent_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.parents;
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        data=$("<a href='javascript:void(0);' class='button cred_field_add' title='"+resp2[f].description+"'>"+resp2[f].name+"</a>");
                        data.data('field',resp2[f]);
                        cont3.append(data);
                    }
                }
            }
            if (resp.extra_fields && parseInt(resp.extra_fields_count)>0)
            {
                cont2=$('<div class="cred-accordeon-item"><a href="javascript:void(0)" class="cred-fields-group-heading">'+locale.extra_fields+'</a></div>');
                cont3=$('<div class="cred-accordeon-item-inside"></div>');
                cont2.append(cont3);
                cont.append(cont2);
                resp2=resp.extra_fields;
                var disabled_fields=[];
                for (var f in resp2)
                {
                    if (resp2.hasOwnProperty(f))
                    {
                        if (!resp2[f].disabled)
                        {
                            data=$("<a href='javascript:void(0);' class='button cred_field_add' title='"+resp2[f].description+"'>"+resp2[f].name+"</a>");
                            data.data('field',resp2[f]);
                            cont3.append(data);
                        }
                        else
                        {
                            data=$("<div class='cred_disabled_container'><a href='javascript:void(0);' class='button cred_field_disabled' disabled='disabled'>"+resp2[f].name+"</a><span class='cred-field-disabled-reason'>"+resp2[f].disabled_reason+"</span></div>");
                            data.data('field',resp2[f]);
                            // add them at the end
                            disabled_fields.push(data);
                            //cont.append(data);
                        }
                    }
                }
                for (var i=0; i<disabled_fields.length;i++)
                    cont3.append(disabled_fields[i]);
            }
            //cont.find('a').after(' ');

            $('.cred_ajax_loader_small').hide();
        },

        shortcode : function(field, extra)
        {
            /*if (field.is_parent)
            console.log(arguments);*/

            var field_out='';
            var post_type='';
            var value=' value=""';
            if (field && field.slug)
            {
                if (field.post_type)
                {
                    post_type=' post="'+field.post_type+'"';
                }
                if (field.value)
                {
                    value=' value="'+field.value+'"';
                }
                if (field.type=='image' || field.type=='file')
                {
                    var max_width=(extra&&extra.max_width)?extra.max_width:false;
                    var max_height=(extra&&extra.max_height)?extra.max_height:false;
                    if (max_width && !isNaN(max_width))
                        value+=' max_width="'+max_width+'"';
                    if (max_height && !isNaN(max_height))
                        value+=' max_height="'+max_height+'"';
                }
                if (field.is_parent)
                {
                    var parent_order=(extra&&extra.parent_order)?extra.parent_order:false;
                    var parent_ordering=(extra&&extra.parent_ordering)?extra.parent_ordering:false;
                    var parent_results=(extra&&extra.parent_max_results)?extra.parent_max_results:false;
                    var required=(extra&&extra.required)?extra.required:false;
                    var no_parent_text=(extra&&extra.parent_text)?extra.parent_text:false;
                    var select_parent_text=(extra&&extra.select_parent_text)?extra.select_parent_text:false;
                    var validate_parent_text=(extra&&extra.validate_parent_text)?extra.validate_parent_text:false;
                    if (parent_results!==false && !isNaN(parent_results))
                        value+=' max_results="'+parent_results+'"';
                    if (parent_order)
                        value+=' order="'+parent_order+'"';
                    if (parent_ordering)
                        value+=' ordering="'+parent_ordering+'"';
                    if (required)
                        value+=' required="'+required.toString()+'"';
                    if (required && select_parent_text!==false)
                        value+=' select_text="'+select_parent_text+'"';
                    if (required && validate_parent_text!==false)
                        value+=' validate_text="'+validate_parent_text+'"';
                    if (!required && no_parent_text!==false)
                        value+=' no_parent_text="'+no_parent_text+'"';
                }
                if (field.type=='textfield' ||
                    field.type=='textarea' ||
                    field.type=='wysiwyg' ||
                    field.type=='date' ||
                    field.type=='phone' ||
                    field.type=='url' ||
                    field.type=='numeric' ||
                    field.type=='email')
                {
                    var readonly=(extra&&extra.readonly)?extra.readonly:false;
                    var escape=(extra&&extra.escape)?extra.escape:false;
                    var placeholder=(extra&&extra.placeholder)?extra.placeholder:false;
                    if (readonly)
                        value+=' readonly="'+readonly.toString()+'"';
                    if (escape)
                        value+=' escape="'+escape.toString()+'"';
                    if (placeholder && ''!=placeholder)
                        value+=' placeholder="'+placeholder+'"';
                }
                field_out='[cred-field field="'+field.slug+'"' + post_type + value + ']';
            }
            if (field && field.taxonomy)
            {
                if (field.hierarchical)
                    field_out='[cred-field field="'+field.name+'" display="checkbox"]';
                else
                    field_out='[cred-field field="'+field.name+'"]';
            }
            if (field && field.popular)
            {
                //field_out='[cred-field field="'+field.name+'" taxonomy="'+field.master_taxonomy+'" type="show_popular" value="Show Popular"]';
                field_out='[cred-field field="'+field.name+'" taxonomy="'+field.master_taxonomy+'" type="show_popular"]';
            }
            if (field && field.add_new_taxonomy)
            {
                //field_out='[cred-field field="'+field.name+'" taxonomy="'+field.master_taxonomy+'" type="add_new" value="Add New"]';
                field_out='[cred-field field="'+field.name+'" taxonomy="'+field.master_taxonomy+'" type="add_new"]';
            }
            return field_out;
        },

        fieldOutput : function(field, form_id, form_name, WPML, pad)
        {
            if (!pad)
                pad='';
            var field_out=[];
            var post_type='';
            var value='';
            WPML = WPML || false;

            if (field)
            {
                if (WPML)
                {
                    field_out.push(pad+'<div class="cred-field cred-field-'+field.slug+'">');
                    field_out.push(pad+aux.pad+'<div class="cred-label">[wpml-string context="cred-form-'+form_name+'-'+form_id+'" name="'+field.name+'"]'+field.name+'[/wpml-string]</div>');
                }
                else
                {
                    field_out.push(pad+'<div class="cred-field cred-field-'+field.slug+'">');
                    field_out.push(pad+aux.pad+'<div class="cred-label">'+field.name+'</div>');
                }
                var args=[field];
                if (arguments.length==5)
                    args=args.concat(Array.prototype.slice.call(arguments, 6));
                else
                    args=args.concat(Array.prototype.slice.call(arguments, 5));
                //console.log(args);
                field_out.push(pad+aux.pad+aux.shortcode.apply(null, args));

                field_out.push(pad+'</div>');
            }
            return field_out.join(aux.nl);
        },

        groupOutput : function(group,fields,obj, form_id, form_name, WPML, pad)
        {
            if (!pad)
                pad='';
            var group_out=[];
            var group_class_slug='cred-group-'+group.replace(/\s+/g,'-');
            group_out.push(pad+'<div class="cred-group '+group_class_slug+'">');
            group_out.push(pad+aux.pad+'<div><h2>'+group+'</h2></div>');
            for (var ii=0; ii<fields.length; ii++)
            {
                if (obj[fields[ii]]._cred_ignore) continue;
                group_out.push(aux.fieldOutput(obj[fields[ii]], form_id, form_name, WPML, pad+aux.pad));
            }
            group_out.push(pad+'</div>');

            return group_out.join(aux.nl)+aux.nl;

        },

        groupOutputContent : function(slug, group_name, content, pad)
        {
            if (!pad)
                pad='';
            var group_out=[];
            var group_class_slug='cred-group-'+slug.replace(/\s+/g,'-');
            group_out.push(pad+'<div class="cred-group '+group_class_slug+'">');
            group_out.push(pad+aux.pad+'<div><h2>'+group_name+'</h2></div>');
            var lines=content.split(aux.nl);
            for (var i=0; i<lines.length; i++)
            {
                lines[i]=pad+aux.pad+lines[i];
            }
            content=lines.join(aux.nl);
            group_out.push(content);
            group_out.push(pad+'</div>');

            return group_out.join(aux.nl);
        },

        taxOutput : function(tax, form_id, form_name, WPML, pad)
        {
            WPML = WPML || false;

            if (!pad)
                pad='';
            var tax_out=[];
            tax_out.push(pad+'<div class="cred-taxonomy cred-taxonomy-'+tax.name+'">');

            if (WPML)
                tax_out.push(pad+aux.pad+'<div class="cred-label"><h2>[wpml-string context="cred-form-'+form_name+'-'+form_id+'" name="'+tax.label+'"]'+tax.label+'[/wpml-string]</h2></div>');
            else
                tax_out.push(pad+aux.pad+'<div class="cred-label"><h2>'+tax.label+'</h2></div>');
            tax_out.push(pad+aux.pad+aux.shortcode(tax));
            tax_out.push(pad+aux.pad+'<div class="cred-taxonomy-auxilliary cred-taxonomy-auxilliary-'+tax.aux.name+'">');
            tax_out.push(pad+aux.pad+aux.pad+aux.shortcode(tax.aux));
            tax_out.push(pad+aux.pad+'</div>');

            tax_out.push(pad+'</div>');

            return tax_out.join(aux.nl);
        },

        refreshFieldSelector : function(el, do_alert, selected)
        {
            if (!_priv.field_data || !_priv.field_data.post_fields) return;

            var obj=_priv.field_data.custom_fields;
            var content=pub.getContent();
            var sel=/*$(this)*/el.closest('div').find('select.cred_mail_to_field:eq(0) optgroup');
            var selected_1=(selected)?selected:sel.find('option:selected').val();
            sel.children('option').not(':eq(0)').remove();
            var found=[],gfound=[];
            for (var ff in obj)
            {
                if (!obj.hasOwnProperty(ff)) continue;

                if (obj[ff].type=='email')
                {
                    found.push(obj[ff].slug);
                }
            }

            // generic fields
            var gfoundmatch=content.match(new RegExp('\\[cred\\-generic\\-field[^\\[\\]]*type=[\\"\']email[\\"\'][^\\[\\]]*\\]','g'));

            if (found.length) // types fields
            {
                for (var ii=0; ii<found.length; ii++)
                {
                    var val=/*'wpcf-'+*/found[ii];
                    var text=found[ii];
                    if (new RegExp('\\[cred\\-field[^\\[\\]]*field=[\\"\']'+text+'[\\"\'][^\\[\\]]*\\]','g').test(content))
                    {
                        if (selected_1 && val==selected_1)
                            sel.append('<option value="'+val+'" selected="selected">'+text+'</option>');
                        else
                            sel.append('<option value="'+val+'">'+text+'</option>');
                    }
                }
            }
            if (gfoundmatch)
            {
                for (var ii=0; ii<gfoundmatch.length; ii++)
                {
                    var gname=gfoundmatch[ii].match(/(?:\s+field=[\'\"])([^=]+?)(?:[\'\"]\s+)/);
                    if (gname)
                    {
                        if (selected_1 && gname[1]==selected_1)
                            sel.append('<option value="'+gname[1]+'" selected="selected">'+gname[1]+'</option>');
                        else
                            sel.append('<option value="'+gname[1]+'">'+gname[1]+'</option>');
                    }
                }
            }
            if (do_alert)
                alert(locale.refresh_done);
        }
    };

    // methods made publicly available
    var pub = {

        doCheck : function()
        {
            // title check
            var title=$('#title').val();

            if (title.match('[^a-zA-Z0-9\-_ ]') || title.length<=0)
            {
                alert(locale.invalid_title);
                return false;
            }
            return true;
        },
        getContent : function()
        {
            if (pub.getCodeMirror())
                return pub.getCodeMirror().getValue();
            else
                return $('#content').val();
        },

        getCSSContent : function()
        {
            if (pub.getCSSCodeMirror())
                return pub.getCSSCodeMirror().getValue();
            else
                return $('#cred-extra-css-editor').val();
        },

        getJSContent : function()
        {
            if (pub.getJSCodeMirror())
                return pub.getJSCodeMirror().getValue();
            else
                return $('#cred-extra-js-editor').val();
        },

        getCodeMirrorContents : function()
        {
            return {
                'content' : pub.getContent(),
                'cred-extra-css-editor' : pub.getCSSContent(),
                'cred-extra-js-editor' : pub.getJSContent()
            };
        },

        getFieldData : function()
        {
            return _priv.field_data;
        },

        getCodeMirror : function()
        {
            return _priv.CodeMirrorEditor;
        },

        getCSSCodeMirror : function()
        {
            return _priv.CodeMirrorCSSEditor;
        },

        getJSCodeMirror : function()
        {
            return _priv.CodeMirrorJSEditor;
        },

         wrapOrPaste : function(text1,text2)
         {
            aux.wrapOrPaste($('#content'), text1, text2);
         },

         insert : function(text)
         {
            aux.InsertAtCursor($('#content'), text);
         },

         /*toggleCodeMirror : function(on)
         {
            return aux.toggleCodeMirror(on);
         },*/

         form_post: {
                init: function(url, admin_url, purl, settingsPage, useCodeMirror)
                {
                    var doinit=true;

                    _priv.settingsPage=settingsPage;

                    $('.wrap > h2').addClass('cred-h2');

                    var cred_media_buttons=$('.cred-media-button');
                    var cred_popup_boxes=$('.cred-popup-box');
                    var cred_disable_notification_overlay=$('<div class="cred_disabled_overlay"></div>');

                    // save current form id
                    //cred_cred.form_post.form_id=$('#post_ID').val();
                    //cred_cred.form_post.form_name=$('#title').val();

                    // add explain texts for title and content
                    $('#titlediv').prepend('<p class="cred-explain-text">'+locale.title_explain_text+'</p>');
                    $('#postdivrich').prepend('<p class="cred-explain-text">'+locale.content_explain_text+'</p>');

                    $('#titlediv').prepend('<a id="cred_add_forms_to_site_help" class="cred-help-link" style="position:absolute;top:0;right:0;" href="'+helpObj['add_forms_to_site']['link']+'" target="_blank" title="'+helpObj['add_forms_to_site']['text']+'">'+helpObj['add_forms_to_site']['text']+'</a>');

                    var formtypediv=$('#credformtypediv');
                    var posttypediv=$('#credposttypediv');
                    var extradiv=$('#credextradiv');
                    var messagesdiv=$('#credmessagesdiv');
                    var notificationdiv=$('#crednotificationdiv');
                    if (formtypediv.length>0)
                    {
                        formtypediv.remove();
                        formtypediv.insertBefore('#postdivrich');
                    }
                    if (posttypediv.length>0)
                    {
                        posttypediv.remove();
                        posttypediv.insertBefore('#postdivrich');
                    }
                    /*if (extradiv.length>0)
                    {
                        extradiv.remove();
                        extradiv.insertBefore('#postdivrich');
                    }*/
                    if (messagesdiv.length>0)
                    {
                        messagesdiv.remove();
                        messagesdiv.insertAfter('#postdivrich');
                    }
                    if (notificationdiv.length>0)
                    {
                        notificationdiv.remove();
                        notificationdiv.insertAfter('#postdivrich');
                    }

                    // wrap post div rich in postbox
                    var pdr=$('#postdivrich').wrap('<div class="inside"></div>').parent();
                    pdr=pdr.wrap('<div id="postdivrichwrap" class="postbox"></div>').parent();
                    pdr.prepend('<div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle"><span>'+locale.form_content+'</span></h3>');
                    if (extradiv.length>0)
                    {
                        //extradiv.remove();
                        extradiv.insertAfter('#postdivrich');
                        extradiv.addClass('cred-exclude');
                    }

                    /*formtypediv.removeClass('closed');
                    posttypediv.removeClass('closed');
                    notificationdiv.removeClass('closed');
                    pdr.removeClass('closed');*/
                    formtypediv.removeClass('hide-if-js');
                    posttypediv.removeClass('hide-if-js');
                    extradiv.removeClass('hide-if-js');
                    messagesdiv.removeClass('hide-if-js');
                    notificationdiv.removeClass('hide-if-js');
                    pdr.removeClass('hide-if-js');

                    // hide some stuff
                    if (0==$('#modulemanagerdiv').length)
                    {
                        // if not module manager sidebar meta box exists
                        $('.postbox-container').css({'display':'none'});
                        $('#post-body').removeClass('columns-2').addClass('columns-1');
                        $('#poststuff').removeClass('has-right-sidebar');
                        $('#poststuff .inner-sidebar').hide();
                    }
                    else
                    {
                        $('#modulemanagerdiv').addClass('cred-not-hide');
                        $('.postbox-container .postbox:not(.cred-not-hide)').css({'display':'none'});
                    }
                    $('#screen-meta-links').css({'display':'none'});

                    if ($.fn.sortable) // allow sortable cred metaboxes, not saved right now
                    {
                        $('#post-body-content').sortable({
                            placeholder: 'sortable-placeholder',
                            connectWith: '#post-body-content',
                            items: '#post-body-content > .postbox',
                            handle: '.hndle',
                            cursor: 'move',
                            delay: 50,
                            distance: 2,
                            tolerance: 'pointer',
                            forcePlaceholderSize: true,
                            helper: 'clone',
                            opacity: 0.65/* TODO save order,
                            stop: function(e,ui) {
                                if ( $(this).find('#dashboard_browser_nag').is(':visible') && 'dashboard_browser_nag' != this.firstChild.id ) {
                                    $(this).sortable('cancel');
                                    return;
                                }

                                postboxes.save_order(pagenow);
                            },
                            receive: function(e,ui) {
                                if ( 'dashboard_browser_nag' == ui.item[0].id )
                                    $(ui.sender).sortable('cancel');

                                postboxes._mark_area();
                            }*/
                        });
                    }
                    // enable CodeMirror for CSS/JS Editor
                    aux.enableExtraCodeMirror();
                    if (extradiv.length>0)
                    {
                        //setTimeout(function(){
                        if ($('#cred-extra-css-editor').hasClass('cred-always-open') || $('#cred-extra-js-editor').hasClass('cred-always-open'))
                            extradiv.removeClass('closed');
                        else
                            extradiv.addClass('closed');
                        //}, 200);
                    }

                    // hide page selection if not Go to page.. option
                    $('.cred_form_action_page_container',formtypediv).hide();
                    $('#cred_form_success_action').change(function(){
                        if ($(this).val()!='form' && $(this).val()!='message')
                            $('.cred_form_action_delay_container',formtypediv).show();
                        else
                            $('.cred_form_action_delay_container',formtypediv).hide();

                        if (doinit)
                        {
                            if ($(this).val()=='message')
                                $('.cred_form_action_message_container',formtypediv).show();
                            else
                                $('.cred_form_action_message_container',formtypediv).hide();
                        }
                        else
                        {
                            if ($(this).val()=='message')
                                $('.cred_form_action_message_container',formtypediv).slideFadeDown('slow','quintEaseOut');
                            else
                                $('.cred_form_action_message_container',formtypediv).slideFadeUp('slow','quintEaseIn');
                        }

                        if ($(this).val()=='page')
                            $('.cred_form_action_page_container',formtypediv).show();
                        else
                        {
                            $('.cred_form_action_page_container',formtypediv).hide();
                            /*if ($(this).val()=='post' && $('#cred_post_status').val()!='publish')
                            {
                                $(this).val('');
                                alert(locale.post_status_must_be_public);
                            }*/
                        }
                    });
                    // do it now
                    $('#cred_form_success_action').trigger('change');

                    $('#cred_form_type').change(function(){
                        var original_option=$('#cred_post_status option').filter(function(){
                            if ($(this).val()=='original')
                                return true;
                            return false;
                            });
                        if ($(this).val()!='edit')
                        {
                            original_option.attr('disabled','disabled');
                            if (original_option.attr('selected'))
                                $('#cred_post_status').val('');
                        }
                        else
                        {
                            original_option.removeAttr('disabled');
                            if (
                                $('#cred_post_status option').filter(function(){
                                if ($(this).attr('selected') && !$(this).attr('disabled'))
                                    return true;
                                return false;
                                }).length==0
                            )
                            original_option.attr('selected','selected');
                        }
                    });
                    $('#cred_form_type').trigger('change');

                    $('#post').append('<input id="cred-submit" type="submit" class="button-primary" value="'+locale.submit_but+'" />');

                    $('.cred_ajax_loader_small').hide();

                    var just_loaded=true;
                    $('#post-body-content').on('change','.cred_ajax_change:eq(0)',function(){
                        var thiss=this;
                        $('.cred_ajax_loader_small').show();
                        $.ajax({
                            url: purl,
                            timeout: 10000,
                            type: 'POST',
                            data: 'post_type='+$(thiss).val(),
                            dataType: 'json',
                            success: function(resp){
                                aux.successfunc(resp);
                                // update fields first time with values
                                if (just_loaded)
                                {
                                    $('#crednotificationdiv .cred-refresh-button').each(function(){
                                        aux.refreshFieldSelector($(this), false, $(this).children('.cred-current-field-value').text());
                                    });
                                    just_loaded=false;
                                }
                            }
                        });
                    });
                    // do it now
                    $('.cred_ajax_change').eq(0).trigger('change');

                    $('#cred-shortcodes-box').on('click','a.cred-fields-group-heading',function(event){
                        $('#cred-shortcodes-box .cred-accordeon-item-inside').stop(true).slideUp('fast');
                        //$(this).addClass('cred-accordeon-open');
                        $(this).next().stop(true).slideDown('slow','quintEaseOut');
						$('.cred-accordeon-item').removeClass('cred-accordeon-item-active') // remove active class from
						$(this).closest('.cred-accordeon-item').addClass('cred-accordeon-item-active') // add .active class to parent .cred-fields-group-heading;
                    });

                    $('#cred-shortcodes-box').on('click','a.cred_field_add',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        var el=$(this);
                        var data=el.data('field');
                        var shortcode;
                        // remove all popups
                        $('.additional_field_options_popup').remove();
                        if (data.slug=='credform')
                            shortcode='['+data.slug+']\n[/'+data.slug+']';
                       else if (data['type']=='image' /*&& data['type']!='file'*/)
                        {
                            // load template
                            $($('#cred_image_dimensions_validation_template').html()).appendTo('#cred-shortcodes-box');
                            $('#cred_image_dimensions_validation').__show();
                            $('#cred_image_dimensions_cancel_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();
                                setTimeout(function(){$('#cred_image_dimensions_validation').__hide();},50);
                            });
                            $('#cred_image_dimensions_validation_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();

                                var max_width=parseInt($.trim($('#cred_max_width').val()),10);
                                var max_height=parseInt($.trim($('#cred_max_height').val()),10);
                                shortcode=aux.shortcode(data, {max_width:max_width, max_height:max_height});
                                aux.InsertAtCursor($('#content'),shortcode);
                                setTimeout(function(){$('#cred-shortcodes-box').__hide();$('#cred-shortcode-button').css('z-index',1);},50);

                            });
                            return false;
                        }
                       // fields can have, placeholder, readonly and escape attributes
                       else if (data['type']=='textfield' ||
                            data['type']=='textarea' ||
                            data['type']=='wysiwyg' ||
                            data['type']=='date' ||
                            data['type']=='url' ||
                            data['type']=='phone' ||
                            data['type']=='numeric' ||
                            data['type']=='email')
                        {
                            // load template
                            $($('#cred_text_extra_options_template').html()).appendTo('#cred-shortcodes-box');
                            $('#cred_text_extra_options').__show();
                            $('#cred_text_extra_options_cancel_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();
                                setTimeout(function(){$('#cred_text_extra_options').__hide();},50);
                            });
                            $('#cred_text_extra_options_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();

                                var placeholder=$.trim($('#cred_text_extra_placeholder').val());
                                var readonly=$('#cred_text_extra_readonly').is(':checked');
                                var escape=false; //$('#cred_text_extra_escape').is(':checked');
                                shortcode=aux.shortcode(data, {placeholder:placeholder, readonly:readonly, escape:escape});
                                aux.InsertAtCursor($('#content'),shortcode);
                                setTimeout(function(){$('#cred-shortcodes-box').__hide();$('#cred-shortcode-button').css('z-index',1);},50);

                            });
                            return false;
                        }
                       else if (data.is_parent)
                        {
                            // load template
                            $($('#cred_parent_field_settings_template').html()).appendTo('#cred-shortcodes-box');
                            $('#cred_parent_field_settings #cred_parent_required').unbind('change').bind('change',function(){
                                if ($(this).is(':checked'))
                                {
                                    $('#cred_parent_field_settings #cred_parent_select_text_container').stop(true).slideFadeDown('fast');
                                    $('#cred_parent_field_settings #cred_parent_no_parent_container').stop(true).slideFadeUp('fast');
                                }
                                else
                                {
                                    $('#cred_parent_field_settings #cred_parent_select_text_container').stop(true).slideFadeUp('fast');
                                    $('#cred_parent_field_settings #cred_parent_no_parent_container').stop(true).slideFadeDown('fast');
                                }
                            });

                            // set default values
                            $('#cred_parent_select_text').val('--- Select '+data.data.post_type+' ---');
                            $('#cred_parent_validation_text').val(data.data.post_type+' must be selected');
                            $('#cred_parent_no_parent_text').val('No Parent');

                            setTimeout(function(){$('#cred_parent_field_settings #cred_parent_required').trigger('change');},50);

                            $('#cred_parent_field_settings').__show();
                            $('#cred_parent_extra_cancel_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();
                                setTimeout(function(){$('#cred_parent_field_settings').__hide();},50);
                            });
                            $('#cred_parent_extra_button').unbind('click').click(function(event){
                                event.stopPropagation();
                                event.preventDefault();

                                var parent_order=$('#cred_parent_order_by').val();
                                var parent_ordering=$('#cred_parent_ordering').val();
                                var parent_max_results=parseInt($.trim($('#cred_parent_max_results').val()),10);
                                var required=$('#cred_parent_required').is(':checked');
                                var no_parent_text=$('#cred_parent_no_parent_text').val();
                                var select_parent_text=$('#cred_parent_select_text').val();
                                var validate_parent_text=$('#cred_parent_validation_text').val();
                                shortcode=aux.shortcode(data, {parent_order:parent_order, parent_ordering:parent_ordering, parent_max_results:parent_max_results, required:required, no_parent_text:no_parent_text, select_parent_text:select_parent_text, validate_parent_text:validate_parent_text});
                                aux.InsertAtCursor($('#content'),shortcode);
                                setTimeout(function(){$('#cred-shortcodes-box').__hide();$('#cred-shortcode-button').css('z-index',1);},50);

                            });
                            return false;
                        }
                       else
                            shortcode=aux.shortcode(data);
                        aux.InsertAtCursor($('#content'),shortcode);
                        setTimeout(function(){$('#cred-shortcodes-box').__hide();$('#cred-shortcode-button').css('z-index',1);},50);
                    });


                    $('#cred-scaffold-box').on('click','#cred-scaffold-insert',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        var scaffold=$('#cred-scaffold-area').val();

                        aux.InsertAtCursor($('#content'),scaffold);
                        setTimeout(function(){$('#cred-scaffold-box').__hide();$('#cred-scaffold-button').css('z-index',1);},50);
                    });

                    $('#post-body-content').on('click','#cred-shortcode-button-button',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();

                        $(this).closest('.cred-media-button').css('z-index',100);
                        $('.additional_field_options_popup').hide();
                        $('#cred-shortcodes-box').__show();
						 if ($('.cred-accordeon-item-active').length === 0) { // make fist accordion item active but only if there was no accordion item opened before. It makes last opened accordeon item opened.
							 $('.cred-accordeon-item:first-child').addClass('cred-accordeon-item-active').find('.cred-accordeon-item-inside').slideDown('fast');
						 }

                    });

                    $('#post-body-content').on('click','#cred-generic-shortcode-button-button',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();

                        $(this).closest('.cred-media-button').css('z-index',100);
                        $('#cred-generic-shortcodes-box').__show();
                    });

                    var genScaffold=function(){
                        var resp=_priv.field_data;

                        if (!resp || !resp.post_fields)
                        {
                            alert(locale.form_types_not_set);
                            return false;
                        }

                        var includeWPML=false;
                        if ($('#cred_include_wpml_scaffold').is(':checked'))
                            includeWPML=true;

                        var form_name_1=$('#title').val();
                        if ($.trim(form_name_1)=='')
                        {
                            alert(locale.set_form_title);
                            return false;
                        }
                        var form_id_1=$('#post_ID').val();

                        var cont=$('#cred-scaffold-area');
                        var groups_out='';
                        var groups={};
                        var nlcnt=0;
                        for (var f in resp.groups)
                        {
                            if (resp.groups.hasOwnProperty(f))
                            {
                                nlcnt++;
                                var fields=resp.groups[f];
                                groups[f]=fields;
                                fields=fields.split(',');
                                groups_out+=aux.groupOutput(f,fields,resp.custom_fields, form_id_1, form_name_1, includeWPML, aux.pad)+aux.nl;
                            }
                        }
                        //if (nlcnt>1)
                          //  groups_out+=aux.nl;

                        var taxs_out='';
                        if (parseInt(resp.taxonomies_count,10)>0)
                        {
                            for (var f in resp.taxonomies)
                            {
                                if (resp.taxonomies.hasOwnProperty(f))
                                {
                                    taxs_out+=aux.taxOutput(resp.taxonomies[f],form_id_1, form_name_1, includeWPML, '')+aux.nl;
                                }
                            }
                        }
                        var parents_out='';
                        if (parseInt(resp.parents_count,10)>0)
                        {
                            for (var f in resp.parents)
                            {
                                if (resp.parents.hasOwnProperty(f))
                                {
                                    parents_out+=aux.fieldOutput(resp.parents[f], form_id_1, form_name_1, includeWPML, '',
                                    // extra params
                                    'date', 'desc', 0,
                                    false, 'No Parent', '-- Select '+resp.parents[f].data.post_type+' --', resp.parents[f].data.post_type+' must be selected')+aux.nl;
                                }
                            }
                        }
                        // add fields
                        var out='';
                        if ('minimal'==$('input[name="cred_theme_css"]:checked').val()) // bypass script and other styles added to form, minimal
                            out+='[credform class="cred-form cred-keep-original"]'+aux.nl+aux.nl;
                        else
                            out+='[credform class="cred-form"]'+aux.nl+aux.nl;
                        out+=aux.pad+aux.shortcode(resp.form_fields['form_messages'])+aux.nl+aux.nl;
                        out+=aux.fieldOutput(resp.post_fields['post_title'], form_id_1, form_name_1, includeWPML,aux.pad)+aux.nl+aux.nl;
                        if (resp.post_fields['post_content'].supports)
                        {
                            out+=aux.fieldOutput(resp.post_fields['post_content'], form_id_1, form_name_1, includeWPML,aux.pad)+aux.nl+aux.nl;
                        }
                        if (resp.post_fields['post_excerpt'].supports)
                        {
                            out+=aux.fieldOutput(resp.post_fields['post_excerpt'], form_id_1, form_name_1, includeWPML,aux.pad)+aux.nl+aux.nl;
                        }
                        out+=groups_out;
                        if (resp.extra_fields['_featured_image'].supports)
                            out+=aux.fieldOutput(resp.extra_fields['_featured_image'], form_id_1, form_name_1, includeWPML, aux.pad)+aux.nl+aux.nl;
                        if (parseInt(resp.taxonomies_count,10)>0)
                            out+=aux.groupOutputContent('taxonomies','Taxonomies',taxs_out, aux.pad)+aux.nl+aux.nl;
                        if (parseInt(resp.parents_count,10)>0)
                            out+=aux.groupOutputContent('parents','Parents',parents_out, aux.pad)+aux.nl+aux.nl;
                        if ($('#cred_include_captcha_scaffold').is(':checked'))
                            out+=aux.pad+'<div class="cred-field cred-field-recaptcha">'+aux.shortcode(resp.extra_fields['recaptcha'])+'</div>'+aux.nl+aux.nl;
                        out+=aux.pad+aux.shortcode(resp.form_fields['form_submit'])+aux.nl+aux.nl;
                        out+='[/credform]'+aux.nl;
                        cont.val(out);
                        return true;
                    };

                    // re-generate scaffols when this option changes
                    $('#cred_include_captcha_scaffold').change(function(){
                        genScaffold();
                    });
                    $('#cred_include_wpml_scaffold').change(function(){
                        genScaffold();
                    });

                    $('#post-body-content').on('click','#cred-scaffold-button-button',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();

                        if (!genScaffold())
                            return false;

                        $(this).closest('.cred-media-button').css('z-index',100);
                        $('#cred-scaffold-box').__show();
                    });

                    /*cred_popup_boxes.click(function(event){
                        event.stopPropagation();
                    });*/

                    //$('html').click(function(e){
                    $(document).mouseup(function (e){
                        if (
                            cred_popup_boxes.filter(function(){
                                return $(this).is(':visible');
                                }).has(e.target).length === 0
                            )
                        {
                            cred_media_buttons.css('z-index',1);
                            cred_popup_boxes.hide();
                        }
                    });

                    $(document).keyup(function(e) {
                        if (e.keyCode == KEYCODE_ESC)
                        {
                            cred_media_buttons.css('z-index',1);
                            cred_popup_boxes.hide();
                        }
                    });

                    $('#cred-notification-add-button').click(function(event){
                        event.preventDefault();
                        var box=$('#crednotificationdiv #cred_notification_settings_panel_container');
                        var count=parseInt(box.attr('class').replace('cred_notification_settings_panel_container-',''));//children('.cred_notification_settings_panel').length;
                        var tmpl=$('#cred_notification_template').html().
                        replace(/name='cred_mail_to_where_selector\[\]'/g,"name='cred_mail_to_where_selector["+count+"]'").
                        replace(/name='cred_mail_to_user\[\]'/g,"name='cred_mail_to_user["+count+"]'").
                        replace(/name='cred_mail_to_field\[\]'/g,"name='cred_mail_to_field["+count+"]'").
                        replace(/name='cred_mail_to_specific\[\]'/g,"name='cred_mail_to_specific["+count+"]'").
                        replace(/name='cred_mail_subject\[\]'/g,"name='cred_mail_subject["+count+"]'").
                        replace(/name='cred_mail_body\[\]'/g,"name='cred_mail_body["+count+"]'");
                        // update count
                        box.attr('class','cred_notification_settings_panel_container-'+(count+1));

                        var el=$(tmpl);
                        box.append(el);
                        el.hide().fadeIn('slow');
                        el.find('.cred_mail_to_container').hide();
                       if (box.children('.cred_notification_settings_panel').length==1)
                        {
                            // if first to add, enable notifications
                            $('#cred_notification_enable').attr('checked','checked');
                            $('#cred_notification_enable').trigger('change');
                        }
                        return false;
                    });

                    // cancel buttons
                    $('#post-body-content').on('click','.cred-cred-cancel-close',function(event){
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();
                    });

                    $('#post-body-content').on('click','.cred-notification-remove-button',function(event){
                        event.preventDefault();
                        $(this).closest('.cred_notification_settings_panel').fadeOut('slow',function(){
                            var box=$('#cred_notification_settings_panel_container');
                            $(this).remove();
                            if (box.children('.cred_notification_settings_panel').length==0)
                            {
                                // if empty, disable notifications
                                $('#cred_notification_enable').removeAttr('checked');
                                $('#cred_notification_enable').trigger('change');
                            }
                        });
                        return false;
                    });

                    // event handlers for notification box
                    $('#post-body-content').on('change','#cred_notification_enable',function(){
                        var tt=$(this);
                        if (tt.is(':checked'))
                        {
                            //$('.cred_notification_settings_panel *').removeAttr('disabled');
                            //$('#cred_notification_settings_panel_container')./*blurjs('remove');*/fadeTo('slow',1);
                            cred_disable_notification_overlay.fadeOut('slow',function(){
                                $(this).remove();
                            });
                        }
                        else
                        {
                            //$('.cred_notification_settings_panel *').attr('disabled',true);
                            //$('#cred_notification_settings_panel_container')./*blurjs();*/fadeTo('slow',0.5);
                            cred_disable_notification_overlay.appendTo($('#cred_notification_settings_panel_container')).fadeIn('slow');
                        }
                    });
                    // do it now on init
                    $('#cred_notification_enable').trigger('change');


                    /*$('#post-body-content').on('change','.cred_mail_to_user',function(){
                            $('.cred_mail_to',$(this).closest('.cred_notification_settings_panel')).val($(this).val());
                            $('.cred_mail_to',$(this).closest('.cred_notification_settings_panel')).trigger('paste');
                    });*/

                    $('#post-body-content').on('change','.cred_notification_subject_codes',function(){
                            var id=$('.cred_mail_subject',$(this).closest('.cred_notification_settings_panel')).attr('id')
                            aux.InsertAtCursor($('.cred_mail_subject',$(this).closest('.cred_notification_settings_panel')), $(this).val());
                    });
                    $('#post-body-content').on('change','.cred_notification_body_codes',function(){
                            aux.InsertAtCursor($('.cred_mail_body',$(this).closest('.cred_notification_settings_panel')), $(this).val());
                    });

                    $('#cred-preview-button a').unbind('click').bind('click',function(event){

                        var data;

                        /*if (_priv.CodeMirrorEditor)
                            data=_priv.CodeMirrorEditor.getValue();
                        else
                            data=$('#content').val();*/

                        data=pub.getContent();

                        if ($.trim(data)=='')
                        {
                            event.preventDefault();
                            return false;
                        }

                        var post_type=$('#cred_post_type').val();
                        var form_type=$('#cred_form_type').val();
                        var css_to_use=$('input[name="cred_theme_css"]:checked').val();
                        var extra_css=pub.getCSSContent();
                        var extra_js=pub.getJSContent();

                        if (post_type=='' || form_type=='')
                        {
                            event.preventDefault();
                            alert(locale.form_types_not_set);
                            return false;
                        }

                        var id=$('#post_ID').val();
                        var target='CRED_Preview_'+id;
                        var action=url+'index.php?cred_form_preview='+id; //$(this).attr('href');
                        //var action=url+'cred_preview/'+id+'/';

                        var previewPopup = window.open('', target, "status=0,title=0,height=600,width=800,scrollbars=1,resizable=1");
                        if (previewPopup)
                        {
                            var previewForm=$("<form style='display:none' name='cred_form_preview_form' method='post' target='"+target+"' action='"+action+"'><input type='hidden' name='"+PREFIX+"form_css_to_use' value='"+css_to_use+"' /><input type='hidden' name='"+PREFIX+"form_preview_post_type' value='"+post_type+"' /><input type='hidden' name='"+PREFIX+"form_preview_form_type' value='"+form_type+"' /><textarea  name='"+PREFIX+"form_preview_content'>"+php.stripslashes(data)+"</textarea><textarea  name='"+PREFIX+"extra_css_to_use'>"+extra_css+"</textarea><textarea  name='"+PREFIX+"extra_js_to_use'>"+extra_js+"</textarea></form>");
                            // does not work in IE(9), so add it to current doc and submit
                            //$(previewPopup.document.body).append(previewForm);
                            $(document.body).append(previewForm);
                            previewForm.submit();
                            // remove it after a while
                            setTimeout(function()
                            {
                                previewForm.remove();
                            },500);

                        }
                        else
                            alert(locale.enable_popup_for_preview);
                    });


                    $('#crednotificationdiv').on('click','.cred-refresh-button',function(event){
                        event.preventDefault();
                        aux.refreshFieldSelector($(this), true, null);
                        return false;
                    });

                    $('#crednotificationdiv').on('change','input[name^="cred_mail_to_where_selector"]',function(event){
                        $(this).closest('.cred_notification_settings_panel').find('.cred_mail_to_container').hide();

                        if ($(this).is(':checked'))
                        {
                            $(this).closest('label').nextAll('.cred_mail_to_container').first().show();
                        }

                        if ($(this).is(':checked') && $(this).val()=='mail_field')
                        {
                            // auto refresh
                            aux.refreshFieldSelector($(this).closest('div').find('.cred-refresh-button'), false, null);
                            //$(this).closest('div').children('.cred-refresh-button').trigger('click');
                        }
                    });

                    // show/hide notification options
                    $('.cred_notification_settings_panel .cred_mail_to_container').hide();
                    //$('.cred_notification_settings_panel').find('input[name^="cred_mail_to_where_selector"]:checked').closest('label').nextAll('.cred_mail_to_container').first().show();
                    $('input[name^="cred_mail_to_where_selector"]:checked').each(function(){
                        $(this).closest('label').nextAll('.cred_mail_to_container').first().show();
                    });

                    //$('#cred_syntax_highlght_button').click(function(){
                    $('#ed_toolbar').on('click','#qt_content_cred_syntax_highlight', function(){
                        if ($(this).hasClass('cred_qt_codemirror_on'))
                        {
                            aux.toggleCodeMirror(false);
                            $(this).removeClass('cred_qt_codemirror_on');
                            $(this).attr('title',locale.syntax_highlight_off);
                            $(this).val(locale.syntax_highlight_off);
                        }
                        else
                        {
                            aux.toggleCodeMirror(true);
                            $(this).addClass('cred_qt_codemirror_on');
                            $(this).attr('title',locale.syntax_highlight_on);
                            $(this).val(locale.syntax_highlight_on);
                        }
                        // set setting on DB also
                        $.ajax({
                            url: admin_url+'/Settings/toggleHighlight',
                            timeout: 10000,
                            type: 'POST',
                            data: {'cred_highlight':($(this).hasClass('cred_qt_codemirror_on'))?'1':'0'},
                            dataType: 'html',
                            success: function(result)
                            {
                            },
                            error: function()
                            {
                            }
                        });

                    });
                    function triggerCodeMirror(sel, deep)
                    {
                        deep = deep || 1;
                        var el=$(sel);
                        if (el.length)
                            el.trigger('click');
                        else
                        {
                            if (deep<50)
                                setTimeout(function(){triggerCodeMirror(sel,++deep);},60);
                        }
                    }
                    useCodeMirror && triggerCodeMirror('#qt_content_cred_syntax_highlight');
                    //else $('#qt_content_cred_syntax_highlight').attr('title',locale.show_syntax_highlight);

                    doinit=false;

                    $('#post').submit(function(event){
                        return pub.doCheck();
                    });
            }
    },

         post:{
            init:function(admin_url)
            {
                    var checkButtonTimer;

                    var closePopup=function(now)
                    {
                        clearInterval(checkButtonTimer);
                        if (!now)
                        setTimeout(function(){
                            /*$('#cred-form-shortcodes-box').hide();
                            $('#cred-form-shortcode-button').css('z-index',1);*/
                            cred_media_buttons.css('z-index',1);
                            cred_popup_boxes.hide();
                        },50);
                        else
                        {
                            cred_media_buttons.css('z-index',1);
                            cred_popup_boxes.hide();
                        }
                    };

                    var checkButton=function()
                    {
                        var butt=$('#cred-insert-shortcode');
                        var disable=false;
                        var tip=false;
                        var mode=$('#cred-form-shortcodes-box-inner input.cred-shortcode-container-radio:checked');

                        switch(mode.attr('id'))
                        {
                            case 'cred-post-creation-container':
                                if ($('#cred_form-new-shortcode-select').val()=='')
                                {
                                    disable=true;
                                    tip=locale.select_form;
                                }
                            break;

                            case 'cred-post-edit-container':
                                if ($('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]:checked').val()=='insert-link')
                                {
                                    $('#cred-edit-link-text-container').show();
                                }
                                else
                                {
                                    $('#cred-edit-link-text-container').hide();
                                }
                                if ($('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-other-post')
                                {
                                    $('#cred-edit-other-post-more').show();
                                }
                                else
                                {
                                    $('#cred-edit-other-post-more').hide();
                                }

                                if ($('#cred_form-edit-shortcode-select').val()=='')
                                {
                                    disable=true;
                                    tip=locale.select_form;
                                }
                                else if (
                                    $('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-other-post'
                                    &&
                                    $('#cred-edit-post-select').val()==''
                                    )
                                    {
                                        disable=true;
                                        tip=locale.select_post;
                                    }
                                /*else if (
                                    $('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-current-post'
                                    &&
                                    // post types dont match
                                    '_cred_cred_'+$('#post_type').val()!=$('#cred_form-edit-shortcode-select option:selected').attr('class')
                                    )
                                    {
                                        disable=true;
                                        tip=locale.post_types_dont_match;
                                    }*/

                            break;

                            case 'cred-post-child-link-container':
                                if ($('#cred-child-form-page').val()=='')
                                {
                                    disable=true;
                                    tip='Select a page which has child form';
                                }

                                if ($('#_cred-post-child-link-container input[name="cred-post-child-parent-action"]:checked').val()=='other'
                                    && $('#cred_post_child_parent_id').val()==''
                                )
                                {
                                    disable=true;
                                    tip='Select Parent Post';
                                }

                            break;

                            case 'cred-post-delete-link-container':
                                if (
                                    $('#cred-post-delete-link-container-advanced input[name="cred-delete-what-to-delete"]:checked').val()=='delete-other-post'
                                    &&
                                        (
                                            $('#cred_post_delete_id').val()==''
                                            ||
                                            !php.isNumber($('#cred_post_delete_id').val())
                                        )
                                    )
                                    {
                                        disable=true;
                                        tip=locale.insert_post_id;
                                    }
                            break;

                            default:
                                disable=true;
                                tip=locale.select_shortcode;
                            break;
                        }
                        // add a tip as title to insert link to notify about potential errors
                        if (tip!==false)
                            butt.attr('title',tip);
                        else
                            butt.attr('title',locale.insert_shortcode);

                        if (disable)
                            butt.attr('disabled','disabled');
                        else
                            butt.removeAttr('disabled'); // if all ok enable it
                    };

                    var checkButton2=function($parent)
                    {
                        var butt=$('.cred-insert-shortcode2',$parent);
                        var disable=false;
                        var tip=false;
                        var mode=$('input.cred-shortcode-container-radio:checked',$parent);

                            if (mode.hasClass('cred-post-creation-container2'))
                            {
                                if ($('.cred_form-new-shortcode-select2',$parent).val()=='')
                                {
                                    disable=true;
                                    tip=locale.select_form;
                                }
                            }

                            else if (mode.hasClass('cred-post-edit-container2'))
                            {
                                if ($('.cred-post-edit-container-advanced2 input[name="cred-edit-how-to-display"]:checked',$parent).val()=='insert-link')
                                {
                                    $('.cred-edit-link-text-container2',$parent).show();
                                }
                                else
                                {
                                    $('.cred-edit-link-text-container2',$parent).hide();
                                }
                                if ($('.cred-post-edit-container-advanced2 input[name="cred-edit-what-to-edit"]:checked',$parent).val()=='edit-other-post')
                                {
                                    $('.cred-edit-other-post-more2',$parent).show();
                                }
                                else
                                {
                                    $('.cred-edit-other-post-more2',$parent).hide();
                                }

                                if ($('.cred_form-edit-shortcode-select2',$parent).val()=='')
                                {
                                    disable=true;
                                    tip=locale.select_form;
                                }
                                else if (
                                    $('.cred-post-edit-container-advanced2 input[name="cred-edit-what-to-edit"]:checked',$parent).val()=='edit-other-post'
                                    &&
                                    $('.cred-edit-post-select2',$parent).val()==''
                                    )
                                    {
                                        disable=true;
                                        tip=locale.select_post;
                                    }
                                /*else if (
                                    $('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val()=='edit-current-post'
                                    &&
                                    // post types dont match
                                    '_cred_cred_'+$('#post_type').val()!=$('#cred_form-edit-shortcode-select option:selected').attr('class')
                                    )
                                    {
                                        disable=true;
                                        tip=locale.post_types_dont_match;
                                    }*/

                            }
                            else if (mode.hasClass('cred-post-child-link-container2'))
                            {
                                if ($('.cred-child-form-page2',$parent).val()=='')
                                {
                                    disable=true;
                                    tip='Select a page which has child form';
                                }

                                if ($('._cred-post-child-link-container2 input[name="cred-post-child-parent-action"]:checked',$parent).val()=='other'
                                    && $('.cred_post_child_parent_id2',$parent).val()==''
                                )
                                {
                                    disable=true;
                                    tip='Select Parent Post';
                                }

                            }

                            else if (mode.hasClass('cred-post-delete-link-container2'))
                            {
                                if (
                                    $('.cred-post-delete-link-container-advanced2 input[name="cred-delete-what-to-delete"]:checked',$parent).val()=='delete-other-post'
                                    &&
                                        (
                                            $('.cred_post_delete_id2',$parent).val()==''
                                            ||
                                            !php.isNumber($('.cred_post_delete_id2',$parent).val())
                                        )
                                    )
                                    {
                                        disable=true;
                                        tip=locale.insert_post_id;
                                    }
                            }
                            else
                            {
                                disable=true;
                                tip=locale.select_shortcode;
                            }

                        // add a tip as title to insert link to notify about potential errors
                        if (tip!==false)
                            butt.attr('title',tip);
                        else
                            butt.attr('title',locale.insert_shortcode);

                        if (disable)
                            butt.attr('disabled','disabled');
                        else
                            butt.removeAttr('disabled'); // if all ok enable it
                    };

                    var cred_media_buttons=$('.cred-media-button');
                    var cred_popup_boxes=$('.cred-popup-box');
                    var new_select_options=$('#cred_form-new-shortcode-select').find('option');
                    var edit_select_options=$('#cred_form-edit-shortcode-select').find('option');
                    var advanced_options=$('.cred-shortcodes-container-advanced');

                    // show / hide advanced options and links
                    advanced_options.each(function(){
                        $(this).hide();
                        $('.cred-show-hide-advanced',$(this).parent()).text(locale.show_advanced_options);
                    });

                    // hide loaders
                    $('.cred_ajax_loader_small').hide();

                    advanced_options.filter(function(){
                        if ($(this).hasClass('cred-show'))
                            return true;
                        return false;
                    }).each(function(){
                        $(this).show();
                        $('.cred-show-hide-advanced',$(this).parent()).text(locale.hide_advanced_options);
                    });

                    cred_popup_boxes.on('click', '.cred-show-hide-advanced', function(){
                        var adv_option=$('.cred-shortcodes-container-advanced',$(this).parent());

                        if (adv_option.hasClass('cred-show'))
                        {
                            adv_option.removeClass('cred-show');
                            adv_option.slideFadeUp('slow','quintEaseIn');
                            $(this).text(locale.show_advanced_options);
                        }
                        else
                        {
                            adv_option.addClass('cred-show');
                            adv_option.slideFadeDown('slow','quintEaseOut');
                            $(this).text(locale.hide_advanced_options);
                        }

                    });

                    $('#cred-form-shortcodes-box').on('change','#cred_form-edit-shortcode-select',function(event){
                        event.stopPropagation();
                        var form_id=$(this).val();
                        var form_name=$("option:selected",$(this)).text();
                        $('#cred-form-addtional-loader').show();
                        $.ajax({
                            url: admin_url+'/Posts/getPosts?form_id='+form_id,
                            timeout: 10000,
                            type: 'GET',
                            data: '',
                            dataType: 'html',
                            success: function(result)
                            {
                                $('#cred-edit-post-select').html(result);
                                $('#cred-form-addtional-loader').hide();
                            },
                            error: function()
                            {
                                $('#cred-form-addtional-loader').hide();
                            }
                        });
                    });

                    $('.cred-form-shortcodes-box2').on('change','.cred_form-edit-shortcode-select2',function(event){
                        event.stopPropagation();
                        var $parent=$(this).closest('.cred-form-shortcodes-box2');
                        var form_id=$(this).val();
                        var form_name=$("option:selected",$(this)).text();
                        $('.cred-form-addtional-loader2',$parent).show();
                        $.ajax({
                            url: admin_url+'/Posts/getPosts?form_id='+form_id,
                            timeout: 10000,
                            type: 'GET',
                            data: '',
                            dataType: 'html',
                            success: function(result)
                            {
                                $('.cred-edit-post-select2',$parent).html(result);
                                $('.cred-form-addtional-loader2',$parent).hide();
                            },
                            error: function()
                            {
                                $('.cred-form-addtional-loader2',$parent).hide();
                            }
                        });
                    });

                    $('#cred-child-form-page').cred_suggest(admin_url+'/Posts/suggestPostsByTitle', {
                        delay: 200,
                        minchars: 3,
                        multiple: false,
                        multipleSep: '',
                        resultsClass : 'ac_results',
                        selectClass : 'ac_over',
                        matchClass : 'ac_match',
                        onStart : function(){$('#cred-form-suggest-child-form-loader').show();},
                        onComplete : function() {$('#cred-form-suggest-child-form-loader').hide();}
                    });

                    $('.cred-child-form-page2').cred_suggest(admin_url+'/Posts/suggestPostsByTitle', {
                        delay: 200,
                        minchars: 3,
                        multiple: false,
                        multipleSep: '',
                        resultsClass : 'ac_results',
                        selectClass : 'ac_over',
                        matchClass : 'ac_match',
                        onStart : function(){$('.cred-form-suggest-child-form-loader2').show();},
                        onComplete : function() {$('.cred-form-suggest-child-form-loader2').hide();}
                    });

                    $('#cred_post_child_parent_id').cred_suggest(admin_url+'/Posts/suggestPostsByTitle' /*+ '&cred_post_type=' + $('#post_type').val()*/, {
                        delay: 200,
                        minchars: 3,
                        multiple: false,
                        multipleSep: '',
                        resultsClass : 'ac_results',
                        selectClass : 'ac_over',
                        matchClass : 'ac_match'
                    });

                    $('.cred_post_child_parent_id2').cred_suggest(admin_url+'/Posts/suggestPostsByTitle' /*+ '&cred_post_type=' + $('#post_type').val()*/, {
                        delay: 200,
                        minchars: 3,
                        multiple: false,
                        multipleSep: '',
                        resultsClass : 'ac_results',
                        selectClass : 'ac_over',
                        matchClass : 'ac_match'
                    });

                    // preselect options if only one of them
                    if (new_select_options.length==2)
                    {
                        new_select_options.eq(0).removeAttr('selected');
                        new_select_options.eq(1).attr('selected','selected');
                    }
                    if (edit_select_options.length==2)
                    {
                        edit_select_options.eq(0).removeAttr('selected');
                        edit_select_options.eq(1).attr('selected','selected');
                        edit_select_options.eq(1).closest('select').trigger('change');
                    }
                    // no new form exists
                    if (new_select_options.length==1)
                    {
                        var rel=$('#cred-form-shortcode-types-select-container #cred-post-creation-container');
                        rel.attr('disabled','disabled');
                        rel.closest('td').append('<span class="cred-warn">'+locale.create_new_content_form+'</span>');
                    }
                    // no edit form exist
                    if (edit_select_options.length==1)
                    {
                        var rel=$('#cred-form-shortcode-types-select-container #cred-post-edit-container');
                        rel.attr('disabled','disabled');
                        rel.closest('td').append('<span class="cred-warn">'+locale.create_edit_content_form+'</span>');
                    }

                    // hide shortcode details areas
                    $('.cred-shortcodes-container').hide();
                    $('#cred-form-addtional-loader').hide();
                    $('.cred-form-addtional-loader2').hide();
                    $('#cred-form-shortcode-types-select-container .cred-shortcode-container-radio').each(function(){
                        this.checked = false;
                    });


                    // hide/show areas according to
                    $('#cred-form-shortcode-types-select-container').on('change','.cred-shortcode-container-radio',function(event){
                           var el=$(this);
                           if (el.is(':disabled'))
                           {
                                return false;
                           }
                           if (el.is(':checked'))
                           {
                                $('.cred-shortcodes-container').hide();
                                $('#_'+el.attr('id')).slideFadeDown('slow','quintEaseOut');
                           }
                    });

                    // hide/show areas according to
                    $('.cred-form-shortcode-types-select-container2').on('change','.cred-shortcode-container-radio',function(event){
                           var el=$(this);
                           if (el.is(':disabled'))
                           {
                                return false;
                           }
                           if (el.is(':checked'))
                           {
                                var el_class=el.attr('class');
                                el_class=el_class.replace('cred-shortcode-container-radio','').replace('cred-radio-10','').replace(/\s+/g,'');
                                $('.cred-shortcodes-container').hide();
                                $('._'+el_class).slideFadeDown('slow','quintEaseOut');
                           }
                    });

                    $('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]').change(function(){
                        if ($(this).is(':checked') && $(this).val()=='insert-link')
                        {
                            $('#cred-edit-html-fieldset').show();
                            $('#cred-edit-html-single-fieldset').show();
                        }
                        else
                        {
                            $('#cred-edit-html-fieldset').hide();
                            $('#cred-edit-html-single-fieldset').hide();
                        }
                    });

                    $('.cred-post-edit-container-advanced2 input[name="cred-edit-how-to-display"]').change(function(){
                        var $parent=$(this).closest('.cred-form-shortcode-button2');

                        if ($(this).is(':checked') && $(this).val()=='insert-link')
                        {
                            $('.cred-edit-html-fieldset2',$parent).show();
                            $('.cred-edit-html-single-fieldset2',$parent).show();
                        }
                        else
                        {
                            $('.cred-edit-html-fieldset2',$parent).hide();
                            $('.cred-edit-html-single-fieldset2',$parent).hide();
                        }
                    });

                    // insert shortcode button handler
                    $('#cred-insert-shortcode').click(function(event){
                        event.stopPropagation();
                        event.preventDefault();

                        var form_id, form_name, post_id, shortcode, form_page_id, parent_id;

                        var el=$(this);
                        if (el.is(':disabled') || el.attr('disabled'))
                            return false;

                        var mode=$('#cred-form-shortcodes-box-inner input.cred-shortcode-container-radio:checked');

                        switch(mode.attr('id'))
                        {
                            case 'cred-post-creation-container':
                                form_id=$('#cred_form-new-shortcode-select').val();
                                form_name=$("option:selected",$('#cred_form-new-shortcode-select')).text();
                                if (!form_id) return false;
                                shortcode='[cred-form form="'+form_name+'"]';
                            break;

                            case 'cred-post-edit-container':
                                form_id=$('#cred_form-edit-shortcode-select').val();
                                form_name=$("#cred_form-edit-shortcode-select option:selected").text();
                                if (!form_id) return false;

                                //post_id=null;
                                switch($('#cred-post-edit-container-advanced input[name="cred-edit-what-to-edit"]:checked').val())
                                {
                                    case 'edit-current-post':
                                        post_id=null;
                                    break;
                                    case 'edit-other-post':
                                        post_id=$('#cred-edit-post-select').val();
                                        if (!post_id) return false;
                                    break;
                                    default: return false;
                                }
                                switch($('#cred-post-edit-container-advanced input[name="cred-edit-how-to-display"]:checked').val())
                                {
                                    case 'insert-link':
                                        var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[];
                                        _class=$('#cred-edit-html-class').val();
                                        _style=$('#cred-edit-html-style').val();
                                        _text=$('#cred-edit-html-text').val();
                                        _more_atts=$('#cred-edit-html-attributes').val();
                                        _target=$('#cred-edit-html-target').val();
                                        if (_class!='')
                                            _atts.push('class="'+_class+'"');
                                        if (_style!='')
                                            _atts.push('style="'+_style+'"');
                                        if (_text!='')
                                            _atts.push('text="'+_text+'"');
                                        if (_target!='')
                                            _atts.push('target="'+_target+'"');
                                        if (_more_atts!='')
                                            _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                                        if (_atts.length>0)
                                            _atts=' '+_atts.join(' ');
                                        else
                                            _atts='';
                                        if (post_id==null)
                                            shortcode='[cred-link-form form="'+form_name+'"'+_atts+']';
                                        else
                                            shortcode='[cred-link-form form="'+form_name+'" post="'+post_id+'"'+_atts+']';
                                    break;
                                    case 'insert-form':
                                        if (post_id==null)
                                            shortcode='[cred-form form="'+form_name+'"]';
                                        else
                                            shortcode='[cred-form form="'+form_name+'" post="'+post_id+'"]';
                                    break;
                                    default: return false;
                                }
                            break;

                            case 'cred-post-child-link-container':
                                form_page_id=$('#cred-child-form-page').val();
                                if (form_page_id=='' || isNaN(new Number(form_page_id))) return false;

                                //post_id=null;
                                switch($('#_cred-post-child-link-container input[name="cred-post-child-parent-action"]:checked').val())
                                {
                                    case 'current':
                                        parent_id=-1;
                                    break;
                                    case 'form':
                                        parent_id=null;
                                    break;
                                    case 'other':
                                        parent_id=$('#cred_post_child_parent_id').val();
                                        if (!parent_id || isNaN(new Number(parent_id))) return false;
                                    break;
                                    default: return false;
                                }
                                var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[], _post_type;
                                _class=$('#cred-child-html-class').val();
                                _style=$('#cred-child-html-style').val();
                                _text=$('#cred-child-link-text').val();
                                _more_atts=$('#cred-child-html-attributes').val();
                                _target=$('#cred-child-html-target').val();
                                //_post_type=$('#post_type').val(); // parent (current) post type
                                //_atts.push('parent_type="'+_post_type+'"');
                                if (_class!='')
                                    _atts.push('class="'+_class+'"');
                                if (_style!='')
                                    _atts.push('style="'+_style+'"');
                                if (_text!='')
                                    _atts.push('text="'+_text+'"');
                                if (_target!='')
                                    _atts.push('target="'+_target+'"');
                                if (_more_atts!='')
                                    _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                                if (_atts.length>0)
                                    _atts=' '+_atts.join(' ');
                                else
                                    _atts='';
                                if (parent_id==null)
                                    shortcode='[cred-child-link-form form="'+form_page_id+'"'+_atts+']';
                                else
                                    shortcode='[cred-child-link-form form="'+form_page_id+'" parent_id="'+parent_id+'"'+_atts+']';
                            break;

                            case 'cred-post-delete-link-container':
                                var _class='',_style='', _text='', _refresh=true, _atts=[];
                                var _action='';
                                _class=$('#cred-delete-html-class').val();
                                _style=$('#cred-delete-html-style').val();
                                _text=$('#cred-delete-html-text').val();
                                _refresh=$('#cred-refresh-after-action').is(':checked');
                                if (_refresh)
                                    _class+=(''==_class)?'cred-refresh-after-delete':' cred-refresh-after-delete';
                                _action=$('#cred-post-delete-link-container-advanced input[name="cred-delete-delete-action"]:checked').val();
                                if (_class!='')
                                    _atts.push('class="'+_class+'"');
                                if (_style!='')
                                    _atts.push('style="'+_style+'"');
                                if (_text!='')
                                    _atts.push('text="'+_text+'"');
                                if (_action!='')
                                    _atts.push('action="'+_action+'"');
                                if (_atts.length>0)
                                    _atts=' '+_atts.join(' ');
                                else
                                    _atts='';
                                if ($('#cred-post-delete-link-container-advanced input[name="cred-delete-what-to-delete"]:checked').val()=='delete-other-post')
                                {
                                    post_id=$('#cred_post_delete_id').val();
                                    shortcode='[cred-delete-post-link post="'+post_id+'"'+_atts+']';
                                }
                                else
                                {
                                    shortcode='[cred-delete-post-link'+_atts+']';
                                }
                            break;

                            default: return false; break;
                        }
                        if (shortcode && shortcode!='')
                        {
                            aux.InsertAtCursor($('#content'),shortcode);
                            closePopup();
                        }
                    });

                    $('.cred-insert-shortcode2').click(function(event){
                        event.stopPropagation();
                        event.preventDefault();

                        var form_id, form_name, post_id, shortcode, form_page_id, parent_id;

                        var el=$(this);
                        if (el.is(':disabled') || el.attr('disabled'))
                            return false;

                        var content=$(el.attr('data-content'));
                        var $parent=el.closest('.cred-form-shortcodes-box-inner2');
                        var mode=$('input.cred-shortcode-container-radio:checked',$parent);

                            if (mode.hasClass('cred-post-creation-container2'))
                            {
                                form_id=$('.cred_form-new-shortcode-select2',$parent).val();
                                form_name=$(".cred_form-new-shortcode-select2 option:selected",$parent).text();
                                if (!form_id) return false;
                                shortcode='[cred-form form="'+form_name+'"]';
                            }
                            else if (mode.hasClass('cred-post-edit-container2'))
                            {
                                form_id=$('.cred_form-edit-shortcode-select2',$parent).val();
                                form_name=$(".cred_form-edit-shortcode-select2 option:selected",$parent).text();
                                if (!form_id) return false;

                                //post_id=null;
                                switch($('.cred-post-edit-container-advanced2 input[name="cred-edit-what-to-edit"]:checked',$parent).val())
                                {
                                    case 'edit-current-post':
                                        post_id=null;
                                    break;
                                    case 'edit-other-post':
                                        post_id=$('.cred-edit-post-select2',$parent).val();
                                        if (!post_id) return false;
                                    break;
                                    default: return false;
                                }
                                switch($('.cred-post-edit-container-advanced2 input[name="cred-edit-how-to-display"]:checked',$parent).val())
                                {
                                    case 'insert-link':
                                        var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[];
                                        _class=$('.cred-edit-html-class2',$parent).val();
                                        _style=$('.cred-edit-html-style2',$parent).val();
                                        _text=$('.cred-edit-html-text2',$parent).val();
                                        _more_atts=$('.cred-edit-html-attributes2',$parent).val();
                                        _target=$('.cred-edit-html-target2',$parent).val();
                                        if (_class!='')
                                            _atts.push('class="'+_class+'"');
                                        if (_style!='')
                                            _atts.push('style="'+_style+'"');
                                        if (_text!='')
                                            _atts.push('text="'+_text+'"');
                                        if (_target!='')
                                            _atts.push('target="'+_target+'"');
                                        if (_more_atts!='')
                                            _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                                        if (_atts.length>0)
                                            _atts=' '+_atts.join(' ');
                                        else
                                            _atts='';
                                        if (post_id==null)
                                            shortcode='[cred-link-form form="'+form_name+'"'+_atts+']';
                                        else
                                            shortcode='[cred-link-form form="'+form_name+'" post="'+post_id+'"'+_atts+']';
                                    break;
                                    case 'insert-form':
                                        if (post_id==null)
                                            shortcode='[cred-form form="'+form_name+'"]';
                                        else
                                            shortcode='[cred-form form="'+form_name+'" post="'+post_id+'"]';
                                    break;
                                    default: return false;
                                }
                            }
                            else if (mode.hasClass('cred-post-child-link-container2'))
                            {
                                form_page_id=$('.cred-child-form-page2',$parent).val();
                                if (form_page_id=='' || isNaN(new Number(form_page_id))) return false;

                                //post_id=null;
                                switch($('._cred-post-child-link-container2 input[name="cred-post-child-parent-action"]:checked',$parent).val())
                                {
                                    case 'current':
                                        parent_id=-1;
                                    break;
                                    case 'form':
                                        parent_id=null;
                                    break;
                                    case 'other':
                                        parent_id=$('.cred_post_child_parent_id2',$parent).val();
                                        if (!parent_id || isNaN(new Number(parent_id))) return false;
                                    break;
                                    default: return false;
                                }
                                var _class='',_target='_self',_style='', _text='', _more_atts='', _atts=[], _post_type;
                                _class=$('.cred-child-html-class2',$parent).val();
                                _style=$('.cred-child-html-style2',$parent).val();
                                _text=$('.cred-child-link-text2',$parent).val();
                                _more_atts=$('.cred-child-html-attributes2',$parent).val();
                                _target=$('.cred-child-html-target2',$parent).val();
                                //_post_type=$('#post_type').val(); // parent (current) post type
                                //_atts.push('parent_type="'+_post_type+'"');
                                if (_class!='')
                                    _atts.push('class="'+_class+'"');
                                if (_style!='')
                                    _atts.push('style="'+_style+'"');
                                if (_text!='')
                                    _atts.push('text="'+_text+'"');
                                if (_target!='')
                                    _atts.push('target="'+_target+'"');
                                if (_more_atts!='')
                                    _atts.push('attributes="'+_more_atts.split('"').join("%dbquo%").split("'").join("%quot%").split('=').join('%eq%')+'"');
                                if (_atts.length>0)
                                    _atts=' '+_atts.join(' ');
                                else
                                    _atts='';
                                if (parent_id==null)
                                    shortcode='[cred-child-link-form form="'+form_page_id+'"'+_atts+']';
                                else
                                    shortcode='[cred-child-link-form form="'+form_page_id+'" parent_id="'+parent_id+'"'+_atts+']';
                            }
                            else if (mode.hasClass('cred-post-delete-link-container2'))
                            {
                                var _class='',_style='', _text='', _refresh=true, _atts=[];
                                var _action='';
                                _class=$('.cred-delete-html-class2',$parent).val();
                                _style=$('.cred-delete-html-style2',$parent).val();
                                _text=$('.cred-delete-html-text2',$parent).val();
                                _refresh=$('.cred-refresh-after-action',$parent).is(':checked');
                                if (_refresh)
                                    _class+=(''==_class)?'cred-refresh-after-delete':' cred-refresh-after-delete';
                                _action=$('.cred-post-delete-link-container-advanced2 input[name="cred-delete-delete-action"]:checked',$parent).val();
                                if (_class!='')
                                    _atts.push('class="'+_class+'"');
                                if (_style!='')
                                    _atts.push('style="'+_style+'"');
                                if (_text!='')
                                    _atts.push('text="'+_text+'"');
                                if (_action!='')
                                    _atts.push('action="'+_action+'"');
                                if (_atts.length>0)
                                    _atts=' '+_atts.join(' ');
                                else
                                    _atts='';

                                if ($('.cred-post-delete-link-container-advanced2 input[name="cred-delete-what-to-delete"]:checked',$parent).val()=='delete-other-post')
                                {
                                    post_id=$('.cred_post_delete_id',$parent).val();
                                    shortcode='[cred-delete-post-link post="'+post_id+'"'+_atts+']';
                                }
                                else
                                {
                                    shortcode='[cred-delete-post-link'+_atts+']';
                                }
                            }
                            else
                                return false;
                        if (shortcode && shortcode!='')
                        {
                            aux.InsertAtCursor(content,shortcode);
                            closePopup();
                        }
                    });

                    $('#post-body').on('click','#cred-form-shortcode-button-button',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();

                        $(this).closest('.cred-media-button').css('z-index',100);
                        $('#cred-form-shortcodes-box').__show();

                        checkButton();
                        checkButtonTimer=setInterval(function(){
                            checkButton();
                        },500);
                    });

                    $('#post-body').on('click','.cred-form-shortcode-button-button2',function(event){
                        event.stopPropagation();
                        event.preventDefault();
                        cred_media_buttons.css('z-index',1);
                        cred_popup_boxes.hide();

                        $(this).closest('.cred-media-button').css('z-index',100);
                        $('.cred-form-shortcodes-box2',$(this).closest('.cred-media-button')).__show();

                        var $parent=$(this).closest('.cred-form-shortcode-button2');
                        checkButton2($parent);
                        checkButtonTimer=setInterval(function(){
                            checkButton2($parent);
                        },500);
                    });

                    // popup open/close handlers
                    /*cred_popup_boxes.click(function(event){
                        event.stopPropagation();
                    });*/

                    $('#cred-form-shortcodes-box').on('click','#cred-popup-cancel',function(event){
                        event.preventDefault();
                        closePopup();
                    });

                    $('.cred-form-shortcodes-box2').on('click','.cred-popup-cancel2',function(event){
                        event.preventDefault();
                        closePopup();
                    });

                    //$('html').click(function(){
                    $(document).click/*mouseup*/(function (e){
                        if (
                            !e._cred_specific &&
                            cred_popup_boxes.filter(function(){
                                return $(this).is(':visible');
                                }).has(e.target).length === 0
                            )
                        {
                            closePopup(true);
                        }
                    });

                    $(document).keyup(function(e) {
                        if (e.keyCode == KEYCODE_ESC)
                        {
                            closePopup();
                        }
                    });

                    // cancel buttons
                    $('#post-body').on('click','.cred-cred-cancel-close',function(event){
                            closePopup();
                    });
            }
         }
     }
     // make public methods/properties available
     return pub;
}(jQuery, cred_cred_config, cred_cred_help);
// animation easing functions
;jQuery.extend(jQuery.easing,{linear:function(a,b,c,d){return c+d*a},backEaseIn:function(a,b,c,d){var e=c+d,f=1.70158;return e*(a/=1)*a*((f+1)*a-f)+c},backEaseOut:function(a,b,c,d){var e=c+d,f=1.70158;return e*((a=a/1-1)*a*((f+1)*a+f)+1)+c},backEaseInOut:function(a,b,c,d){var e=c+d,f=1.70158;return(a/=.5)<1?e/2*a*a*(((f*=1.525)+1)*a-f)+c:e/2*((a-=2)*a*(((f*=1.525)+1)*a+f)+2)+c},bounceEaseIn:function(a,b,c,d){var e=c+d,f=this.bounceEaseOut(1-a,1,0,d);return e-f+c},bounceEaseOut:function(a,b,c,d){var e=c+d;return a<1/2.75?e*7.5625*a*a+c:a<2/2.75?e*(7.5625*(a-=1.5/2.75)*a+.75)+c:a<2.5/2.75?e*(7.5625*(a-=2.25/2.75)*a+.9375)+c:e*(7.5625*(a-=2.625/2.75)*a+.984375)+c},circEaseIn:function(a,b,c,d){var e=c+d;return-e*(Math.sqrt(1-(a/=1)*a)-1)+c},circEaseOut:function(a,b,c,d){var e=c+d;return e*Math.sqrt(1-(a=a/1-1)*a)+c},circEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?-e/2*(Math.sqrt(1-a*a)-1)+c:e/2*(Math.sqrt(1-(a-=2)*a)+1)+c},cubicEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a+c},cubicEaseOut:function(a,b,c,d){var e=c+d;return e*((a=a/1-1)*a*a+1)+c},cubicEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a+c:e/2*((a-=2)*a*a+2)+c},elasticEaseIn:function(a,b,c,d){var e=c+d;if(a==0)return c;if(a==1)return e;var f=.25,g,h=e;return h<Math.abs(e)?(h=e,g=f/4):g=f/(2*Math.PI)*Math.asin(e/h),-(h*Math.pow(2,10*(a-=1))*Math.sin((a*1-g)*2*Math.PI/f))+c},elasticEaseOut:function(a,b,c,d){var e=c+d;if(a==0)return c;if(a==1)return e;var f=.25,g,h=e;return h<Math.abs(e)?(h=e,g=f/4):g=f/(2*Math.PI)*Math.asin(e/h),-(h*Math.pow(2,-10*a)*Math.sin((a*1-g)*2*Math.PI/f))+e},expoEaseIn:function(a,b,c,d){var e=c+d;return a==0?c:e*Math.pow(2,10*(a-1))+c-e*.001},expoEaseOut:function(a,b,c,d){var e=c+d;return a==1?e:d*1.001*(-Math.pow(2,-10*a)+1)+c},expoEaseInOut:function(a,b,c,d){var e=c+d;return a==0?c:a==1?e:(a/=.5)<1?e/2*Math.pow(2,10*(a-1))+c-e*5e-4:e/2*1.0005*(-Math.pow(2,-10*--a)+2)+c},quadEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a+c},quadEaseOut:function(a,b,c,d){var e=c+d;return-e*(a/=1)*(a-2)+c},quadEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a+c:-e/2*(--a*(a-2)-1)+c},quartEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a*a+c},quartEaseOut:function(a,b,c,d){var e=c+d;return-e*((a=a/1-1)*a*a*a-1)+c},quartEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a*a+c:-e/2*((a-=2)*a*a*a-2)+c},quintEaseIn:function(a,b,c,d){var e=c+d;return e*(a/=1)*a*a*a*a+c},quintEaseOut:function(a,b,c,d){var e=c+d;return e*((a=a/1-1)*a*a*a*a+1)+c},quintEaseInOut:function(a,b,c,d){var e=c+d;return(a/=.5)<1?e/2*a*a*a*a*a+c:e/2*((a-=2)*a*a*a*a+2)+c},sineEaseIn:function(a,b,c,d){var e=c+d;return-e*Math.cos(a*(Math.PI/2))+e+c},sineEaseOut:function(a,b,c,d){var e=c+d;return e*Math.sin(a*(Math.PI/2))+c},sineEaseInOut:function(a,b,c,d){var e=c+d;return-e/2*(Math.cos(Math.PI*a)-1)+c}})

/*
 *	jquery.suggest 1.1b - 2007-08-06
 * Patched by Mark Jaquith with Alexander Dick's "multiple items" patch to allow for auto-suggesting of more than one tag before submitting
 * See: http://www.vulgarisoip.com/2007/06/29/jquerysuggest-an-alternative-jquery-based-autocomplete-library/#comment-7228
 *
 *	Uses code and techniques from following libraries:
 *	1. http://www.dyve.net/jquery/?autocomplete
 *	2. http://dev.jquery.com/browser/trunk/plugins/interface/iautocompleter.js
 *
 *	All the new stuff written by Peter Vulgaris (www.vulgarisoip.com)
 *	Feel free to do whatever you want with this file
 *
 */

;(function($) {

	$.cred_suggest = function(input, options) {
		var $input, $results, timeout, prevLength, cache, cacheSize;

		$input = $(input).attr("autocomplete", "off");
		$results = $(document.createElement("ul"));

		timeout = false;		// hold timeout ID for suggestion results to appear
		prevLength = 0;			// last recorded length of $input.val()
		cache = [];				// cache MRU list
		cacheSize = 0;			// size of cache in chars (bytes?)

		$results.addClass(options.resultsClass).appendTo('body');


		resetPosition();
		$(window)
			.load(resetPosition)		// just in case user is changing size of page while loading
			.resize(resetPosition);

		$input.blur(function() {
			setTimeout(function() { $results.hide() }, 200);
		});


		// help IE users if possible
		if ( $.browser.msie ) {
			try {
				$results.bgiframe();
			} catch(e) { }
		}

		// I really hate browser detection, but I don't see any other way
		if ($.browser.mozilla)
			$input.keypress(processKey);	// onkeypress repeats arrow keys in Mozilla/Opera
		else
			$input.keydown(processKey);		// onkeydown repeats arrow keys in IE/Safari




		function resetPosition() {
			// requires jquery.dimension plugin
			var offset = $input.offset();
			$results.css({
				top: (offset.top + input.offsetHeight) + 'px',
				left: offset.left + 'px'
			});
		}


		function processKey(e) {

			// handling up/down/escape requires results to be visible
			// handling enter/tab requires that AND a result to be selected
			if ((/27$|38$|40$/.test(e.keyCode) && $results.is(':visible')) ||
				(/^13$|^9$/.test(e.keyCode) && getCurrentResult())) {

				if (e.preventDefault)
					e.preventDefault();
				if (e.stopPropagation)
					e.stopPropagation();

				e.cancelBubble = true;
				e.returnValue = false;

				switch(e.keyCode) {

					case 38: // up
						prevResult();
						break;

					case 40: // down
						nextResult();
						break;

					case 9:  // tab
					case 13: // return
						selectCurrentResult();
						break;

					case 27: //	escape
						$results.hide();
						break;

				}

			} else if ($input.val().length != prevLength) {

				if (timeout)
					clearTimeout(timeout);
				timeout = setTimeout(suggest, options.delay);
				prevLength = $input.val().length;

			}


		}


		function suggest() {

			var q = $.trim($input.val()), multipleSepPos, items;

			if ( options.multiple ) {
				multipleSepPos = q.lastIndexOf(options.multipleSep);
				if ( multipleSepPos != -1 ) {
					q = $.trim(q.substr(multipleSepPos + options.multipleSep.length));
				}
			}
			if (q.length >= options.minchars) {

				cached = checkCache(q);

				if (cached) {

					displayItems(cached['items']);

				} else {

					if (options.onStart)
                    {
                        options.onStart.call(this);
                    }
                    $.get(options.source, {q: q}, function(txt) {

						$results.hide();

						items = parseTxt(txt, q);

						displayItems(items);
						addToCache(q, items, items.length /*txt.length*/);

                        if (options.onComplete)
                        {
                            options.onComplete.call(this);
                        }

					});

				}

			} else {

				$results.hide();

			}

		}


		function checkCache(q) {
			var i;
			for (i = 0; i < cache.length; i++)
				if (cache[i]['q'] == q) {
					cache.unshift(cache.splice(i, 1)[0]);
					return cache[0];
				}

			return false;

		}

		function addToCache(q, items, size) {
			var cached;
			while (cache.length && (cacheSize + size > options.maxCacheSize)) {
				cached = cache.pop();
				cacheSize -= cached['size'];
			}

			cache.push({
				q: q,
				size: size,
				items: items
				});

			cacheSize += size;

		}

		function displayItems(items) {
			var html = '', i;
			if (!items)
				return;

			if (!items.length) {
				$results.hide();
				return;
			}

			resetPosition(); // when the form moves after the page has loaded

			for (i = 0; i < items.length; i++)
				html += '<li data-val="'+items[i].val+'">' + items[i].display + '</li>';

			$results.html(html).show();

			$results
				.children('li')
				.mouseover(function() {
					$results.children('li').removeClass(options.selectClass);
					$(this).addClass(options.selectClass);
				})
				.click(function(e) {
					e.preventDefault();
					e.stopPropagation();
                    e._cred_specific=true;
					selectCurrentResult();
				});

		}

		function parseTxt(txt, q) {

			var items = [];//, tokens = txt.split(options.delimiter), i, token;
            /*
			// parse returned data for non-empty items
			for (i = 0; i < tokens.length; i++) {
				token = $.trim(tokens[i]);
				if (token) {
					token = token.replace(
						new RegExp(q, 'ig'),
						function(q) { return '<span class="' + options.matchClass + '">' + q + '</span>' }
						);
					items[items.length] = token;
				}
			}
            */
            items=$.parseJSON(txt);
			return items;
		}

		function getCurrentResult() {
			var $currentResult;
			if (!$results.is(':visible'))
				return false;

			$currentResult = $results.children('li.' + options.selectClass);

			if (!$currentResult.length)
				$currentResult = false;

			return $currentResult;

		}

		function selectCurrentResult() {

			$currentResult = getCurrentResult();

			if ($currentResult) {
				if ( options.multiple ) {
					if ( $input.val().indexOf(options.multipleSep) != -1 ) {
						$currentVal = $input.val().substr( 0, ( $input.val().lastIndexOf(options.multipleSep) + options.multipleSep.length ) );
					} else {
						$currentVal = "";
					}
					$input.val( $currentVal + $currentResult.attr('data-val') + options.multipleSep);
					$input.focus();
				} else {
					$input.val($currentResult.attr('data-val'));
					$input.focus();
				}
				$results.hide();

				if (options.onSelect)
					options.onSelect.apply($input[0]);

			}

		}

		function nextResult() {

			$currentResult = getCurrentResult();

			if ($currentResult)
				$currentResult
					.removeClass(options.selectClass)
					.next()
						.addClass(options.selectClass);
			else
				$results.children('li:first-child').addClass(options.selectClass);

		}

		function prevResult() {
			var $currentResult = getCurrentResult();

			if ($currentResult)
				$currentResult
					.removeClass(options.selectClass)
					.prev()
						.addClass(options.selectClass);
			else
				$results.children('li:last-child').addClass(options.selectClass);

		}
	}

	$.fn.cred_suggest = function(source, options) {

		if (!source)
			return;

		options = options || {};
		options.multiple = options.multiple || false;
		options.multipleSep = options.multipleSep || ", ";
		options.source = source;
		options.delay = options.delay || 100;
		options.resultsClass = options.resultsClass || 'ac_results';
		options.selectClass = options.selectClass || 'ac_over';
		options.matchClass = options.matchClass || 'ac_match';
		options.minchars = options.minchars || 2;
		options.delimiter = options.delimiter || '\n';
		options.onSelect = options.onSelect || false;
		options.onStart = options.onStart || false;
		options.onComplete = options.onComplete || false;
		options.maxCacheSize = options.maxCacheSize || 65536;

		this.each(function() {
			new $.cred_suggest(this, options);
		});

		return this;

	};

})(jQuery);