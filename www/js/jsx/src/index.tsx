import * as React from 'react';
import * as ReactDOM from 'react-dom';

import App from './components/app';
//import TaskCode from './taskCode';

if (!Object.values) {
    Object.values = (obj)=> {
        var vals = [];
        for (let key in obj) {
            if (obj.hasOwnProperty(key)) {
                vals.push(obj[key]);
            }
        }
        return vals;
    }
}

$('.fyziklani-results').parent('.container').css({width: 'inherit'});

ReactDOM.render(<App/>, document.getElementsByClassName('fyziklani-results')[0]);

/*
jQuery('#taskcode').each(
    (a, input) => {
        let $ = jQuery;
        if (!input.value) {
            let c = document.createElement('div');
            let tasks = $(input).data('tasks');
            let teams = $(input).data('teams');
            $(input).parent().parent().append(c);
            $(input).parent().hide();
            $(c).addClass('col-lg-6');
            ReactDOM.render(<TaskCode node={input} tasks={tasks} teams={teams}/>, c);
        }
    }
);*/
