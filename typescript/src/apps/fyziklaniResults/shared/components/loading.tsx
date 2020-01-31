import * as React from 'react';

export default class Loading extends React.Component<{}, {}> {
    public render() {
        return (
            <div className="load" style={{textAlign: 'center'}}>
                <img alt="logo" src="/images/fof/logo-animated.svg" style={{width: '50%'}}/>
            </div>);
    }
}
