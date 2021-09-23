import '@hundh/contao-utils-bundle';
import Swal from 'sweetalert2';

class FileManagerBundle {
    static init() {
        FileManagerBundle.initActions();
    }

    static initActions() {
        const fileManager = document.querySelector('.mod_huh_file_manager');

        if (fileManager.length < 1) {
            return;
        }

        utilsBundle.event.addDynamicEventListener('click', '.mod_huh_file_manager .actions a', (element, event) => {
            event.preventDefault();

            utilsBundle.ajax.get(element.getAttribute('href'), {}, {
                onSuccess: (response) => {
                    console.log(response);
                },
                onError: (response) => {

                }
            });
        })
    }
}

export {FileManagerBundle};
