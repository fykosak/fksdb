import * as React from 'react';

export default class Loading extends React.Component<{}, {}> {
    public render() {
        return (
            <div className="load" style={{textAlign: 'center'}}>
                <img src={'/images/gears.svg'} style={{width: '50%'}}/>
            </div>);
    }
}
