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
});

