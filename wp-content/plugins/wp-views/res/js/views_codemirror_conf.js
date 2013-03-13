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
                        return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), shortcodesOverlay);
                    });
                    
            var HTMLEditor =[];
            var HTMLCodeMirrorActive = true;
            
            function InsertAtCursor(shortcode, text_area){
                var textarea_marker =  text_area;
                
                if(typeof HTMLEditor[textarea_marker] === "undefined"){return;}
                
                if(HTMLEditor[textarea_marker].somethingSelected()){
                    HTMLEditor[textarea_marker].replaceSelection(shortcode);
                }else{
                    var current_code = HTMLEditor[textarea_marker].getValue();
                    var cursor = HTMLEditor[textarea_marker].getCursor();
                    var lines = current_code.split('\n');
                    var result_code = '';
                    
                    var length = lines.length;
                    var s_line = null;
                    for (var i = 0; i < length; i++) {
                      if(cursor.line===i){
                          s_line = lines[i].substr(0,cursor.ch)+shortcode+lines[i].substr(cursor.ch);
                      }else{
                          s_line = lines[i];
                      }
                      if (i < length - 1) { // don't add to last line
                        s_line += "\n"; 
                      }
                      result_code = result_code+s_line;
                    }
                    HTMLEditor[textarea_marker].setValue(result_code);
                    cursor.ch=cursor.ch+shortcode.length;
                    HTMLEditor[textarea_marker].setCursor(cursor);
                    HTMLEditor[textarea_marker].refresh();
                    HTMLEditor[textarea_marker].focus();
                }
            }