import * as React from 'react';

export default class BackLink extends React.Component<{}, {}> {
    public render() {
        return (
            <a className="nav-link" onClick={() => {
                return window.history.back();
            }}>
                Späť
            </a>
        );
    }
}
