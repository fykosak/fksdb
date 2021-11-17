import { translator } from '@translator/translator';
import * as React from 'react';

export default class Attendance extends React.Component<Record<string, never>, { processing: boolean; messages: string[] }> {
    constructor(props) {
        super(props);
        this.state = {processing: false, messages: []};
    }

    public render() {
        return <>
            {this.state.messages.map((text, index) => {
                return <p key={index} className="alert alert-danger">{text}</p>;
            })}
            <div className="text-center">
                <label
                    className={'btn btn-large ' + (this.state.processing ? 'disabled btn-secondary' : 'btn-primary')}>
        <span className="h3">
            {this.state.processing ?
                <i className="fa fa-spinner fa-spin" aria-hidden="true"/>
                : <i className="fa fa-qrcode" aria-hidden="true"/>}
        </span>
                    {this.state.processing ?
                        <span className="mx-3 h3">{translator.getText('...reading code...')}</span> :
                        <span className="mx-3 h3">{translator.getText('Scan QR-code')}</span>
                    }
                    <input
                        style={{height: 1, width: 1}}
                        tabIndex={-1}
                        type="file"
                        accept="image/*"
                        capture="environment"
                        disabled={this.state.processing}
                        onChange={(event) => {
                            this.handleOnChange(event);
                        }}/>
                </label>
            </div>
        </>;
    }

    private handleOnChange(event: React.ChangeEvent<HTMLInputElement>) {
        event.persist();
        this.setState({processing: true, messages: []});
        this.preprocessImage(event).then((code) => {
            fetch('/event145/team-application/detail/' +
                code +
                '?applicationComponent-transitionName=approved_or_spare__participated&do=applicationComponent-transition');
            this.setState({processing: false});
        }).catch((e) => {
            this.setState({processing: false, messages: [e]});
        });
    }

    private preprocessImage(event: React.ChangeEvent<HTMLInputElement>) {
        return new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                event.target.value = '';
                window.qrcode.callback = (e) => {
                    if (e instanceof Error) {
                        reject('Failed to read QR-code: ' + e.message);
                    } else {
                        resolve(e);
                    }
                };
                window.qrcode.decode(reader.result);
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    }
}
