import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import TaskCodeApp from './submitForm/components/';

export const fyziklani = () => {

    mapRegister.register('fyziklani.submit-form', (element, reactId, rawData, actions) => {
        const c = document.createElement('div');
        const {tasks, teams, availablePoints} = JSON.parse(rawData);
        element.appendChild(c);
        ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions}
                                     availablePoints={availablePoints}/>, c);
    });
    /*  mapRegister.register('fyziklani.routing', (element, reactId, rawData, actions) => {
          const wrap = document.querySelector('#wrap > .container');
          if (wrap) {
              wrap.className = wrap.className.split(' ').reduce((className, name) => {
                  if (name === 'container') {
                      return className + ' container-fluid';
                  }
                  return className + ' ' + name;
              }, '');
          }
          const data = JSON.parse(rawData);
          ReactDOM.render(<Routing teams={data.teams} rooms={data.rooms}/>, element);
      });*/

};
