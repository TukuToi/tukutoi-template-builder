var TKT_GLOBAL_NAMESPACE = {};
(function( $ ) {
	'use strict';

    jQuery(document).ready(function($) {

        /**
         * Start Register a Custom Mode for CodeMirror.
         * 
         * Start Massively inspired by Toolset View.
         */
	    var codemirror_html = document.getElementById('content');

	    wp.CodeMirror.defineMode("tkt_shortcodes", function(config, parserConfig) {

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
                    if (type == "endShortcode") {
                        popContext();
                        return cont();
                    }
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
                if (type == "word") {
                    setStyle = "attribute";
                    return cont(attribute, attributes);
                }
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
                if (type == "word" && Kludges.allowUnquoted) {
                    setStyle = "string";
                    return cont();
                }
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
                        return {
                            tokenize: inText,
                            cc: [],
                            indented: 0,
                            startOfLine: true,
                            tagName: null,
                            shortcodeName: null,
                            context: null
                        };
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
            return wp.CodeMirror.overlayMode(wp.CodeMirror.getMode(config, parserConfig.backdrop || "htmlmixed"), shortcodesOverlay);
        });
        /**
         * End Register a Custom Mode for CodeMirror.
         * 
         * End Massively inspired by Toolset View.
         */
    
        /**
         * Instantiate a Global CodeMirror instance so other TukuToi Plugins can listen to it
         */
	    TKT_GLOBAL_NAMESPACE.codemirror = wp.CodeMirror.fromTextArea( codemirror_html, {
    		mode: 'tkt_shortcodes',
            foldGutter: true,
            lineNumbers: true, 
            styleActiveLine: true,
            gutters: [
                "CodeMirror-lint-markers",
                "CodeMirror-linenumbers",
                "CodeMirror-foldgutter"
            ],
            lint: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            matchBrackets: true,
            matchTags:{
                bothTags:true
            },
            extraKeys: {
                "Alt-F": "findPersistent"
            },
            //viewportMargin: Infinity,
            placeholder: "Write your HTML Code for the Template here. The Editor of course accepts as well ShortCodes. CSS and JS can be added within style and script tags, however it is suggested to either use Theme Style/Scripts or Customizer, or the specific CSS and JS editors below under the main editor.",
            //highlightSelectionMatches: {showToken: /\w/, annotateScrollbar: true},
            lineWrapping: true,
	    });

        /**
         * Add the only really useful WordPress Quicktag back to the editor.
         * Adapted so it inserts to the CodeMirror.
         */
        $('#wp-link-submit').on('click', function(){
            var inputstext = $( '#wp-link-text' );
            var attrs = wpLink.getAttrs();
            var text = inputstext.val();
            var html = wpLink.buildHtml(attrs);
            html = html + text + '</a>';
            var codemirror = TKT_GLOBAL_NAMESPACE.codemirror.getDoc();
            codemirror.replaceSelection(html);
        });
        
        /**
         * An example of adding a Custom Quicktag
         */
        // QTags.addButton( 
        //     'tkt_quicktag_id', 
        //     'TKT QuickTag Label', 
        //     tkt_quicktag_callback
        // );
 
        // function tkt_quicktag_callback() {
        //     var an_alert = prompt( 'Enter a class name:', '' );
             
        //     if ( an_alert ) {
        //         var codemirror = TKT_GLOBAL_NAMESPACE.codemirror.getDoc();
        //         codemirror.replaceSelection('<div>' + an_alert + '</div>');
        //     }
        // }

        /**
         * Instantiate Select2 on the Admin side 
         * 
         * #tkt_template_assigned_to
         * The "Template usage" > "use this template as..." Selector.
         * Conditionally to its selected values we show #tkt_taxonomy_type or #tkt_post_type
         * 
         * To shw/hide a Select2 element conditionally, said element must be in a DIV with class "tkt_conditional_select2"
         * It also needs class(es) matching the value of #tkt_template_assigned_to options which should show it.
         * 
         * #tkt_post_type
         * The "Template usage" > "use for these post types..." Selector.
         * Shows only if #tkt_template_assigned_to has "Single Posts/Pages/CPT" or "Post Type Archive" selected.
         * Lets users choose what Single or Archive (Post) Type to target.
         * 
         * NOTE: We switched to use one Select for all templates, just keeping this code for reference.
         */
        // Initiate the Multiple Select2 instances By ID with placeholder.
        $( "#tkt_template_assigned_to" ).select2({
            placeholder: 'Choose from Locations',
            width: '100%',
        });
        // $( "#tkt_post_type" ).select2({
        //     placeholder: 'Choose from Post Types',
        //     width: '100%',
        // });
        // $( "#tkt_taxonomy_type" ).select2({
        //     placeholder: 'Choose from Custom Taxonomies',
        //     width: '100%',
        // });
        $( "#tkt_content_template_assigned_to" ).select2({
            placeholder: 'Choose from Post Types',
            width: '100%',
        });
        // Initiate the Single Select2 instances by Class, no placeholder.
        $( ".tkt_template_select" ).select2({
            width: '100%',
        });
        // // On Load check if the hidden Select2 should be shown.
        // $.each( $('#tkt_template_assigned_to').find(':selected'), function( key, value ) {
        //     $( '.' + $(value).val() ).show();
        // });
        // // On Selection, check what hidden Select2 to show.
        // $('#tkt_template_assigned_to').on("select2:select", function(e) { 
        //     $( '.tkt_conditional_select2' ).hide();
        //     $.each( $(this).select2('data'), function( key, value ){
        //         $( '.' + value.id ).show();
        //     } );
           
        // });
        // // On deselection, check what hidden Select2 to show or hide. Also clear all selections if select2 is hidden.
        // $('#tkt_template_assigned_to').on("select2:unselect", function(e) { 
        //     $( '.tkt_conditional_select2' ).hide();
        //     $.each( $(this).select2('data'), function( key, value ){
        //         $( '.' + value.id ).show();
        //     } );   
        //     if ( !$('.' + e.params.data.id).is(":visible") ){
        //         $.each( $('.' + e.params.data.id + ' > select'), function( k, v ){
        //             $('#' + $(v).attr('id') ).val(null).trigger('change');
        //         });
        //     }        
        // });

        /**
         * Provide a "Copy ShortCode" button
         */
        $('#tkt_copy_template_shortcode').on('click', function(e){
            e.preventDefault();
            copy_shortcode();
        })
        function copy_shortcode() {
            var shortcode = document.getElementById("tkt_template_shortcode");
            var temp_textarea = document.createElement("textarea");
            temp_textarea.value = shortcode.textContent;
            document.body.appendChild(temp_textarea);
            temp_textarea.select();
            document.execCommand("copy");
            temp_textarea.remove();
            var icon = $('#tkt_copy_template_shortcode').html();
            $('#tkt_copy_template_shortcode').html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M13.485 1.431a1.473 1.473 0 0 1 2.104 2.062l-7.84 9.801a1.473 1.473 0 0 1-2.12.04L.431 8.138a1.473 1.473 0 0 1 2.084-2.083l4.111 4.112 6.82-8.69a.486.486 0 0 1 .04-.045z"/>')
            setTimeout(function() { 
                $('#tkt_copy_template_shortcode').html(icon);
            }, 1600);
        }

    })

})( jQuery );