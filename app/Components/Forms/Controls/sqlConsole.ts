import * as CodeMirror from 'codemirror';
import { fromTextArea } from 'codemirror';

window.onload = () => {
    document.querySelectorAll('.sql-console').forEach((el: HTMLTextAreaElement) => {
        fromTextArea(el,
            {
                mode: 'text/x-mariadb',
                indentWithTabs: true,
                smartIndent: true,
                lineNumbers: true,
                autofocus: true,
            });
    });
    document.querySelectorAll('.syntax-sql').forEach((el) => {
        const code = el.innerHTML;
        el.innerHTML = '';
        CodeMirror(el, {
            value: code,
            mode: 'text/x-mariadb',
            lineNumbers: true,
            readOnly: true,
            indentWithTabs: true,
            smartIndent: true,
            autofocus: true,
        });
    });
};
