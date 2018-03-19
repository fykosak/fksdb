export async function netteFetch(data: any = null, success: (data: any) => void, error: (e) => void): Promise<any> {
    const netteJQuery: any = $;
    return new Promise((resolve, reject) => {
        netteJQuery.nette.ajax({
            data,
            error: (e) => {
                error(e);
                reject(e);
            },
            method: 'POST',
            success: (d) => {
                success(d);
                resolve(d);
            },
        });
    });
}
