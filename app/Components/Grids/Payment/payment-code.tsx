import * as React from 'react';
import { useEffect, useState } from 'react';
import * as QRCode from 'qrcode';
import { Translator } from '@translator/translator';

export interface OwnProps {
    data: {
        paymentId: number;
        price: number;
        currency: 'EUR' | 'CZK';
        constantSymbol: string;
        variableSymbol: string;
        specificSymbol: string;
        bankAccount: string;
        bankName: string;
        recipient: string;
        iban: string
        swift: string;
    };
    translator: Translator;
}

export default function PaymentCode({data}: OwnProps) {
    console.log(data);
    const [qrCode, setQRCode] = useState<string>(null);
    useEffect(function () {
        const text = 'SPD*1.0*'
            + 'ACC:' + data.iban.replace(' ', '') + '*'
            + 'AM:' + data.price + '*'
            + 'CC:' + data.currency + '*'
            + 'RF:' + data.paymentId + '*'
            + 'RN:' + data.recipient + '*';
        QRCode.toString(text).then((code) => {
            setQRCode(code);
        });
    }, [])

    console.log(qrCode);
    if (qrCode) {
        return <div style={{maxWidth: '20rem'}} dangerouslySetInnerHTML={{__html: qrCode}}/>;
    }
    return null;

}
