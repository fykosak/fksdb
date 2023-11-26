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
        recipient: string;
        iban: string;
        swift: string;
    };
    translator: Translator;
}

export default function PaymentCode({data}: OwnProps) {
    const [qrCode, setQRCode] = useState<string>(null);
    useEffect(function () {
        let text = 'SPD*1.0*';
        if (data.currency === 'CZK') {
            text += 'ACC:' + data.iban.replace(' ', '') + '*';
            if (data.variableSymbol) {
                text += 'X-VS:' + data.variableSymbol + '*';
            }
            if (data.specificSymbol) {
                text += 'X-SS:' + data.specificSymbol + '*';
            }
            if (data.constantSymbol) {
                text += 'X-KS:' + data.constantSymbol + '*';
            }
        } else {
            text += 'ACC:' + data.iban.replace(' ', '') + '+' + data.swift + '*';
        }
        text += 'AM:' + data.price + '*';
        text += 'CC:' + data.currency + '*';
        text += 'RF:' + data.paymentId + '*';
        text += 'RN:' + data.recipient + '*'
        QRCode.toString(text).then((code) => {
            setQRCode(code);
        });
    }, []);

    if (qrCode) {
        return <div style={{maxWidth: '20rem'}} dangerouslySetInnerHTML={{__html: qrCode}}/>;
    }
    return <div className="fa-3x">
        <i className="fas fa-spinner fa-spin"/>
    </div>;
}
