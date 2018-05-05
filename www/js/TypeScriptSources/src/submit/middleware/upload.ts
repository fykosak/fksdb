const allowedTypes = [
    'application/pdf',
];

export const handleFileUpload = (data: FileList, taskId: number, setError: (error) => void): FormData => {

    if (data.length > 1) {
        console.log('max 1 file');
        return;
    }

    if (data.length === 1) {
        if (data.hasOwnProperty(0)) {
            const file: File = data[0];
            const formData = new FormData();
            if (allowedTypes.indexOf(file.type) !== -1) {
                formData.append('task' + taskId, file);
                formData.set('act', 'upload');
                return formData;
            } else {
                setError({text: 'Nepodorovaný formát', level: 'danger'});
            }

        }
    }
    return null;
};
