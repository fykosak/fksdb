import * as React from 'react';
import * as ReactDOM from 'react-dom';

import App from './components/app';
import TaskCode from './taskCode';

$('.fyziklani-results').parent('.container').css({width: 'inherit'});
if (document.getElementsByClassName('fyziklani-results').length) {
    ReactDOM.render(<App/>, document.getElementsByClassName('fyziklani-results')[0]);
}


jQuery('#taskcode').each(
    (a, input: HTMLInputElement) => {
        let $ = jQuery;
        if (!input.value) {
            const c = document.createElement('div');
            const tasks = $(input).data('tasks');
            const teams = $(input).data('teams');
            $(input).parent().parent().append(c);
            $(input).parent().hide();
            $(c).addClass('col-lg-6');
            ReactDOM.render(<TaskCode node={input} tasks={tasks} teams={teams}/>, c);
        }
    }
);
