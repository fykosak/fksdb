import '../../../../www/js/codemirror.min.js';
import './sqlConsole.scss';

$(() => {
    document.querySelectorAll('.sqlConsole').forEach((el) => {
        // @ts-ignore
        CodeMirror.fromTextArea(el,
            {
                mode: 'text/x-mysql',
                indentWithTabs: true,
                smartIndent: true,
                lineNumbers: true,
                matchBrackets: true,
                autofocus: true,
            });
    });
    document.querySelectorAll('.syntax-sql').forEach((el) => {
        const code = el.innerHTML;
        el.innerHTML = '';
// @ts-ignore
        CodeMirror(el, {
            value: code,
            mode: 'text/x-mysql',
            lineNumbers: true,
            readOnly: true,
            indentWithTabs: true,
            smartIndent: true,
            matchBrackets: true,
            autofocus: true,
        });
    });
});
