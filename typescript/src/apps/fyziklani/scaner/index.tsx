import * as React from 'react';
import QrCode from 'qrcode-reader';

const qrCode = new QrCode();

export default class Index extends React.Component<{}, {}> {
    public render() {
        return <input
            tabIndex={-1}
            type="file"
            accept="image/*"
            capture="environment"
            onChange={(event) => {
                const reader = new FileReader();
                reader.onload = () => {
                    event.target.value = '';
                    qrCode.callback = (res) => {
                        if (res instanceof Error) {
                            alert('No QR code found. Please make sure the QR code is within the camera\'s frame and try again.');
                        } else {
                            console.log(res);
                        }
                    };
                    qrCode.decode(reader.result);
                };
                reader.readAsDataURL(event.target.files[0]);
            }}/>;
    }
}
