$(function() {
    $('.sqlConsole').each(function(index, el) {
        var codeMirror = CodeMirror.fromTextArea(el,
                {
                    mode: 'text/x-mysql',
                    indentWithTabs: true,
                    smartIndent: true,
                    lineNumbers: true,
                    matchBrackets: true,
                    autofocus: true
                });
    });
    $('.syntax-sql').each(function(index, el) {
        $el = $(el);
        var code = $el.html();
        $el.empty();

        CodeMirror(el, {
            value: code,
            mode: 'text/x-mysql',
            lineNumbers: !$el.is('.inline'),
            readOnly: true,
                                indentWithTabs: true,
                    smartIndent: true,
                    matchBrackets: true,
                    autofocus: true
        });
        
    });
});

