import * as React from 'react';

export default class Powered extends React.Component<{}, {}> {

    public render() {
        return (
            <div className="container-fluid justify-content-center d-flex align-content-center mt-5">
                <span>Powered by</span>
                <a href="https://reactjs.org/">
                    <img src="/images/react.svg" alt="react" style={{height: '2rem'}}/>
                </a>
                <a href="https://redux.js.org">
                    <img src="/images/redux.png" alt="redux" style={{height: '1.75rem'}}/>
                </a>
                <a href="https://www.typescriptlang.org/">
                    <img src="/images/typescript.svg" alt="typescript" style={{height: '1.5rem'}}/>
                </a>
            </div>
        );
    }
}
