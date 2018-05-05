export const handleFileUpload = (data: FileList, upload: (formData: FormData) => Promise<any>, taskId: number) => {

    if (data.length > 1) {
        console.log('max 1 file');
        return;
    }

    if (data.length === 1) {
        if (data.hasOwnProperty(0)) {
            const formData = new FormData();
            console.log(data[0]);
            formData.append('task' + taskId, data[0]);
            formData.set('act', 'upload');
            upload(formData);
        }
    }

};
