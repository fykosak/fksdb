import { netteFetch } from '../../shared/helpers/fetch';

export const handleFileUpload = (data: FileList, upload: (formData: FormData) => Promise<any>, taskId: number) => {

    if (data.length > 1) {
        console.log('max 1 file');
        return;
    }

    if (data.length === 1) {
        if (data.hasOwnProperty(0)) {
            const formData = new FormData();
            formData.append('task' + taskId, data[0]);
            formData.set('act', 'upload');
            upload(formData);
        }
    }

};

export const uploadFile = (data: FormData, success: (data) => void, error: (e: any) => void): Promise<any> => {
    // return netteFetch(data, success, error);
    return new Promise((resolve, reject) => {
        $.ajax({
            cache: false,
            contentType: false,
            data,
            dataType: 'json',
            error: (e) => {
                reject(e);
                error(e);
            },
            processData: false,
            success: (d) => {
                resolve(d);
                console.log(d);
                success(d);
            },
            type: 'POST',
            url: '#',
        });
    });
};

