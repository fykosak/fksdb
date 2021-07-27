import * as React from 'react';
import SingleSelect from './SingleSelect';

interface OwnProps {
    mode?: string;
}

export default class Index extends React.Component<OwnProps, {}> {

    public render() {
       // const {mode} = this.props;
        /*  if (mode === 'presentation') {
              return <MultiSelect/>;
          }*/
        return <SingleSelect/>;
    }
}
