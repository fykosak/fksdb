import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import { Html5Qrcode } from 'html5-qrcode';
import { Html5QrcodeError } from 'html5-qrcode/esm/core';
import { TranslatorContext } from '@translator/LangContext';

export default class Scan extends React.Component<WrappedFieldProps & Record<string, never>, { processing: boolean; error: Html5QrcodeError }> {
    static contextType = TranslatorContext;

    constructor(props) {
        super(props);
        this.state = {processing: false, error: null};
    }

    public render() {
        const translator = this.context;
        return <>
            <h3>{translator.getText('Scan QR-code')}</h3>
            {this.state.error && <p className="alert alert-danger">{this.state.error.toString()}</p>}
            <div id="reader" style={{display: 'none'}}/>
            <div className="text-center">
                <label
                    className={'btn btn-large ' + (this.state.processing ? 'disabled btn-outline-secondary' : 'btn-outline-primary')}>
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

    private handleOnChange(event: React.ChangeEvent<HTMLInputElement>): void {
        event.persist();
        const html5QrCode = new Html5Qrcode('reader', {verbose: true});
        if (event.target.files.length == 0) {
            return;
        }
        const imageFile = event.target.files[0];
        this.setState({processing: true, error: null});

        html5QrCode.scanFile(imageFile, true)
            .then(decodedText => {
                this.props.input.onChange(decodedText);
                this.setState({processing: false, error: null});
            })
            .catch((err: Html5QrcodeError) => {
                this.setState({processing: false, error: err});
            });
    }
}
