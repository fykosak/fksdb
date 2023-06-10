import { translator } from '@translator/translator';
import { Message } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';

const allowedTypes = [
    'application/pdf',
];

export const handleFileUpload = (data: FileList, setError: (error: Message) => void): FormData | null | void => {

    if (data.length > 1) {
        console.log('max 1 file');
        return;
    }

    if (data.length === 1) {
        if (Object.hasOwn(data,0)) {
            const file: File = data[0];
            const formData = new FormData();
            if (allowedTypes.indexOf(file.type) !== -1) {
                formData.append('submit', file);
                return formData;
            } else {
                setError({text: translator.getText('Unsupported format'), level: 'danger'});
            }
        }
    }
    return null;
};
