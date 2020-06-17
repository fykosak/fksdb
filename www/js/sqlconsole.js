$(() => {
    document.querySelectorAll('.sqlConsole').forEach((el) => {
        CodeMirror.fromTextArea(el,
            {
                mode: 'text/x-mysql',
                indentWithTabs: true,
                smartIndent: true,
                lineNumbers: true,
                matchBrackets: true,
                autofocus: true
            });
    });
    document.querySelectorAll('.syntax-sql').forEach((el) => {
        const code = el.innerHTML;
        el.innerHTML = '';

        CodeMirror(el, {
            value: code,
            mode: 'text/x-mysql',
            lineNumbers: true,
            readOnly: true,
            indentWithTabs: true,
            smartIndent: true,
            matchBrackets: true,
            autofocus: true
        });
    });
});

